<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `messages`. */
final class Message extends Entity
{
    public const TABLE = 'messages';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'sender_id' => 'int',
        'receiver_id' => 'int',
        'subject' => '?string',
        'body' => '?string',
        'is_read' => 'bool',
        'read_at' => '?string',
        'parent_id' => '?int',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['sender_id', 'receiver_id'];
    public const ENUMS = [];
    public const LENGTHS = [
        'subject' => 150,
    ];
}
