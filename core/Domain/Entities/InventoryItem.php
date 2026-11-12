<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `inventory_items`. */
final class InventoryItem extends Entity
{
    public const TABLE = 'inventory_items';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'name' => 'string',
        'category_id' => '?int',
        'supplier_id' => '?int',
        'quantity' => 'int',
        'unit_price' => 'float',
        'purchase_date' => '?string',
        'location' => '?string',
        'status' => 'string',
    ];
    public const REQUIRED = ['name'];
    public const ENUMS = [
        'status' => ['in_stock', 'low_stock', 'out_of_stock'],
    ];
    public const LENGTHS = [
        'name' => 120,
        'location' => 120,
    ];
}
