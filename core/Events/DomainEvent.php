<?php
declare(strict_types=1);

namespace CocoCore\Events;

/** Événement métier immuable. */
class DomainEvent
{
    public readonly \DateTimeImmutable $occurredAt;

    /** @param array<string,mixed> $payload */
    public function __construct(public readonly string $name, public readonly array $payload = [])
    {
        $this->occurredAt = new \DateTimeImmutable();
    }
}
