<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `teachers`. */
final class Teacher extends Entity
{
    public const TABLE = 'teachers';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'user_id' => '?int',
        'official_id' => '?string',
        'employee_id' => 'string',
        'first_name' => 'string',
        'last_name' => '?string',
        'gender' => '?string',
        'dob' => '?string',
        'department' => '?string',
        'teaching_level' => 'string',
        'qualification' => '?string',
        'specialization' => '?string',
        'experience_years' => '?int',
        'phone' => '?string',
        'email' => '?string',
        'address' => '?string',
        'salary' => 'float',
        'joining_date' => '?string',
        'photo' => '?string',
        'cv' => '?string',
        'status' => 'string',
        'created_at' => 'string',
        'updated_at' => 'string',
    ];
    public const REQUIRED = ['employee_id', 'first_name'];
    public const ENUMS = [
        'gender' => ['male', 'female', 'other'],
        'teaching_level' => ['maternel', 'primaire', 'secondaire'],
        'status' => ['active', 'inactive', 'left'],
    ];
    public const LENGTHS = [
        'official_id' => 20,
        'employee_id' => 30,
        'first_name' => 60,
        'last_name' => 60,
        'department' => 80,
        'qualification' => 255,
        'specialization' => 120,
        'phone' => 30,
        'email' => 120,
        'address' => 255,
        'photo' => 255,
        'cv' => 255,
    ];
}
