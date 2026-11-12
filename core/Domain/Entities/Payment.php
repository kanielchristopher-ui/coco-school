<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `payments`. */
final class Payment extends Entity
{
    public const TABLE = 'payments';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'receipt_no' => 'string',
        'fee_id' => 'int',
        'student_id' => 'int',
        'amount' => 'float',
        'payment_method' => 'string',
        'transaction_id' => '?string',
        'paid_by' => '?int',
        'payment_date' => 'string',
        'remark' => '?string',
    ];
    public const REQUIRED = ['receipt_no', 'fee_id', 'student_id'];
    public const ENUMS = [
        'payment_method' => ['cash', 'card', 'bank_transfer', 'cheque', 'online', 'mobile_money'],
    ];
    public const LENGTHS = [
        'receipt_no' => 40,
        'transaction_id' => 100,
        'remark' => 255,
    ];
}
