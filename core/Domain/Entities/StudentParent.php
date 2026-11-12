<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `student_parents`. */
final class StudentParent extends Entity
{
    public const TABLE = 'student_parents';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'student_id' => 'int',
        'parent_id' => 'int',
    ];
    public const REQUIRED = ['student_id', 'parent_id'];
    public const ENUMS = [];
    public const LENGTHS = [];
}
