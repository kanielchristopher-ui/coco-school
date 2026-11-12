<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `exam_types`. */
final class ExamType extends Entity
{
    public const TABLE = 'exam_types';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'name' => 'string',
        'description' => '?string',
    ];
    public const REQUIRED = ['name'];
    public const ENUMS = [];
    public const LENGTHS = [
        'name' => 60,
        'description' => 255,
    ];
}
