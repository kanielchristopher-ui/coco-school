<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `payrolls`. */
final class Payroll extends Entity
{
    public const TABLE = 'payrolls';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'employee_type' => 'string',
        'employee_id' => 'int',
        'position' => '?string',
        'salary_amount' => 'float',
        'month' => 'string',
        'payment_date' => 'string',
        'status' => 'string',
        'notes' => '?string',
        'created_by' => '?int',
        'created_at' => 'string',
        'updated_at' => 'string',
    ];
    public const REQUIRED = ['employee_type', 'employee_id', 'month', 'payment_date'];
    public const ENUMS = [
        'employee_type' => ['teacher', 'staff'],
        'status' => ['paid', 'pending'],
    ];
    public const LENGTHS = [
        'position' => 80,
        'month' => 7,
        'notes' => 255,
    ];
}
