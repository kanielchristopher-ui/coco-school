<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `retake_exams`. */
final class RetakeExam extends Entity
{
    public const TABLE = 'retake_exams';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'student_id' => 'int',
        'subject_id' => '?int',
        'academic_year_id' => 'int',
        'original_percent' => 'float',
        'retake_percent' => '?float',
        'result' => 'string',
        'exam_date' => '?string',
    ];
    public const REQUIRED = ['student_id', 'academic_year_id', 'original_percent'];
    public const ENUMS = [
        'result' => ['pending', 'passed', 'failed'],
    ];
    public const LENGTHS = [];
}
