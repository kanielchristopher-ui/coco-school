<?php
declare(strict_types=1);

namespace CocoCore\Contracts;

use CocoCore\Events\DomainEvent;

interface EventDispatcherInterface
{
    public function listen(string $eventName, callable $listener): void;

    public function dispatch(DomainEvent $event): void;
}
