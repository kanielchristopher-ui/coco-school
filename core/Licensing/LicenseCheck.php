<?php
declare(strict_types=1);

namespace CocoCore\Licensing;

/** Résultat d'une vérification de licence. */
final class LicenseCheck
{
    public function __construct(
        public readonly LicenseStatus $status,
        public readonly ?License $license = null,
        /** Jours restants avant la fin de validité (négatif si dépassée) ; null si non applicable. */
        public readonly ?int $daysLeft = null,
        public readonly ?int $graceDaysLeft = null,
        public readonly string $detail = ''
    ) {
    }
}
