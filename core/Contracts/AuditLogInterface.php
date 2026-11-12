<?php
declare(strict_types=1);

namespace CocoCore\Contracts;

interface AuditLogInterface
{
    /** @param array<string,mixed> $details */
    public function record(?int $actorId, string $action, string $entity, ?int $entityId, array $details = []): void;
}
