<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `authorized_pickups`. */
final class AuthorizedPickup extends Entity
{
    public const TABLE = 'authorized_pickups';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'student_id' => 'int',
        'full_name' => 'string',
        'relation' => 'string',
        'phone' => '?string',
        'id_document' => '?string',
        'active' => 'bool',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['student_id', 'full_name', 'relation'];
    public const ENUMS = [];
    public const LENGTHS = [
        'full_name' => 120,
        'relation' => 60,
        'phone' => 30,
        'id_document' => 60,
    ];
}
