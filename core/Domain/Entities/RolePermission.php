<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `role_permissions`. */
final class RolePermission extends Entity
{
    public const TABLE = 'role_permissions';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'role_id' => 'int',
        'permission_id' => 'int',
    ];
    public const REQUIRED = ['role_id', 'permission_id'];
    public const ENUMS = [];
    public const LENGTHS = [];
}
