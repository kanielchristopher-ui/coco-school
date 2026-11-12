<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `events`. */
final class Event extends Entity
{
    public const TABLE = 'events';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'title' => 'string',
        'description' => '?string',
        'event_date' => 'string',
        'event_time' => '?string',
        'event_type' => 'string',
        'location' => '?string',
        'created_by' => '?int',
    ];
    public const REQUIRED = ['title', 'event_date'];
    public const ENUMS = [
        'event_type' => ['event', 'meeting', 'holiday', 'exam', 'sport'],
    ];
    public const LENGTHS = [
        'title' => 150,
        'location' => 120,
    ];
}
