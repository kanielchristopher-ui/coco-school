<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `conduct_grades`. */
final class ConductGrade extends Entity
{
    public const TABLE = 'conduct_grades';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'student_id' => 'int',
        'academic_year_id' => 'int',
        'period_id' => 'string',
        'score' => 'float',
        'mention' => '?string',
        'incident_count' => 'int',
        'comment' => '?string',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['student_id', 'academic_year_id', 'period_id', 'score'];
    public const ENUMS = [];
    public const LENGTHS = [
        'period_id' => 10,
        'mention' => 40,
        'comment' => 255,
    ];
}
