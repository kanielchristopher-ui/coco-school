<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `stock_issues`. */
final class StockIssue extends Entity
{
    public const TABLE = 'stock_issues';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'item_id' => 'int',
        'issued_to' => '?string',
        'quantity' => 'int',
        'issue_date' => '?string',
        'remark' => '?string',
    ];
    public const REQUIRED = ['item_id'];
    public const ENUMS = [];
    public const LENGTHS = [
        'issued_to' => 120,
        'remark' => 255,
    ];
}
