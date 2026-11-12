<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `exams`. */
final class Exam extends Entity
{
    public const TABLE = 'exams';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'name' => 'string',
        'exam_type_id' => '?int',
        'subject_id' => '?int',
        'class_id' => '?int',
        'section_id' => '?int',
        'session_id' => '?int',
        'term' => '?string',
        'start_date' => '?string',
        'end_date' => '?string',
        'status' => 'string',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['name'];
    public const ENUMS = [
        'status' => ['draft', 'published', 'upcoming', 'active', 'completed'],
    ];
    public const LENGTHS = [
        'name' => 100,
        'term' => 30,
    ];
}
