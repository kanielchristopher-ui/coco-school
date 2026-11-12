<?php
declare(strict_types=1);

namespace CocoCore\Database;

use CocoCore\Contracts\AuditLogInterface;

/** Journal d'audit du Core (table core_audit_log, migration 0002). */
final class PdoAuditLog implements AuditLogInterface
{
    public function __construct(private Connection $db)
    {
    }

    public function record(?int $actorId, string $action, string $entity, ?int $entityId, array $details = []): void
    {
        $this->db->insert('core_audit_log', [
            'actor_id' => $actorId, 'action' => $action, 'entity' => $entity, 'entity_id' => $entityId,
            'details_json' => $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }
}
