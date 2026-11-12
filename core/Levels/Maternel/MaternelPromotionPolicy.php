<?php
declare(strict_types=1);

namespace CocoCore\Levels\Maternel;

use CocoCore\Levels\ClassLadder;
use CocoCore\Levels\PromotionDecision;

/** Maternel : passage AUTOMATIQUE (pas d'échec) ; l'entrée en 1ère primaire exige 6 ans révolus. */
final class MaternelPromotionPolicy
{
    public function decide(string $classCode, ?int $ageAtReference): PromotionDecision
    {
        $next = ClassLadder::next($classCode);
        if ($classCode === 'M3') {
            if ($ageAtReference !== null && $ageAtReference < 6) {
                return new PromotionDecision(PromotionDecision::REPEAT, 'M3', 'Âge insuffisant pour la 1ère primaire (6 ans révolus requis) : maintien en 3ème maternelle.');
            }
            return new PromotionDecision(PromotionDecision::PROMOTED, $next, 'Admis en 1ère primaire (établissement primaire).');
        }
        return new PromotionDecision(PromotionDecision::PROMOTED, $next, 'Passage automatique.');
    }
}
