<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `child_health_records`. */
final class ChildHealthRecord extends Entity
{
    public const TABLE = 'child_health_records';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'student_id' => 'int',
        'vaccinations' => '?string',
        'allergies' => '?string',
        'medical_notes' => '?string',
        'doctor_name' => '?string',
        'doctor_phone' => '?string',
        'emergency_contact_name' => '?string',
        'emergency_contact_phone' => '?string',
        'updated_at' => 'string',
    ];
    public const REQUIRED = ['student_id'];
    public const ENUMS = [];
    public const LENGTHS = [
        'doctor_name' => 120,
        'doctor_phone' => 30,
        'emergency_contact_name' => 120,
        'emergency_contact_phone' => 30,
    ];
}
