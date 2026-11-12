<?php
declare(strict_types=1);

namespace CocoCore\Licensing;

use CocoCore\Contracts\LicenseGuardInterface;

/** Garde qui autorise tout (tests, outils en ligne de commande). */
final class NullLicenseGuard implements LicenseGuardInterface
{
    public function requireWrite(?string $level = null, ?string $module = null): void
    {
    }

    public function requireQuota(string $level, int $adding = 1): void
    {
    }
}
