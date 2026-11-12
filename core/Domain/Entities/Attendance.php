<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `attendance`. */
final class Attendance extends Entity
{
    public const TABLE = 'attendance';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'student_id' => 'int',
        'class_id' => '?int',
        'section_id' => '?int',
        'subject_id' => '?int',
        'date' => 'string',
        'status' => 'string',
        'remark' => '?string',
        'taken_by' => '?int',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['student_id', 'date'];
    public const ENUMS = [
        'status' => ['present', 'absent', 'late', 'leave'],
    ];
    public const LENGTHS = [
        'remark' => 255,
    ];
}
