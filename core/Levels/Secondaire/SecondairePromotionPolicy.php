<?php
declare(strict_types=1);

namespace CocoCore\Levels\Secondaire;

use CocoCore\Levels\ClassLadder;
use CocoCore\Levels\PromotionDecision;

/**
 * Secondaire : ≥ 50 % = admis ; entre 40 et 50 % = examen de repêchage (si autorisé par l'établissement) ; < 40 % = redoublement.
 * La bande de repêchage (40-50 %) est une valeur PAR DÉFAUT à valider avec l'inspection/l'établissement.
 */
final class SecondairePromotionPolicy
{
    public const PASS = 50.0;
    public const RETAKE_FLOOR = 40.0;

    public function decide(string $classCode, float $yearPercent, bool $retakeAllowed = true): PromotionDecision
    {
        if ($yearPercent >= self::PASS) {
            if ($classCode === 'H4') {
                return new PromotionDecision(PromotionDecision::COMPLETED, null, 'Fin des humanités : candidat à l\'Examen d\'État.', true);
            }
            return new PromotionDecision(PromotionDecision::PROMOTED, ClassLadder::next($classCode), sprintf('Total annuel %.1f %% : admis.', $yearPercent));
        }
        if ($retakeAllowed && $yearPercent >= self::RETAKE_FLOOR) {
            return new PromotionDecision(PromotionDecision::RETAKE, $classCode, sprintf('Total annuel %.1f %% : examen de repêchage.', $yearPercent));
        }
        return new PromotionDecision(PromotionDecision::REPEAT, $classCode, sprintf('Total annuel %.1f %% : redoublement.', $yearPercent));
    }
}
