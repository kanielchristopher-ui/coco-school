<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `fee_discounts`. */
final class FeeDiscount extends Entity
{
    public const TABLE = 'fee_discounts';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'student_id' => 'int',
        'title' => '?string',
        'discount_type' => 'string',
        'value' => 'float',
        'reason' => '?string',
        'valid_from' => '?string',
        'valid_to' => '?string',
    ];
    public const REQUIRED = ['student_id'];
    public const ENUMS = [
        'discount_type' => ['fixed', 'percent'],
    ];
    public const LENGTHS = [
        'title' => 100,
        'reason' => 255,
    ];
}
