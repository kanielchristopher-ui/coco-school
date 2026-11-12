<?php
// GÉNÉRÉ par tools/generate_entities.py depuis database/migrations — NE PAS MODIFIER À LA MAIN.
declare(strict_types=1);

namespace CocoCore\Domain\Entities;

use CocoCore\Domain\Entity;

/** Table `bulletin_audit_log`. */
final class BulletinAuditLog extends Entity
{
    public const TABLE = 'bulletin_audit_log';
    public const PRIMARY_KEY = ['id'];
    public const FIELDS = [
        'id' => 'int',
        'bulletin_id' => 'int',
        'actor_id' => '?int',
        'action' => 'string',
        'details_json' => '?string',
        'created_at' => 'string',
    ];
    public const REQUIRED = ['bulletin_id', 'action'];
    public const ENUMS = [];
    public const LENGTHS = [
        'action' => 40,
    ];
}
