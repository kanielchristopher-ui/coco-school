<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `timetables`. */
final class TimetableEntry extends Entity
{
    public const TABLE = 'timetables';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'class_id' => 'int',
        'section_id' => '?int',
        'day_of_week' => 'int',
        'period' => '?string',
        'subject_id' => '?int',
        'teacher_id' => '?int',
        'start_time' => '?string',
        'end_time' => '?string',
    ];
    public const REQUIRED = ['class_id', 'day_of_week'];
    public const ENUMS = [];
    public const LENGTHS = [
        'period' => 30,
    ];
}
