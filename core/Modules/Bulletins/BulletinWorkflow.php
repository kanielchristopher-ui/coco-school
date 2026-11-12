<?php
declare(strict_types=1);

namespace CocoCore\Modules\Bulletins;

use CocoCore\Exceptions\BusinessRuleException;
use CocoCore\Security\SchoolRolePolicy;

/**
 * Cycle de vie d'un bulletin : draft -> validated -> published -> superseded.
 * Un bulletin publié n'est JAMAIS modifié : toute correction crée une nouvelle version.
 */
final class BulletinWorkflow
{
    public const DRAFT = 'draft';
    public const VALIDATED = 'validated';
    public const PUBLISHED = 'published';
    public const SUPERSEDED = 'superseded';

    private const TRANSITIONS = [
        self::DRAFT => [self::VALIDATED],
        self::VALIDATED => [self::PUBLISHED, self::DRAFT],
        self::PUBLISHED => [self::SUPERSEDED],
        self::SUPERSEDED => [],
    ];

    public function __construct(private SchoolRolePolicy $policy = new SchoolRolePolicy())
    {
    }

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    /**
     * Vérifie la transition, le rôle et (pour la validation) la complétude des notes.
     * @throws BusinessRuleException
     */
    public function assertTransition(string $role, string $level, string $from, string $to, bool $marksComplete): void
    {
        if (!$this->canTransition($from, $to)) {
            throw new BusinessRuleException(sprintf('Transition « %s » → « %s » interdite.', $from, $to));
        }
        $allowed = $to === self::PUBLISHED ? $this->policy->canPublish($role, $level) : $this->policy->canValidate($role, $level);
        if (!$allowed) {
            throw new BusinessRuleException('Ce rôle ne peut pas effectuer cette action sur ce niveau.');
        }
        if ($to === self::VALIDATED && !$marksComplete) {
            throw new BusinessRuleException('Toutes les notes doivent être saisies avant la validation.');
        }
    }

    /** Un bulletin publié verrouille la saisie : une nouvelle version est nécessaire. */
    public function locksMarks(string $latestStatus): bool
    {
        return $latestStatus === self::PUBLISHED;
    }
}
