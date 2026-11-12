<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `classes`. */
final class SchoolClass extends Entity
{
    public const TABLE = 'classes';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'name' => 'string',
        'numeric_name' => '?string',
        'cycle' => 'string',
        'class_teacher_id' => '?int',
        'created_at' => 'string',
        'establishment_id' => '?int',
        'option_id' => '?int',
    ];
    public const REQUIRED = ['name'];
    public const ENUMS = [
        'cycle' => ['maternel', 'primaire', 'secondaire'],
    ];
    public const LENGTHS = [
        'name' => 60,
        'numeric_name' => 10,
    ];
}
