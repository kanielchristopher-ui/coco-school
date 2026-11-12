<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `notifications`. */
final class Notification extends Entity
{
    public const TABLE = 'notifications';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'user_id' => 'int',
        'title' => 'string',
        'message' => '?string',
        'link' => '?string',
        'type' => '?string',
        'is_read' => 'bool',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['user_id', 'title'];
    public const ENUMS = [];
    public const LENGTHS = [
        'title' => 150,
        'link' => 255,
        'type' => 40,
    ];
}
