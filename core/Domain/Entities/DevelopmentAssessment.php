<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `development_assessments`. */
final class DevelopmentAssessment extends Entity
{
    public const TABLE = 'development_assessments';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'student_id' => 'int',
        'domain_id' => 'int',
        'academic_year_id' => 'int',
        'period_id' => 'string',
        'level' => 'string',
        'comment' => '?string',
        'assessed_by' => '?int',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['student_id', 'domain_id', 'academic_year_id', 'period_id', 'level'];
    public const ENUMS = [
        'level' => ['acquired', 'in_progress', 'not_acquired'],
    ];
    public const LENGTHS = [
        'period_id' => 10,
        'comment' => 255,
    ];
}
