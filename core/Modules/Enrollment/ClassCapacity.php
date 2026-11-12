<?php
declare(strict_types=1);

namespace CocoCore\Modules\Enrollment;

use CocoCore\Levels\LevelRegistry;

final class ClassCapacity
{
    /** @return array{ok:bool,limit:int,free:int} */
    public static function check(string $level, int $enrolled, ?int $sectionCapacity = null): array
    {
        $limit = $sectionCapacity ?? LevelRegistry::profile($level)->recommendedClassSize();
        return ['ok' => $enrolled < $limit, 'limit' => $limit, 'free' => max(0, $limit - $enrolled)];
    }
}
