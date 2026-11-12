<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `staff`. */
final class Staff extends Entity
{
    public const TABLE = 'staff';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'user_id' => '?int',
        'employee_id' => 'string',
        'first_name' => 'string',
        'last_name' => '?string',
        'department' => '?string',
        'designation' => '?string',
        'phone' => '?string',
        'email' => '?string',
        'joining_date' => '?string',
        'basic_salary' => 'float',
        'status' => 'string',
    ];
    public const REQUIRED = ['employee_id', 'first_name'];
    public const ENUMS = [
        'status' => ['active', 'inactive', 'left'],
    ];
    public const LENGTHS = [
        'employee_id' => 30,
        'first_name' => 60,
        'last_name' => 60,
        'department' => 80,
        'designation' => 80,
        'phone' => 30,
        'email' => 120,
    ];
}
