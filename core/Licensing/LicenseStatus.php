<?php
declare(strict_types=1);

namespace CocoCore\Licensing;

/**
 * État d'une licence à un instant donné.
 *  - écriture autorisée : VALID, EXPIRING_SOON, GRACE, TRIAL
 *  - lecture seule      : EXPIRED, TRIAL_EXPIRED, NOT_YET_VALID
 *  - verrouillé         : INVALID_SIGNATURE, MALFORMED, REVOKED, TAMPERED (seule la page Licence reste accessible)
 */
enum LicenseStatus: string
{
    case VALID = 'valid';
    case EXPIRING_SOON = 'expiring_soon';
    case GRACE = 'grace';
    case EXPIRED = 'expired';
    case NOT_YET_VALID = 'not_yet_valid';
    case INVALID_SIGNATURE = 'invalid_signature';
    case MALFORMED = 'malformed';
    case REVOKED = 'revoked';
    case TAMPERED = 'tampered';
    case TRIAL = 'trial';
    case TRIAL_EXPIRED = 'trial_expired';

    public function allowsWrite(): bool
    {
        return in_array($this, [self::VALID, self::EXPIRING_SOON, self::GRACE, self::TRIAL], true);
    }

    public function isLocked(): bool
    {
        return in_array($this, [self::INVALID_SIGNATURE, self::MALFORMED, self::REVOKED, self::TAMPERED], true);
    }

    public function allowsRead(): bool
    {
        return !$this->isLocked();
    }

    /** Gravité pour l'affichage : ok, warning, danger. */
    public function severity(): string
    {
        return match ($this) {
            self::VALID => 'ok',
            self::EXPIRING_SOON, self::GRACE, self::TRIAL, self::NOT_YET_VALID => 'warning',
            default => 'danger',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::VALID => 'License valid',
            self::EXPIRING_SOON => 'License expires soon',
            self::GRACE => 'License expired — grace period',
            self::EXPIRED => 'License expired — read-only mode',
            self::NOT_YET_VALID => 'License not yet valid',
            self::INVALID_SIGNATURE => 'Invalid license signature',
            self::MALFORMED => 'Malformed license',
            self::REVOKED => 'License revoked',
            self::TAMPERED => 'System clock tampering detected',
            self::TRIAL => 'Trial period',
            self::TRIAL_EXPIRED => 'Trial period ended — read-only mode',
        };
    }
}
