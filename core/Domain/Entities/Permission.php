<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `permissions`. */
final class Permission extends Entity
{
    public const TABLE = 'permissions';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'name' => 'string',
        'slug' => 'string',
        'module' => '?string',
    ];
    public const REQUIRED = ['name', 'slug'];
    public const ENUMS = [];
    public const LENGTHS = [
        'name' => 100,
        'slug' => 100,
        'module' => 60,
    ];
}
