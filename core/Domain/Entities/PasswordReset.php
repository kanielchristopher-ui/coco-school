<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `password_resets`. */
final class PasswordReset extends Entity
{
    public const TABLE = 'password_resets';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'user_id' => 'int',
        'email' => 'string',
        'token' => 'string',
        'expires_at' => 'string',
        'used' => 'bool',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['user_id', 'email', 'token', 'expires_at'];
    public const ENUMS = [];
    public const LENGTHS = [
        'email' => 120,
        'token' => 255,
    ];
}
