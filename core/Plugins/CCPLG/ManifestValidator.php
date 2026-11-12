<?php
declare(strict_types=1);

namespace CocoCore\Plugins\CCPLG;

final class ManifestValidator
{
    private const FORMAT = 'CCPLG';
    private const APP = 'Coco School';
    private const MAX_FILES = 200;
    private const MAX_FILE_BYTES = 5_000_000;
    private const MAX_TOTAL_BYTES = 15_000_000;

    /** @return array<string,mixed> */
    public function validate(string $manifestJson, array $archiveEntries, string $signatureBase64, string $trustedPublicKeyBase64): array
    {
        try {
            $manifest = json_decode($manifestJson, true, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new PackageException('Manifeste JSON invalide.', 0, $e);
        }

        if (!is_array($manifest) || ($manifest['format'] ?? null) !== self::FORMAT
            || ($manifest['application'] ?? null) !== self::APP
            || ($manifest['format_version'] ?? null) !== 1) {
            throw new PackageException('Format CCPLG ou application non reconnu.');
        }
        foreach (['plugin_id', 'name', 'version', 'files'] as $field) {
            if (!isset($manifest[$field])) {
                throw new PackageException('Champ obligatoire absent : ' . $field);
            }
        }
        if (!preg_match('/^[a-z0-9][a-z0-9._-]{2,79}$/', (string) $manifest['plugin_id'])) {
            throw new PackageException('Identifiant extension invalide.');
        }
        if (!is_array($manifest['files']) || count($manifest['files']) < 1 || count($manifest['files']) > self::MAX_FILES) {
            throw new PackageException('Liste de fichiers invalide ou trop volumineuse.');
        }

        $publicKey = base64_decode($trustedPublicKeyBase64, true);
        $signature = base64_decode($signatureBase64, true);
        if ($publicKey === false || $signature === false || !function_exists('sodium_crypto_sign_verify_detached')) {
            throw new PackageException('Vérification Ed25519 indisponible ou clé/signature invalide.');
        }
        if (!sodium_crypto_sign_verify_detached($signature, $manifestJson, $publicKey)) {
            throw new PackageException('Signature CCPLG invalide.');
        }

        $declared = [];
        $total = 0;
        foreach ($manifest['files'] as $path => $meta) {
            $this->assertSafePath((string) $path);
            if (!is_array($meta) || !preg_match('/^[a-f0-9]{64}$/', (string) ($meta['sha256'] ?? ''))) {
                throw new PackageException('Métadonnées SHA-256 invalides : ' . $path);
            }
            if (preg_match('/\.(php[0-9]?|phtml|phar|js|mjs|sh|sql)$/i', $path)) {
                throw new PackageException('Fichier exécutable/interdit dans une extension : ' . $path);
            }
            if (!array_key_exists($path, $archiveEntries) || !is_string($archiveEntries[$path])) {
                throw new PackageException('Fichier déclaré absent : ' . $path);
            }
            $size = strlen($archiveEntries[$path]);
            if ($size > self::MAX_FILE_BYTES) {
                throw new PackageException('Fichier trop volumineux : ' . $path);
            }
            $total += $size;
            if (!hash_equals($meta['sha256'], hash('sha256', $archiveEntries[$path]))) {
                throw new PackageException('Empreinte SHA-256 incorrecte : ' . $path);
            }
            $declared[$path] = true;
        }
        if ($total > self::MAX_TOTAL_BYTES) {
            throw new PackageException('Extension trop volumineuse.');
        }
        foreach ($archiveEntries as $path => $_) {
            if ($path !== 'plugin.json' && $path !== 'signature.ccsig' && !isset($declared[$path])) {
                throw new PackageException('Fichier non déclaré dans le manifeste : ' . $path);
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
            throw new PackageException('Chemin interdit dans le paquet.');
        }
    }
}
