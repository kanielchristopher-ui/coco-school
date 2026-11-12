<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `salary`. */
final class Salary extends Entity
{
    public const TABLE = 'salary';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'staff_id' => '?int',
        'teacher_id' => '?int',
        'month' => 'int',
        'year' => 'int',
        'basic_salary' => 'float',
        'allowances' => 'float',
        'deductions' => 'float',
        'net_salary' => 'float',
        'payment_date' => '?string',
        'status' => 'string',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['month', 'year'];
    public const ENUMS = [
        'status' => ['paid', 'pending', 'draft'],
    ];
    public const LENGTHS = [];
}
