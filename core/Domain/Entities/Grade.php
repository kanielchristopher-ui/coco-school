<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `grades`. */
final class Grade extends Entity
{
    public const TABLE = 'grades';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'grade' => 'string',
        'min_percent' => 'float',
        'max_percent' => 'float',
        'point' => 'float',
        'remarks' => '?string',
        'is_active' => 'bool',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['grade'];
    public const ENUMS = [];
    public const LENGTHS = [
        'grade' => 10,
        'remarks' => 255,
    ];
}
