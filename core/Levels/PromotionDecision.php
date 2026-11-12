<?php
declare(strict_types=1);

namespace CocoCore\Levels;

/** Décision de fin d'année pour un élève. */
final class PromotionDecision
{
    public const PROMOTED = 'promoted';
    public const REPEAT = 'repeat';
    public const RETAKE = 'retake';           // repêchage / examen de reprise
    public const COMPLETED = 'completed';     // fin du cycle (candidat à l'examen national / sortie)

    public function __construct(
        public readonly string $outcome,
        public readonly ?string $nextClass,
        public readonly string $note = '',
        public readonly bool $nationalExamEligible = false
    ) {
    }
}
