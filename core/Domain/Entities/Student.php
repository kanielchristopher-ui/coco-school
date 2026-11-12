<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `students`. */
final class Student extends Entity
{
    public const TABLE = 'students';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'user_id' => '?int',
        'official_id' => '?string',
        'admission_no' => 'string',
        'roll_no' => '?string',
        'first_name' => 'string',
        'middle_name' => '?string',
        'last_name' => '?string',
        'gender' => '?string',
        'dob' => '?string',
        'birth_place' => '?string',
        'nationality' => '?string',
        'perm_number' => '?string',
        'class_id' => '?int',
        'section_id' => '?int',
        'guardian_name' => '?string',
        'guardian_relation' => '?string',
        'guardian_phone' => '?string',
        'guardian_email' => '?string',
        'address' => '?string',
        'city' => '?string',
        'state' => '?string',
        'zip' => '?string',
        'phone' => '?string',
        'email' => '?string',
        'photo' => '?string',
        'blood_group' => '?string',
        'medical_info' => '?string',
        'status' => 'string',
        'admission_date' => '?string',
        'created_at' => 'string',
        'updated_at' => 'string',
    ];
    public const REQUIRED = ['admission_no', 'first_name'];
    public const ENUMS = [
        'gender' => ['male', 'female', 'other'],
        'status' => ['active', 'inactive', 'alumni', 'transferred'],
    ];
    public const LENGTHS = [
        'official_id' => 20,
        'admission_no' => 30,
        'roll_no' => 20,
        'first_name' => 60,
        'middle_name' => 60,
        'last_name' => 60,
        'birth_place' => 100,
        'nationality' => 60,
        'perm_number' => 30,
        'guardian_name' => 100,
        'guardian_relation' => 40,
        'guardian_phone' => 30,
        'guardian_email' => 120,
        'address' => 255,
        'city' => 60,
        'state' => 60,
        'zip' => 15,
        'phone' => 30,
        'email' => 120,
        'photo' => 255,
        'blood_group' => 10,
    ];
}
