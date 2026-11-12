<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `drivers`. */
final class Driver extends Entity
{
    public const TABLE = 'drivers';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'name' => 'string',
        'license_no' => '?string',
        'phone' => '?string',
        'address' => '?string',
        'status' => 'string',
    ];
    public const REQUIRED = ['name'];
    public const ENUMS = [
        'status' => ['active', 'inactive'],
    ];
    public const LENGTHS = [
        'name' => 100,
        'license_no' => 60,
        'phone' => 30,
        'address' => 255,
    ];
}
