<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `remember_tokens`. */
final class RememberToken extends Entity
{
    public const TABLE = 'remember_tokens';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'user_id' => 'int',
        'selector' => 'string',
        'token_hash' => 'string',
        'expires_at' => 'string',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['user_id', 'selector', 'token_hash', 'expires_at'];
    public const ENUMS = [];
    public const LENGTHS = [
        'selector' => 64,
        'token_hash' => 255,
    ];
}
