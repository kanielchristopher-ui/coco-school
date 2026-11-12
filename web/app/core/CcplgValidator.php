<?php
/**
 * Valide un paquet .ccplg : format, signature Ed25519 du manifeste (clé de confiance
 * fournie par la configuration, jamais par le paquet), empreintes SHA-256, chemins sûrs,
 * extensions interdites (php, js, sh, sql…), tailles maximales.
 */
class CcplgValidator
{
    private const FORMAT = 'CCPLG';
    private const APP = 'Coco School';
    private const MAX_FILES = 200;
    private const MAX_FILE_BYTES = 5000000;
    private const MAX_TOTAL_BYTES = 15000000;

    /** @return array<string,mixed> */
    public function validate(string $manifestJson, array $archiveEntries, string $signatureBase64, string $trustedPublicKeyBase64): array
    {
        try {
            $manifest = json_decode($manifestJson, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new CcplgException('Invalid JSON manifest.', 0, $e);
        }

        if (!is_array($manifest) || ($manifest['format'] ?? null) !== self::FORMAT
            || ($manifest['application'] ?? null) !== self::APP
            || ($manifest['format_version'] ?? null) !== 1) {
            throw new CcplgException('Unrecognised CCPLG format or application.');
        }
        foreach (['plugin_id', 'name', 'version', 'files'] as $field) {
            if (!isset($manifest[$field])) {
                throw new CcplgException('Missing required field: ' . $field);
            }
        }
        if (!preg_match('/^[a-z0-9][a-z0-9._-]{2,79}$/', (string) $manifest['plugin_id'])) {
            throw new CcplgException('Invalid extension identifier.');
        }
        if (!is_array($manifest['files']) || count($manifest['files']) < 1 || count($manifest['files']) > self::MAX_FILES) {
            throw new CcplgException('Invalid or oversized file list.');
        }

        $publicKey = base64_decode($trustedPublicKeyBase64, true);
        $signature = base64_decode($signatureBase64, true);
        if ($publicKey === false || $publicKey === '' || $signature === false || !function_exists('sodium_crypto_sign_verify_detached')) {
            throw new CcplgException('Ed25519 verification unavailable or invalid key/signature.');
        }
        if (!sodium_crypto_sign_verify_detached($signature, $manifestJson, $publicKey)) {
            throw new CcplgException('Invalid CCPLG signature.');
        }

        $declared = [];
        $total = 0;
        foreach ($manifest['files'] as $path => $meta) {
            $path = (string) $path;
            $this->assertSafePath($path);
            if (!is_array($meta) || !preg_match('/^[a-f0-9]{64}$/', (string) ($meta['sha256'] ?? ''))) {
                throw new CcplgException('Invalid SHA-256 metadata: ' . $path);
            }
            if (preg_match('/\.(php[0-9]?|phtml|phar|js|mjs|sh|sql)$/i', $path)) {
                throw new CcplgException('Executable/forbidden file in extension: ' . $path);
            }
            if (!array_key_exists($path, $archiveEntries) || !is_string($archiveEntries[$path])) {
                throw new CcplgException('Declared file is missing: ' . $path);
            }
            $size = strlen($archiveEntries[$path]);
            if ($size > self::MAX_FILE_BYTES) {
                throw new CcplgException('File too large: ' . $path);
            }
            $total += $size;
            if (!hash_equals((string) $meta['sha256'], hash('sha256', $archiveEntries[$path]))) {
                throw new CcplgException('Wrong SHA-256 fingerprint: ' . $path);
            }
            $declared[$path] = true;
        }
        if ($total > self::MAX_TOTAL_BYTES) {
            throw new CcplgException('Extension too large.');
        }
        foreach ($archiveEntries as $path => $_) {
            if (!isset($declared[$path])) {
                throw new CcplgException('File not declared in the manifest: ' . $path);
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
            throw new CcplgException('Forbidden path in package.');
        }
    }
}
