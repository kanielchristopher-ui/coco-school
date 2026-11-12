<?php
declare(strict_types=1);

namespace CocoCore\Security;

/**
 * Politique d'accès aux bulletins périodiques et aux extensions.
 *
 * Adaptation au système de rôles de l'application :
 *  - level_code « primaire » = maternel + primaire  → géré par le DIRECTEUR D'ÉCOLE (director)
 *  - level_code « secondaire »                        → géré par le PRÉFET (prefet)
 *  - administrateur / super administrateur            → tous les niveaux
 *  - directeur des études (proviseur)                 → peut vérifier/valider au secondaire (pas publier)
 *  - enseignant : saisit les points de ses cours ; parent/élève : bulletins publiés uniquement
 */
final class SchoolRolePolicy
{
    private const ADMINS = ['super_admin', 'admin'];

    public function canManageExtensions(string $role, string $level): bool
    {
        if (in_array($role, self::ADMINS, true)) {
            return true;
        }
        return match ($this->normalizeLevel($level)) {
            'primaire'   => $role === 'director',
            'secondaire' => $role === 'prefet',
            default      => false,
        };
    }

    public function canCreateOrEditTemplate(string $role, string $level): bool
    {
        return $this->canManageExtensions($role, $level);
    }

    public function canEditScheme(string $role, string $level): bool
    {
        return $this->canManageExtensions($role, $level);
    }

    public function canPublish(string $role, string $level): bool
    {
        return $this->canManageExtensions($role, $level);
    }

    public function canValidate(string $role, string $level): bool
    {
        return $this->canManageExtensions($role, $level)
            || ($role === 'proviseur' && $this->normalizeLevel($level) === 'secondaire');
    }

    public function canEnterMarks(string $role, bool $isAssignedTeacher): bool
    {
        return $role === 'teacher' && $isAssignedTeacher;
    }

    public function canViewBulletin(string $role, string $bulletinStatus, int $viewerUserId, int $studentUserId, bool $isLinkedParent): bool
    {
        if ($role === 'student') {
            return $bulletinStatus === 'published' && $viewerUserId === $studentUserId;
        }
        if ($role === 'parent') {
            return $bulletinStatus === 'published' && $isLinkedParent;
        }
        // Personnel : brouillons et bulletins validés visibles pour contrôle ; publiés pour tous.
        return in_array($role, ['super_admin', 'admin', 'director', 'prefet', 'proviseur', 'teacher'], true);
    }

    /** maternelle/maternel/primaire → « primaire » ; secondaire → « secondaire ». */
    public function normalizeLevel(string $level): string
    {
        return match ($level) {
            'maternelle', 'maternel', 'primaire' => 'primaire',
            'secondaire'                          => 'secondaire',
            default                               => $level,
        };
    }
}
