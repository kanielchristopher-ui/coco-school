<?php
declare(strict_types=1);

namespace CocoCore\Events;

use CocoCore\Contracts\EventDispatcherInterface;

/** Dispatcher synchrone. Les écouteurs reçoivent le DomainEvent ; '*' écoute tout. */
final class EventDispatcher implements EventDispatcherInterface
{
    /** @var array<string,list<callable>> */
    private array $listeners = [];

    public function listen(string $eventName, callable $listener): void
    {
        $this->listeners[$eventName][] = $listener;
    }

    public function dispatch(DomainEvent $event): void
    {
        foreach (array_merge($this->listeners[$event->name] ?? [], $this->listeners['*'] ?? []) as $listener) {
            $listener($event);
        }
    }
}
