<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `activity_logs`. */
final class ActivityLog extends Entity
{
    public const TABLE = 'activity_logs';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'user_id' => '?int',
        'action' => 'string',
        'module' => '?string',
        'description' => '?string',
        'ip_address' => '?string',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['action'];
    public const ENUMS = [];
    public const LENGTHS = [
        'action' => 80,
        'module' => 60,
        'description' => 255,
        'ip_address' => 45,
    ];
}
