<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `fees`. */
final class Fee extends Entity
{
    public const TABLE = 'fees';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'invoice_no' => 'string',
        'student_id' => 'int',
        'fee_type_id' => '?int',
        'amount' => 'float',
        'discount_type' => 'string',
        'discount_amount' => 'float',
        'scholarship' => 'float',
        'due_date' => '?string',
        'status' => 'string',
        'created_at' => 'string',
        'updated_at' => 'string',
    ];
    public const REQUIRED = ['invoice_no', 'student_id'];
    public const ENUMS = [
        'discount_type' => ['fixed', 'percent', 'none'],
        'status' => ['pending', 'partial', 'paid', 'overdue', 'cancelled'],
    ];
    public const LENGTHS = [
        'invoice_no' => 40,
    ];
}
