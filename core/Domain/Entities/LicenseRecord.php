<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `licenses`. */
final class LicenseRecord extends Entity
{
    public const TABLE = 'licenses';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'license_id' => 'string',
        'token' => 'string',
        'holder' => 'string',
        'valid_from' => 'string',
        'valid_until' => 'string',
        'installed_by' => '?int',
        'installed_at' => 'string',
        'status' => 'string',
    ];
    public const REQUIRED = ['license_id', 'token', 'holder', 'valid_from', 'valid_until'];
    public const ENUMS = [
        'status' => ['active', 'replaced', 'revoked'],
    ];
    public const LENGTHS = [
        'license_id' => 64,
        'holder' => 200,
    ];
}
