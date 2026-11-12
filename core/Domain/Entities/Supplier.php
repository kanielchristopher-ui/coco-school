<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `suppliers`. */
final class Supplier extends Entity
{
    public const TABLE = 'suppliers';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'name' => 'string',
        'contact_person' => '?string',
        'phone' => '?string',
        'email' => '?string',
        'address' => '?string',
    ];
    public const REQUIRED = ['name'];
    public const ENUMS = [];
    public const LENGTHS = [
        'name' => 120,
        'contact_person' => 80,
        'phone' => 30,
        'email' => 120,
        'address' => 255,
    ];
}
