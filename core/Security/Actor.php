<?php
declare(strict_types=1);

namespace CocoCore\Security;

/**
 * Qui agit : indépendant de toute session Web.
 * $establishmentIds = null  -> accès à TOUS les établissements (administrateurs) ;
 * sinon liste des établissements (maternel / primaire / secondaire) que l'utilisateur gère.
 */
final class Actor
{
    /** @param list<int>|null $establishmentIds */
    public function __construct(
        public readonly int $id,
        public readonly string $role,
        public readonly int $schoolId = 1,
        public readonly string $name = '',
        public readonly ?array $establishmentIds = null
    ) {
    }

    public function canAccessEstablishment(int $establishmentId): bool
    {
        return $this->establishmentIds === null || in_array($establishmentId, $this->establishmentIds, true);
    }
}
