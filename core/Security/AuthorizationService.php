<?php
declare(strict_types=1);

namespace CocoCore\Security;

use CocoCore\Contracts\PermissionRepositoryInterface;
use CocoCore\Exceptions\PermissionDeniedException;

/** Applique les permissions CÔTÉ CORE (la matrice vient de la base : roles x permissions). */
final class AuthorizationService
{
    /** @var array<string,list<string>>|null */
    private ?array $matrix = null;

    /** @param list<string> $superRoles rôles qui ont toutes les permissions */
    public function __construct(private PermissionRepositoryInterface $permissions, private array $superRoles = ['super_admin'])
    {
    }

    public function can(Actor $actor, string $permission): bool
    {
        if (in_array($actor->role, $this->superRoles, true)) {
            return true;
        }
        $this->matrix ??= $this->permissions->matrix();
        return in_array($permission, $this->matrix[$actor->role] ?? [], true);
    }

    public function authorize(Actor $actor, string $permission): void
    {
        if (!$this->can($actor, $permission)) {
            throw new PermissionDeniedException(sprintf('Le rôle « %s » n\'a pas la permission « %s ».', $actor->role, $permission));
        }
    }
}
