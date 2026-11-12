<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `settings`. */
final class Setting extends Entity
{
    public const TABLE = 'settings';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'setting_key' => 'string',
        'setting_value' => '?string',
    ];
    public const REQUIRED = ['setting_key'];
    public const ENUMS = [];
    public const LENGTHS = [
        'setting_key' => 80,
    ];
}
