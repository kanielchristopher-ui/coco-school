<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `parents`. */
final class Guardian extends Entity
{
    public const TABLE = 'parents';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'user_id' => '?int',
        'official_id' => '?string',
        'father_name' => '?string',
        'mother_name' => '?string',
        'guardian_name' => '?string',
        'occupation' => '?string',
        'phone' => '?string',
        'email' => '?string',
        'address' => '?string',
        'created_at' => 'string',
    ];
    public const REQUIRED = [];
    public const ENUMS = [];
    public const LENGTHS = [
        'official_id' => 20,
        'father_name' => 100,
        'mother_name' => 100,
        'guardian_name' => 100,
        'occupation' => 100,
        'phone' => 30,
        'email' => 120,
        'address' => 255,
    ];
}
