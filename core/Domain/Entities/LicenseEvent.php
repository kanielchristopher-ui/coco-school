<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `license_events`. */
final class LicenseEvent extends Entity
{
    public const TABLE = 'license_events';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'event' => 'string',
        'details_json' => '?string',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['event'];
    public const ENUMS = [];
    public const LENGTHS = [
        'event' => 40,
    ];
}
