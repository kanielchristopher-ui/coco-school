<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `subjects`. */
final class Subject extends Entity
{
    public const TABLE = 'subjects';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'name' => 'string',
        'code' => '?string',
        'class_id' => '?int',
        'teacher_id' => '?int',
        'credits' => '?int',
        'description' => '?string',
        'created_at' => 'string',
        'weekly_hours' => '?int',
    ];
    public const REQUIRED = ['name'];
    public const ENUMS = [];
    public const LENGTHS = [
        'name' => 80,
        'code' => 20,
    ];
}
