<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `transport_assignments`. */
final class TransportAssignment extends Entity
{
    public const TABLE = 'transport_assignments';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'student_id' => 'int',
        'route_id' => '?int',
        'vehicle_id' => '?int',
        'pickup_point' => '?string',
        'amount' => 'float',
        'status' => 'string',
    ];
    public const REQUIRED = ['student_id'];
    public const ENUMS = [
        'status' => ['active', 'inactive'],
    ];
    public const LENGTHS = [
        'pickup_point' => 120,
    ];
}
