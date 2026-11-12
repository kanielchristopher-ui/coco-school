<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `official_id_counters`. */
final class OfficialIdCounter extends Entity
{
    public const TABLE = 'official_id_counters';
    public const PRIMARY_KEY = ['prefix'];
    public const FIELDS = [
        'prefix' => 'string',
        'last_number' => 'int',
    ];
    public const REQUIRED = ['prefix'];
    public const ENUMS = [];
    public const LENGTHS = [
        'prefix' => 10,
    ];
}
