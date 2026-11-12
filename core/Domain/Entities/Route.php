<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `routes`. */
final class Route extends Entity
{
    public const TABLE = 'routes';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'name' => 'string',
        'stops' => '?string',
        'fare' => 'float',
        'status' => 'bool',
    ];
    public const REQUIRED = ['name'];
    public const ENUMS = [];
    public const LENGTHS = [
        'name' => 100,
    ];
}
