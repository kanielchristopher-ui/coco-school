<?php
declare(strict_types=1);

namespace CocoCore\Levels\Maternel;

use CocoCore\Exceptions\ValidationException;

/**
 * Évaluation QUALITATIVE du maternel par domaine de développement : acquis / en cours d'acquisition / non acquis.
 * Pas de points, pas de pourcentage, pas de classement.
 */
final class DevelopmentAssessmentService
{
    public const LEVELS = ['acquired', 'in_progress', 'not_acquired'];
    /** Part minimale de domaines « acquis » pour recommander l'entrée en primaire (à valider avec l'école). */
    public const READY_RATE = 0.6;

    public function assertLevel(string $level): void
    {
        if (!in_array($level, self::LEVELS, true)) {
            throw new ValidationException(['level' => 'Niveau d\'acquisition invalide (acquired, in_progress, not_acquired).']);
        }
    }

    /**
     * @param list<array{domain_code:string,level:string}> $assessments appréciations d'une période
     * @return array{acquired:int,in_progress:int,not_acquired:int,total:int,acquired_rate:float,ready_for_primary:bool,attention:list<string>}
     */
    public function summarize(array $assessments): array
    {
        $count = ['acquired' => 0, 'in_progress' => 0, 'not_acquired' => 0];
        $attention = [];
        foreach ($assessments as $a) {
            $this->assertLevel((string) $a['level']);
            $count[$a['level']]++;
            if ($a['level'] === 'not_acquired') {
                $attention[] = (string) $a['domain_code'];
            }
        }
        $total = array_sum($count);
        $rate = $total > 0 ? round($count['acquired'] / $total, 4) : 0.0;
        return $count + [
            'total' => $total, 'acquired_rate' => $rate,
            // les domaines langage et psychomotricité conditionnent l'entrée en primaire
            'ready_for_primary' => $total > 0 && $rate >= self::READY_RATE && !array_intersect($attention, ['LANG', 'MOT']),
            'attention' => $attention,
        ];
    }
}
