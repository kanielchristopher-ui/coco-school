<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `student_fees`. */
final class StudentFee extends Entity
{
    public const TABLE = 'student_fees';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'student_id' => 'int',
        'class_id' => '?int',
        'fee_type' => 'string',
        'amount' => 'float',
        'academic_session' => 'string',
        'month' => '?string',
        'due_date' => '?string',
        'status' => 'string',
        'created_by' => '?int',
        'created_at' => 'string',
        'updated_at' => 'string',
    ];
    public const REQUIRED = ['student_id', 'fee_type', 'academic_session'];
    public const ENUMS = [
        'status' => ['pending', 'partial', 'paid'],
    ];
    public const LENGTHS = [
        'fee_type' => 40,
        'academic_session' => 60,
        'month' => 20,
    ];
}
