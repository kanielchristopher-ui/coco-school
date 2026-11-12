<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `core_audit_log`. */
final class CoreAuditLog extends Entity
{
    public const TABLE = 'core_audit_log';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'actor_id' => '?int',
        'action' => 'string',
        'entity' => 'string',
        'entity_id' => '?int',
        'details_json' => '?string',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['action', 'entity'];
    public const ENUMS = [];
    public const LENGTHS = [
        'action' => 60,
        'entity' => 60,
    ];
}
