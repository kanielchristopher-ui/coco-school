<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `user_establishments`. */
final class UserEstablishment extends Entity
{
    public const TABLE = 'user_establishments';
    public const PRIMARY_KEY = ['user_id', 'establishment_id'];
    public const FIELDS = [
        'user_id' => 'int',
        'establishment_id' => 'int',
    ];
    public const REQUIRED = ['user_id', 'establishment_id'];
    public const ENUMS = [];
    public const LENGTHS = [];
}
