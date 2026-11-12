<?php
/**
 * Installation d'une extension vérifiée : seules des ressources déclaratives
 * (JSON, HTML, CSS, images) sont écrites, dans un dossier temporaire puis
 * déplacées de façon atomique. Rien n'est jamais exécuté.
 */

declare(strict_types=1);

final class CcplgPackageInstaller
{
    public function __construct(
        private CcplgPackageReader $reader = new CcplgPackageReader(),
        private CcplgManifestValidator $validator = new CcplgManifestValidator()
    ) {
    }

    /** @return array{plugin_id:string,version:string,name:string,path:string,sha256:string} */
    public function install(string $packagePath, string $destinationRoot, string $trustedPublicKeyBase64): array
    {
        $package  = $this->reader->read($packagePath);
        $manifest = $this->validator->validate($package['manifest'], $package['entries'], $package['signature'], $trustedPublicKeyBase64);

        $target = rtrim($destinationRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $manifest['plugin_id'];
        if (!is_dir($destinationRoot) && !mkdir($destinationRoot, 0750, true) && !is_dir($destinationRoot)) {
            throw new CcplgException("Impossible de créer le répertoire des extensions.");
        }
        if (is_dir($target)) {
            throw new CcplgException('Cette extension est déjà installée.');
        }
        $temp = $target . '.tmp-' . bin2hex(random_bytes(5));
        if (!mkdir($temp, 0750, true)) {
            throw new CcplgException("Impossible de préparer l'installation.");
        }
        try {
            $package['entries']['plugin.json'] = $package['manifest'];
            foreach ($package['entries'] as $relative => $content) {
                $relative    = str_replace('\\', '/', $relative);
                $destination = $temp . DIRECTORY_SEPARATOR . $relative;
                $parent      = dirname($destination);
                if (!is_dir($parent) && !mkdir($parent, 0750, true) && !is_dir($parent)) {
                    throw new CcplgException('Impossible de créer un sous-répertoire.');
                }
                if (file_put_contents($destination, $content, LOCK_EX) === false) {
                    throw new CcplgException("Échec d'écriture d'une ressource.");
                }
            }
            if (!rename($temp, $target)) {
                throw new CcplgException('Installation atomique impossible.');
            }
        } catch (Throwable $e) {
            $this->removeTree($temp);
            throw $e;
        }
        return [
            'plugin_id' => (string) $manifest['plugin_id'],
            'version'   => (string) $manifest['version'],
            'name'      => (string) $manifest['name'],
            'path'      => $target,
            'sha256'    => (string) hash_file('sha256', $packagePath),
        ];
    }

    public function removeTree(string $path): void
    {
        if (!is_dir($path) || is_link($path)) {
            return;
        }
        foreach (new DirectoryIterator($path) as $item) {
            if ($item->isDot()) {
                continue;
            }
            $child = $item->getPathname();
            if ($item->isDir() && !$item->isLink()) {
                $this->removeTree($child);
            } else {
                unlink($child);
            }
        }
        rmdir($path);
    }
}
