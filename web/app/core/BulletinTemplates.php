<?php
/**
 * ============================================================
 *  BulletinTemplates
 *  Catalogue des modèles de bulletin :
 *    - modèles intégrés  : app/templates/bulletins/
 *    - modèles d'extensions .ccplg installées (signées) : storage/extensions/
 *  Aucun code d'extension n'est exécuté : HTML/CSS déclaratifs, rendus par SafeTemplateRenderer.
 * ============================================================
 */

declare(strict_types=1);

use CocoCore\Plugins\CCPLG\ManifestValidator;
use CocoCore\Plugins\CCPLG\PackageException;
use CocoCore\Plugins\CCPLG\PackageInstaller;
use CocoCore\Plugins\CCPLG\PackageReader;
use CocoCore\Plugins\Templates\SafeTemplateRenderer;

class BulletinTemplates
{
    public const DEFAULT_ID = 'classique';

    public static function config(): array
    {
        static $cfg = null;
        return $cfg ??= (array) require APP_PATH . '/config/ccplg.php';
    }

    /** @return array<string,array{id:string,label:string,source:string,levels:array}> */
    public static function catalog(): array
    {
        $out = [];
        $index = json_decode((string) @file_get_contents(APP_PATH . '/templates/bulletins/index.json'), true) ?: [];
        foreach ($index as $tpl) {
            $out[$tpl['id']] = ['id' => $tpl['id'], 'label' => $tpl['label'], 'source' => 'builtin', 'levels' => $tpl['levels'] ?? ['primaire', 'secondaire'], 'html' => $tpl['html'], 'css' => $tpl['css']];
        }
        foreach (self::installed() as $ext) {
            if ($ext['status'] !== 'active') {
                continue;
            }
            $manifest = json_decode((string) $ext['manifest_json'], true) ?: [];
            foreach ($manifest['provides']['templates'] ?? [] as $tpl) {
                $id = 'ext:' . $ext['plugin_id'] . ':' . $tpl['id'];
                $out[$id] = ['id' => $id, 'label' => $tpl['label'] . ' (' . $ext['plugin_id'] . ')', 'source' => $ext['plugin_id'], 'levels' => $tpl['levels'] ?? ['primaire', 'secondaire'], 'html' => $tpl['html'], 'css' => $tpl['css']];
            }
        }
        return $out;
    }

    public static function forLevel(string $level): array
    {
        return array_filter(self::catalog(), fn($t) => in_array($level, $t['levels'], true));
    }

    /** @return array{html:string,css:string} */
    public static function load(string $id): array
    {
        $cat = self::catalog();
        $tpl = $cat[$id] ?? $cat[self::DEFAULT_ID] ?? null;
        if (!$tpl) {
            throw new PackageException('Modèle introuvable.');
        }
        $base = $tpl['source'] === 'builtin'
            ? APP_PATH . '/templates/bulletins'
            : rtrim((string) self::config()['extensions_dir'], '/\\') . '/' . $tpl['source'];
        $read = function (string $rel) use ($base): string {
            $path = realpath($base . '/' . $rel);
            $root = realpath($base);
            if ($path === false || $root === false || !str_starts_with($path, $root . DIRECTORY_SEPARATOR) || is_link($path)) {
                throw new PackageException('Fichier de modèle invalide.');
            }
            return (string) file_get_contents($path);
        };
        return ['html' => $read($tpl['html']), 'css' => $read($tpl['css'])];
    }

    /** @return array{html:string,css:string} HTML déjà échappé et sûr. */
    public static function render(array $presented, string $templateId): array
    {
        $tpl = self::load($templateId);
        $renderer = new SafeTemplateRenderer();
        $renderer->assertSafe($tpl['css']);
        return ['html' => $renderer->render($tpl['html'], $presented), 'css' => $tpl['css']];
    }

    // ---------------------------------------------------------------- extensions

    public static function installed(): array
    {
        try {
            return Database::getInstance()->fetchAll('SELECT * FROM school_extensions ORDER BY installed_at DESC');
        } catch (Throwable $e) {
            return [];
        }
    }

    /** Vérifie la signature (clé de confiance du serveur), puis installe. */
    public static function install(string $packagePath, int $userId): array
    {
        $cfg = self::config();
        if (!function_exists('sodium_crypto_sign_verify_detached') || !class_exists('ZipArchive')) {
            throw new PackageException('Les extensions PHP « sodium » et « zip » sont requises.');
        }
        $reader = new PackageReader();
        $package = $reader->read($packagePath);
        $manifest = json_decode($package['manifest'], true) ?: [];
        $pluginId = (string) ($manifest['plugin_id'] ?? '');
        $db = Database::getInstance();
        if ($pluginId !== '' && $db->fetch('SELECT id FROM school_extensions WHERE plugin_id = ?', [$pluginId])) {
            throw new PackageException('Cette extension est déjà installée : supprimez-la d\'abord pour la mettre à jour.');
        }
        $installer = new PackageInstaller($reader, new ManifestValidator());
        $res = $installer->install($packagePath, (string) $cfg['extensions_dir'], (string) $cfg['trusted_public_key']);

        // Les modèles déclarés doivent exister et être sûrs
        $renderer = new SafeTemplateRenderer();
        foreach ($manifest['provides']['templates'] ?? [] as $tpl) {
            foreach (['html', 'css'] as $k) {
                $file = $res['path'] . '/' . ($tpl[$k] ?? '');
                if (!is_file($file)) {
                    throw new PackageException('Modèle déclaré mais absent : ' . ($tpl[$k] ?? ''));
                }
                $renderer->assertSafe((string) file_get_contents($file));
            }
        }
        $db->insert('school_extensions', [
            'school_id' => 1, 'plugin_id' => $res['plugin_id'], 'name' => (string) ($manifest['name'] ?? $res['plugin_id']),
            'version' => $res['version'], 'package_sha256' => hash_file('sha256', $packagePath),
            'manifest_json' => json_encode($manifest, JSON_UNESCAPED_UNICODE), 'installed_by' => $userId, 'status' => 'active',
        ]);
        return $res;
    }

    public static function setStatus(int $id, string $status): void
    {
        Database::getInstance()->update('school_extensions', ['status' => $status === 'active' ? 'active' : 'disabled'], 'id = ?', [$id]);
    }

    public static function remove(int $id): void
    {
        $db = Database::getInstance();
        $ext = $db->fetch('SELECT * FROM school_extensions WHERE id = ?', [$id]);
        if (!$ext) {
            return;
        }
        $dir = rtrim((string) self::config()['extensions_dir'], '/\\') . '/' . $ext['plugin_id'];
        if (preg_match('/^[a-z0-9][a-z0-9._-]{2,79}$/', $ext['plugin_id']) && is_dir($dir) && !is_link($dir)) {
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($it as $f) {
                $f->isDir() && !$f->isLink() ? rmdir($f->getPathname()) : unlink($f->getPathname());
            }
            rmdir($dir);
        }
        $db->delete('school_extensions', 'id = ?', [$id]);
    }
}
