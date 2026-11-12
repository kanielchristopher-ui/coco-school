<?php
declare(strict_types=1);

namespace CocoCore\Levels;

use CocoCore\Levels\Maternel\MaternelPromotionPolicy;
use CocoCore\Levels\Primaire\PrimairePromotionPolicy;
use CocoCore\Levels\Secondaire\SecondairePromotionPolicy;

/** Décision de passage selon les règles PROPRES à chaque niveau. */
final class PromotionService
{
    /**
     * @param float|null $yearPercent pourcentage annuel (null au maternel)
     * @param int|null $ageAtReference âge révolu à la date de référence de la rentrée (utile pour M3 → P1)
     */
    public function decide(string $classCode, ?float $yearPercent, ?int $ageAtReference = null, bool $retakeAllowed = true): PromotionDecision
    {
        return match (ClassLadder::levelOf($classCode)) {
            SchoolLevel::MATERNEL => (new MaternelPromotionPolicy())->decide($classCode, $ageAtReference),
            SchoolLevel::PRIMAIRE => (new PrimairePromotionPolicy())->decide($classCode, $yearPercent ?? 0.0),
            SchoolLevel::SECONDAIRE => (new SecondairePromotionPolicy())->decide($classCode, $yearPercent ?? 0.0, $retakeAllowed),
        };
    }
}
