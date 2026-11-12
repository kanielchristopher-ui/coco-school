<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `fee_collections`. */
final class FeeCollection extends Entity
{
    public const TABLE = 'fee_collections';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'student_id' => 'int',
        'class_id' => '?int',
        'fee_type' => 'string',
        'amount_paid' => 'float',
        'payment_date' => 'string',
        'payment_method' => 'string',
        'collected_by' => '?int',
        'notes' => '?string',
        'receipt_no' => 'string',
        'status' => 'string',
        'created_at' => 'string',
        'updated_at' => 'string',
    ];
    public const REQUIRED = ['student_id', 'fee_type', 'payment_date', 'receipt_no'];
    public const ENUMS = [
        'status' => ['active', 'voided'],
    ];
    public const LENGTHS = [
        'fee_type' => 40,
        'payment_method' => 20,
        'notes' => 255,
        'receipt_no' => 30,
    ];
}
