<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `results`. */
final class Result extends Entity
{
    public const TABLE = 'results';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'exam_id' => 'int',
        'student_id' => 'int',
        'subject_id' => 'int',
        'marks_obtained' => 'float',
        'max_marks' => 'float',
        'grade' => '?string',
        'remarks' => '?string',
        'teacher_comments' => '?string',
        'correction_comment' => '?string',
        'status' => 'string',
        'published_at' => '?string',
        'reopened_at' => '?string',
        'created_at' => 'string',
        'updated_at' => 'string',
        'result_date' => '?string',
    ];
    public const REQUIRED = ['exam_id', 'student_id', 'subject_id'];
    public const ENUMS = [
        'status' => ['draft', 'submitted', 'approved', 'returned', 'published', 'locked'],
    ];
    public const LENGTHS = [
        'grade' => 5,
        'remarks' => 255,
        'teacher_comments' => 255,
        'correction_comment' => 500,
    ];
}
