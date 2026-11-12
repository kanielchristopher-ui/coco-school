<?php
declare(strict_types=1);

namespace CocoCore\Levels\Maternel;

final class AgeSection
{
    /** Âge révolu à la rentrée -> classe d'accueil conseillée : 3 ans = M1, 4 ans = M2, 5 ans = M3. */
    public static function classFor(int $ageYears): ?string
    {
        return match (true) {
            $ageYears === 3 => 'M1',
            $ageYears === 4 => 'M2',
            $ageYears === 5 => 'M3',
            default => null,
        };
    }
}
