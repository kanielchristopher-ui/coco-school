<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `school_extensions`. */
final class SchoolExtension extends Entity
{
    public const TABLE = 'school_extensions';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'school_id' => 'int',
        'plugin_id' => 'string',
        'name' => 'string',
        'version' => 'string',
        'package_sha256' => 'string',
        'manifest_json' => '?string',
        'installed_by' => '?int',
        'installed_at' => 'string',
        'status' => 'string',
    ];
    public const REQUIRED = ['plugin_id', 'name', 'version', 'package_sha256'];
    public const ENUMS = [
        'status' => ['active', 'disabled'],
    ];
    public const LENGTHS = [
        'plugin_id' => 80,
        'name' => 150,
        'version' => 30,
        'package_sha256' => 64,
    ];
}
