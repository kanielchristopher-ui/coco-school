<?php
declare(strict_types=1);

namespace CocoCore\Modules\Auth;

use CocoCore\Contracts\AuditLogInterface;
use CocoCore\Contracts\UserRepositoryInterface;
use CocoCore\Exceptions\BusinessRuleException;
use CocoCore\Exceptions\ValidationException;
use CocoCore\Security\Actor;
use CocoCore\Security\PasswordHasher;

/**
 * Authentification SANS session : retourne un Actor. La couche Web (ou une API)
 * décide comment mémoriser la connexion (session PHP, jeton d'appareil…).
 */
final class AuthService
{
    /** Rôles sans restriction d'établissement. */
    public const UNRESTRICTED_ROLES = ['super_admin', 'admin'];

    public function __construct(
        private UserRepositoryInterface $users,
        private PasswordHasher $hasher,
        private AuditLogInterface $audit
    ) {
    }

    public function attempt(string $login, string $password): Actor
    {
        $login = trim($login);
        if ($login === '' || $password === '') {
            throw new ValidationException(['login' => 'Identifiant et mot de passe obligatoires.']);
        }
        $user = $this->users->findByLogin($login);
        if ($user === null || !$this->hasher->verify($password, (string) $user['password'])) {
            $this->audit->record($user['id'] ?? null, 'login_failed', 'user', $user['id'] ?? null, ['login' => $login]);
            // Message volontairement identique : ne pas révéler si le compte existe.
            throw new BusinessRuleException('Identifiants invalides.');
        }
        if ($user['status'] !== 'active') {
            $this->audit->record((int) $user['id'], 'login_blocked', 'user', (int) $user['id'], ['status' => $user['status']]);
            throw new BusinessRuleException('Compte inactif ou suspendu.');
        }
        if ($this->hasher->needsRehash((string) $user['password'])) {
            $this->users->updatePasswordHash((int) $user['id'], $this->hasher->hash($password));
        }
        $this->audit->record((int) $user['id'], 'login', 'user', (int) $user['id']);
        // Administrateurs : tous les établissements. Autres rôles : uniquement ceux auxquels ils sont rattachés.
        $scope = in_array($user['role_slug'], self::UNRESTRICTED_ROLES, true) ? null : $this->users->establishmentIds((int) $user['id']);
        return new Actor((int) $user['id'], (string) $user['role_slug'], 1, trim($user['first_name'] . ' ' . $user['last_name']), $scope);
    }
}
