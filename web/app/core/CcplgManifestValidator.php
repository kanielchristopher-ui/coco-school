<?php
/**
 * ============================================================
 *  CcplgManifestValidator  (repris du projet Coco School)
 *  Valide le manifeste d'un paquet .ccplg :
 *   - format / application reconnus, champs obligatoires
 *   - signature Ed25519 du manifeste avec la clé de CONFIANCE du serveur
 *   - chemins sûrs, SHA-256 de chaque fichier, aucun fichier non déclaré
 *   - aucun fichier exécutable (php, js, sh, sql…)
 * ============================================================
 */

declare(strict_types=1);

final class CcplgManifestValidator
{
    private const FORMAT = 'CCPLG';
    private const MAX_FILES = 200;
    private const MAX_FILE_BYTES = 5000000;
    private const MAX_TOTAL_BYTES = 15000000;

    /** @return array<string,mixed> */
    public function validate(string $manifestJson, array $archiveEntries, string $signatureBase64, string $trustedPublicKeyBase64): array
    {
        try {
            $manifest = json_decode($manifestJson, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new CcplgException('Manifeste JSON invalide.', 0, $e);
        }

        $application = defined('CCPLG_APPLICATION') ? CCPLG_APPLICATION : 'Coco School';
        if (!is_array($manifest) || ($manifest['format'] ?? null) !== self::FORMAT
            || ($manifest['application'] ?? null) !== $application
            || ($manifest['format_version'] ?? null) !== 1) {
            throw new CcplgException('Format CCPLG ou application non reconnu.');
        }
        foreach (['plugin_id', 'name', 'version', 'files'] as $field) {
            if (!isset($manifest[$field])) {
                throw new CcplgException('Champ obligatoire absent : ' . $field);
            }
        }
        if (!preg_match('/^[a-z0-9][a-z0-9._-]{2,79}$/', (string) $manifest['plugin_id'])) {
            throw new CcplgException('Identifiant extension invalide.');
        }
        if (!is_array($manifest['files']) || count($manifest['files']) < 1 || count($manifest['files']) > self::MAX_FILES) {
            throw new CcplgException('Liste de fichiers invalide ou trop volumineuse.');
        }

        // La clé de confiance vient de la configuration du serveur, JAMAIS du paquet.
        $publicKey = base64_decode($trustedPublicKeyBase64, true);
        $signature = base64_decode($signatureBase64, true);
        if ($publicKey === false || $signature === false || $trustedPublicKeyBase64 === '' || !function_exists('sodium_crypto_sign_verify_detached')) {
            throw new CcplgException('Vérification Ed25519 indisponible (extension sodium ?) ou clé/signature invalide.');
        }
        if (!sodium_crypto_sign_verify_detached($signature, $manifestJson, $publicKey)) {
            throw new CcplgException('Signature CCPLG invalide.');
        }

        $declared = [];
        $total = 0;
        foreach ($manifest['files'] as $path => $meta) {
            $this->assertSafePath((string) $path);
            if (!is_array($meta) || !preg_match('/^[a-f0-9]{64}$/', (string) ($meta['sha256'] ?? ''))) {
                throw new CcplgException('Métadonnées SHA-256 invalides : ' . $path);
            }
            if (preg_match('/\.(php[0-9]?|phtml|phar|js|mjs|sh|sql)$/i', (string) $path)) {
                throw new CcplgException('Fichier exécutable/interdit dans une extension : ' . $path);
            }
            if (!array_key_exists($path, $archiveEntries) || !is_string($archiveEntries[$path])) {
                throw new CcplgException('Fichier déclaré absent : ' . $path);
            }
            $size = strlen($archiveEntries[$path]);
            if ($size > self::MAX_FILE_BYTES) {
                throw new CcplgException('Fichier trop volumineux : ' . $path);
            }
            $total += $size;
            if (!hash_equals($meta['sha256'], hash('sha256', $archiveEntries[$path]))) {
                throw new CcplgException('Empreinte SHA-256 incorrecte : ' . $path);
            }
            $declared[$path] = true;
        }
        if ($total > self::MAX_TOTAL_BYTES) {
            throw new CcplgException('Extension trop volumineuse.');
        }
        foreach ($archiveEntries as $path => $_) {
            if ($path !== 'plugin.json' && $path !== 'signature.ccsig' && !isset($declared[$path])) {
                throw new CcplgException('Fichier non déclaré dans le manifeste : ' . $path);
            }
        }
        return $manifest;
    }

    private function assertSafePath(string $path): void
    {
        $normalized = str_replace('\\', '/', $path);
        if ($normalized === '' || str_starts_with($normalized, '/') || preg_match('/^[A-Za-z]:/', $normalized)
            || in_array('..', explode('/', $normalized), true)
            || str_contains($normalized, "\0")) {
            throw new CcplgException('Chemin interdit dans le paquet.');
        }
    }
}
