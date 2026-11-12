<?php
declare(strict_types=1);

namespace CocoCore\Licensing;

use CocoCore\Exceptions\LicenseException;

/**
 * Format d'une clé de licence :   CCL1.<charge utile JSON en base64url>.<signature Ed25519 en base64url>
 * La signature couvre exactement les octets de la charge utile transmise (pas de canonicalisation nécessaire).
 */
final class LicenseCodec
{
    public const PREFIX = 'CCL1';

    public static function b64urlEncode(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    public static function b64urlDecode(string $text): string
    {
        $raw = base64_decode(strtr($text, '-_', '+/'), true);
        if ($raw === false) {
            throw new LicenseException('Clé de licence illisible (base64).', 'malformed');
        }
        return $raw;
    }

    /** @param array<string,mixed> $payload @param string $secretKey clé secrète Ed25519 libsodium (64 octets) */
    public static function encode(array $payload, string $secretKey): string
    {
        if (!function_exists('sodium_crypto_sign_detached')) {
            throw new LicenseException('L\'extension PHP « sodium » est requise.', 'sodium');
        }
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $sig = sodium_crypto_sign_detached($json, $secretKey);
        return self::PREFIX . '.' . self::b64urlEncode($json) . '.' . self::b64urlEncode($sig);
    }

    /** @return array{json:string,payload:array<string,mixed>,signature:string} */
    public static function decode(string $token): array
    {
        $parts = explode('.', trim(preg_replace('/\s+/', '', $token) ?? ''));
        if (count($parts) !== 3 || $parts[0] !== self::PREFIX) {
            throw new LicenseException('Format de clé de licence invalide.', 'malformed');
        }
        $json = self::b64urlDecode($parts[1]);
        $sig = self::b64urlDecode($parts[2]);
        $payload = json_decode($json, true);
        if (!is_array($payload) || strlen($sig) !== 64) {
            throw new LicenseException('Contenu de licence illisible.', 'malformed');
        }
        return ['json' => $json, 'payload' => $payload, 'signature' => $sig];
    }
}
