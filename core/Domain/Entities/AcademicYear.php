<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `academic_sessions`. */
final class AcademicYear extends Entity
{
    public const TABLE = 'academic_sessions';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'name' => 'string',
        'start_date' => '?string',
        'end_date' => '?string',
        'is_current' => 'bool',
    ];
    public const REQUIRED = ['name'];
    public const ENUMS = [];
    public const LENGTHS = [
        'name' => 60,
    ];
}
