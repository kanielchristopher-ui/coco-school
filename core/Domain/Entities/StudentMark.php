<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `student_marks`. */
final class StudentMark extends Entity
{
    public const TABLE = 'student_marks';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'school_id' => 'int',
        'student_id' => 'int',
        'class_id' => 'int',
        'subject_id' => 'int',
        'academic_year_id' => 'int',
        'scheme_version_id' => 'int',
        'step_id' => 'string',
        'max_points' => 'float',
        'points_obtained' => 'float',
        'entered_by' => '?int',
        'updated_at' => 'string',
    ];
    public const REQUIRED = ['student_id', 'class_id', 'subject_id', 'academic_year_id', 'scheme_version_id', 'step_id', 'max_points', 'points_obtained'];
    public const ENUMS = [];
    public const LENGTHS = [
        'step_id' => 40,
    ];
}
