<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `users`. */
final class User extends Entity
{
    public const TABLE = 'users';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'role_id' => 'int',
        'username' => 'string',
        'email' => 'string',
        'password' => 'string',
        'first_name' => '?string',
        'last_name' => '?string',
        'phone' => '?string',
        'photo' => '?string',
        'status' => 'string',
        'last_login' => '?string',
        'created_at' => 'string',
        'updated_at' => 'string',
    ];
    public const REQUIRED = ['role_id', 'username', 'email', 'password'];
    public const ENUMS = [
        'status' => ['active', 'inactive', 'suspended', 'pending'],
    ];
    public const LENGTHS = [
        'username' => 60,
        'email' => 120,
        'password' => 255,
        'first_name' => 60,
        'last_name' => 60,
        'phone' => 30,
        'photo' => 255,
    ];
}
