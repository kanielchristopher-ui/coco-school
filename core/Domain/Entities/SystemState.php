<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `system_state`. */
final class SystemState extends Entity
{
    public const TABLE = 'system_state';
    public const PRIMARY_KEY = ['key_name'];
    public const FIELDS = [
        'key_name' => 'string',
        'value_text' => '?string',
        'updated_at' => 'string',
    ];
    public const REQUIRED = ['key_name'];
    public const ENUMS = [];
    public const LENGTHS = [
        'key_name' => 60,
    ];
}
