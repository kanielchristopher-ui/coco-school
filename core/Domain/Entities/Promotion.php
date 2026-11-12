<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `promotions`. */
final class Promotion extends Entity
{
    public const TABLE = 'promotions';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'student_id' => 'int',
        'from_class_id' => '?int',
        'to_class_id' => '?int',
        'session_id' => '?int',
        'promoted_date' => '?string',
        'remarks' => '?string',
    ];
    public const REQUIRED = ['student_id'];
    public const ENUMS = [];
    public const LENGTHS = [
        'remarks' => 255,
    ];
}
