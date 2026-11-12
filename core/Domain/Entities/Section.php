<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `sections`. */
final class Section extends Entity
{
    public const TABLE = 'sections';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'class_id' => 'int',
        'name' => 'string',
        'room_no' => '?string',
        'capacity' => '?int',
    ];
    public const REQUIRED = ['class_id', 'name'];
    public const ENUMS = [];
    public const LENGTHS = [
        'name' => 30,
        'room_no' => 20,
    ];
}
