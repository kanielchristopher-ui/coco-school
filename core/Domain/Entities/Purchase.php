<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `purchases`. */
final class Purchase extends Entity
{
    public const TABLE = 'purchases';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'item_id' => 'int',
        'supplier_id' => '?int',
        'quantity' => 'int',
        'unit_price' => 'float',
        'total' => 'float',
        'invoice_no' => '?string',
        'purchase_date' => '?string',
    ];
    public const REQUIRED = ['item_id'];
    public const ENUMS = [];
    public const LENGTHS = [
        'invoice_no' => 40,
    ];
}
