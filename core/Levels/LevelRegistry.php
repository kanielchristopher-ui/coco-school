<?php
declare(strict_types=1);

namespace CocoCore\Levels;

use CocoCore\Levels\Maternel\MaternelProfile;
use CocoCore\Levels\Primaire\PrimaireProfile;
use CocoCore\Levels\Secondaire\SecondaireProfile;

final class LevelRegistry
{
    public static function profile(SchoolLevel|string $level): LevelProfile
    {
        $lvl = $level instanceof SchoolLevel ? $level : SchoolLevel::parse($level);
        return match ($lvl) {
            SchoolLevel::MATERNEL => new MaternelProfile(),
            SchoolLevel::PRIMAIRE => new PrimaireProfile(),
            SchoolLevel::SECONDAIRE => new SecondaireProfile(),
        };
    }

    /** @return list<LevelProfile> */
    public static function all(): array
    {
        return array_map(fn(SchoolLevel $l) => self::profile($l), SchoolLevel::cases());
    }

    /** Un module est-il propre/actif pour ce niveau ? (ex. « tenafep » : primaire uniquement) */
    public static function moduleEnabled(SchoolLevel|string $level, string $module): bool
    {
        return in_array($module, self::profile($level)->modules(), true);
    }
}
