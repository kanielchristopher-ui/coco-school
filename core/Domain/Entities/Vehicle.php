<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `vehicles`. */
final class Vehicle extends Entity
{
    public const TABLE = 'vehicles';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'vehicle_no' => 'string',
        'model' => '?string',
        'capacity' => '?int',
        'driver_id' => '?int',
        'status' => 'string',
    ];
    public const REQUIRED = ['vehicle_no'];
    public const ENUMS = [
        'status' => ['active', 'inactive', 'maintenance'],
    ];
    public const LENGTHS = [
        'vehicle_no' => 30,
        'model' => 80,
    ];
}
