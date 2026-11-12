<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `fee_types`. */
final class FeeType extends Entity
{
    public const TABLE = 'fee_types';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'name' => 'string',
        'amount' => 'float',
        'frequency' => 'string',
        'description' => '?string',
        'status' => 'bool',
    ];
    public const REQUIRED = ['name'];
    public const ENUMS = [
        'frequency' => ['one_time', 'monthly', 'yearly', 'term'],
    ];
    public const LENGTHS = [
        'name' => 80,
        'description' => 255,
    ];
}
