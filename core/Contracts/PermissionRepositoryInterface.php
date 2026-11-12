<?php
declare(strict_types=1);

namespace CocoCore\Contracts;

interface PermissionRepositoryInterface
{
    /** @return array<string,list<string>> rôle (slug) => permissions (slugs) */
    public function matrix(): array;
}
