<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `school_evaluation_schemes`. */
final class SchoolEvaluationScheme extends Entity
{
    public const TABLE = 'school_evaluation_schemes';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'school_id' => 'int',
        'level_code' => 'string',
        'academic_year_id' => 'int',
        'scheme_id' => 'string',
        'version' => 'int',
        'config_json' => 'string',
        'created_by' => '?int',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['level_code', 'academic_year_id', 'scheme_id', 'config_json'];
    public const ENUMS = [];
    public const LENGTHS = [
        'level_code' => 30,
        'scheme_id' => 80,
    ];
}
