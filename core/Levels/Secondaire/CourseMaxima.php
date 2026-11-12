<?php
declare(strict_types=1);

namespace CocoCore\Levels\Secondaire;

/**
 * Maxima d'un cours = fonction de son poids horaire. Valeurs PAR DÉFAUT (à valider/adapter) :
 *   1-2 h/sem → période 10 ; 3-4 h → 20 ; 5-6 h → 30 ; 7 h et + → 40 ; examen = 2 × période.
 * Un cours peut surcharger (subjects.period_max / exam_max).
 */
final class CourseMaxima
{
    /** @return array{period:float,exam:float} */
    public static function forWeeklyHours(int $hours, ?float $periodOverride = null, ?float $examOverride = null): array
    {
        $period = match (true) {
            $hours <= 2 => 10.0,
            $hours <= 4 => 20.0,
            $hours <= 6 => 30.0,
            default => 40.0,
        };
        $period = $periodOverride ?? $period;
        return ['period' => $period, 'exam' => $examOverride ?? $period * 2];
    }
}
