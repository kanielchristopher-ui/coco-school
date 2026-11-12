<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `development_domains`. */
final class DevelopmentDomain extends Entity
{
    public const TABLE = 'development_domains';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'code' => 'string',
        'name' => 'string',
        'description' => '?string',
        'sort_order' => 'int',
    ];
    public const REQUIRED = ['code', 'name'];
    public const ENUMS = [];
    public const LENGTHS = [
        'code' => 20,
        'name' => 100,
        'description' => 255,
    ];
}
