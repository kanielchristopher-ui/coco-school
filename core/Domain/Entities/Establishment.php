<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `establishments`. */
final class Establishment extends Entity
{
    public const TABLE = 'establishments';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'code' => 'string',
        'name' => 'string',
        'level' => 'string',
        'regime' => 'string',
        'head_title' => 'string',
        'head_name' => '?string',
        'province' => '?string',
        'city' => '?string',
        'address' => '?string',
        'phone' => '?string',
        'email' => '?string',
        'status' => 'string',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['code', 'name', 'level'];
    public const ENUMS = [
        'level' => ['maternel', 'primaire', 'secondaire'],
        'regime' => ['public', 'conventionne_catholique', 'conventionne_protestant', 'conventionne_kimbanguiste', 'conventionne_autre', 'prive'],
        'status' => ['active', 'inactive'],
    ];
    public const LENGTHS = [
        'code' => 30,
        'name' => 150,
        'head_title' => 60,
        'head_name' => 120,
        'province' => 80,
        'city' => 80,
        'address' => 255,
        'phone' => 30,
        'email' => 120,
    ];
}
