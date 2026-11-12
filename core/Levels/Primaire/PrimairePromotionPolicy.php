<?php
declare(strict_types=1);

namespace CocoCore\Levels\Primaire;

use CocoCore\Levels\ClassLadder;
use CocoCore\Levels\PromotionDecision;

/** Primaire : réussite à 50 % du total annuel ; en 6ème, la réussite ouvre le TENAFEP puis l'éducation de base (7ème). */
final class PrimairePromotionPolicy
{
    public const PASS = 50.0;

    public function decide(string $classCode, float $yearPercent): PromotionDecision
    {
        if ($yearPercent < self::PASS) {
            return new PromotionDecision(PromotionDecision::REPEAT, $classCode, sprintf('Total annuel %.1f %% < %.0f %% : redoublement.', $yearPercent, self::PASS));
        }
        if ($classCode === 'P6') {
            return new PromotionDecision(PromotionDecision::COMPLETED, ClassLadder::next('P6'), 'Fin du primaire : candidat au TENAFEP ; admission en 7ème (éducation de base) selon le résultat.', true);
        }
        return new PromotionDecision(PromotionDecision::PROMOTED, ClassLadder::next($classCode), sprintf('Total annuel %.1f %% : admis.', $yearPercent));
    }
}
