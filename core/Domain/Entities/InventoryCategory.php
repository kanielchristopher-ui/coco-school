<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `inventory_categories`. */
final class InventoryCategory extends Entity
{
    public const TABLE = 'inventory_categories';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'name' => 'string',
    ];
    public const REQUIRED = ['name'];
    public const ENUMS = [];
    public const LENGTHS = [
        'name' => 80,
    ];
}
