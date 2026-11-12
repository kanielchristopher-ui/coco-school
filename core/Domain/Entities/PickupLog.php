<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `pickup_logs`. */
final class PickupLog extends Entity
{
    public const TABLE = 'pickup_logs';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'student_id' => 'int',
        'picked_by_name' => 'string',
        'authorized' => 'bool',
        'recorded_by' => '?int',
        'picked_at' => 'string',
    ];
    public const REQUIRED = ['student_id', 'picked_by_name', 'authorized'];
    public const ENUMS = [];
    public const LENGTHS = [
        'picked_by_name' => 120,
    ];
}
