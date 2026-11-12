<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `student_enrollments`. */
final class StudentEnrollment extends Entity
{
    public const TABLE = 'student_enrollments';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'student_id' => 'int',
        'class_id' => 'int',
        'section_id' => '?int',
        'establishment_id' => 'int',
        'academic_year_id' => 'int',
        'enrolled_on' => 'string',
        'status' => 'string',
        'final_percent' => '?float',
        'decision_note' => '?string',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['student_id', 'class_id', 'establishment_id', 'academic_year_id', 'enrolled_on'];
    public const ENUMS = [
        'status' => ['enrolled', 'promoted', 'repeating', 'retake', 'transferred', 'withdrawn', 'graduated'],
    ];
    public const LENGTHS = [
        'decision_note' => 255,
    ];
}
