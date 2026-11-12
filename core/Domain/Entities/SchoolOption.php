<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `school_options`. */
final class SchoolOption extends Entity
{
    public const TABLE = 'school_options';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'code' => 'string',
        'name' => 'string',
        'group_name' => 'string',
        'exetat_code' => '?string',
        'status' => 'string',
    ];
    public const REQUIRED = ['code', 'name', 'group_name'];
    public const ENUMS = [
        'status' => ['active', 'inactive'],
    ];
    public const LENGTHS = [
        'code' => 20,
        'name' => 120,
        'group_name' => 120,
        'exetat_code' => 20,
    ];
}
