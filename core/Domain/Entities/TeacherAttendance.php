<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `teacher_attendance`. */
final class TeacherAttendance extends Entity
{
    public const TABLE = 'teacher_attendance';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'teacher_id' => 'int',
        'date' => 'string',
        'status' => 'string',
        'remark' => '?string',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['teacher_id', 'date'];
    public const ENUMS = [
        'status' => ['present', 'absent', 'late', 'leave'],
    ];
    public const LENGTHS = [
        'remark' => 255,
    ];
}
