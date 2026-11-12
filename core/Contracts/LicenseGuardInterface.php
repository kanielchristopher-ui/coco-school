<?php
declare(strict_types=1);

namespace CocoCore\Contracts;

/** Garde de licence appelée par les services avant toute ÉCRITURE. */
interface LicenseGuardInterface
{
    /** @throws \CocoCore\Exceptions\LicenseException */
    public function requireWrite(?string $level = null, ?string $module = null): void;

    /** Vérifie le plafond d'élèves actifs du niveau (ajout de $adding élèves). @throws \CocoCore\Exceptions\LicenseException */
    public function requireQuota(string $level, int $adding = 1): void;
}
