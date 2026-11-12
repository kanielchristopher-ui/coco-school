<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `expenses`. */
final class Expense extends Entity
{
    public const TABLE = 'expenses';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'category_id' => 'int',
        'description' => 'string',
        'amount' => 'float',
        'expense_date' => 'string',
        'paid_by' => '?int',
        'notes' => '?string',
        'created_at' => 'string',
        'updated_at' => 'string',
    ];
    public const REQUIRED = ['category_id', 'description', 'expense_date'];
    public const ENUMS = [];
    public const LENGTHS = [
        'description' => 255,
        'notes' => 255,
    ];
}
