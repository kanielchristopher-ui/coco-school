<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `fee_plans`. */
final class FeePlan extends Entity
{
    public const TABLE = 'fee_plans';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'establishment_id' => 'int',
        'academic_year_id' => 'int',
        'class_id' => '?int',
        'fee_type' => 'string',
        'amount' => 'float',
        'frequency' => 'string',
        'active' => 'bool',
    ];
    public const REQUIRED = ['establishment_id', 'academic_year_id', 'fee_type', 'amount'];
    public const ENUMS = [
        'frequency' => ['one_time', 'monthly', 'term', 'semester', 'yearly'],
    ];
    public const LENGTHS = [
        'fee_type' => 80,
    ];
}
