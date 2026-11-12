<?php
declare(strict_types=1);

namespace CocoCore\Licensing;

use CocoCore\Exceptions\LicenseException;

/**
 * Vérifie une clé de licence HORS LIGNE : signature Ed25519 (une ou plusieurs clés publiques de confiance,
 * pour la rotation), dates, délai de grâce, révocation locale et retour en arrière de l'horloge.
 */
final class LicenseVerifier
{
    /** @param list<string> $trustedPublicKeys clés publiques Ed25519 en base64 (32 octets) */
    public function __construct(private array $trustedPublicKeys, private int $expiringDays = 30, private int $clockToleranceDays = 1)
    {
    }

    /**
     * @param list<string> $revokedIds identifiants de licence révoqués
     */
    public function verify(string $token, \DateTimeImmutable $now, ?\DateTimeImmutable $lastSeen = null, array $revokedIds = []): LicenseCheck
    {
        if (!function_exists('sodium_crypto_sign_verify_detached')) {
            throw new LicenseException('L\'extension PHP « sodium » est requise pour vérifier une licence.', 'sodium');
        }
        try {
            $decoded = LicenseCodec::decode($token);
        } catch (LicenseException $e) {
            return new LicenseCheck(LicenseStatus::MALFORMED, null, null, null, $e->getMessage());
        }

        $signed = false;
        foreach ($this->trustedPublicKeys as $b64) {
            $pub = base64_decode($b64, true);
            if ($pub !== false && strlen($pub) === 32 && sodium_crypto_sign_verify_detached($decoded['signature'], $decoded['json'], $pub)) {
                $signed = true;
                break;
            }
        }
        if (!$signed) {
            return new LicenseCheck(LicenseStatus::INVALID_SIGNATURE, null, null, null, 'La signature ne correspond à aucune clé de confiance.');
        }

        try {
            $license = License::fromArray($decoded['payload']);
        } catch (LicenseException $e) {
            return new LicenseCheck(LicenseStatus::MALFORMED, null, null, null, $e->getMessage());
        }

        if (in_array($license->id, $revokedIds, true)) {
            return new LicenseCheck(LicenseStatus::REVOKED, $license, null, null, 'Cette licence a été révoquée.');
        }
        if ($lastSeen !== null && $now < $lastSeen->modify('-' . $this->clockToleranceDays . ' days')) {
            return new LicenseCheck(LicenseStatus::TAMPERED, $license, null, null, 'L\'horloge du système est antérieure à la dernière utilisation enregistrée.');
        }
        if ($now < $license->validFrom) {
            return new LicenseCheck(LicenseStatus::NOT_YET_VALID, $license, null, null, 'La licence commence le ' . $license->validFrom->format('Y-m-d') . '.');
        }

        $daysLeft = $this->daysBetween($now, $license->validUntil);
        if ($now <= $license->validUntilEnd()) {
            $status = $daysLeft <= $this->expiringDays ? LicenseStatus::EXPIRING_SOON : LicenseStatus::VALID;
            return new LicenseCheck($status, $license, $daysLeft);
        }
        if ($now <= $license->graceEnd()) {
            return new LicenseCheck(LicenseStatus::GRACE, $license, $daysLeft, $this->daysBetween($now, $license->validUntil->modify('+' . $license->graceDays . ' days')));
        }
        return new LicenseCheck(LicenseStatus::EXPIRED, $license, $daysLeft, 0);
    }

    /** Nombre de jours calendaires de $now (jour inclus) à $date : 0 le dernier jour, négatif après. */
    private function daysBetween(\DateTimeImmutable $now, \DateTimeImmutable $date): int
    {
        $a = $now->setTime(0, 0, 0);
        $b = $date->setTime(0, 0, 0);
        return (int) $a->diff($b)->format('%r%a');
    }
}
