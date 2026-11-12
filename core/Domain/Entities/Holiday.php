<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `holidays`. */
final class Holiday extends Entity
{
    public const TABLE = 'holidays';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'title' => 'string',
        'start_date' => 'string',
        'end_date' => 'string',
        'description' => '?string',
    ];
    public const REQUIRED = ['title', 'start_date', 'end_date'];
    public const ENUMS = [];
    public const LENGTHS = [
        'title' => 150,
        'description' => 255,
    ];
}
