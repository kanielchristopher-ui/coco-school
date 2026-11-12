<?php
declare(strict_types=1);

namespace CocoCore\Levels\Secondaire;

/** Note de conduite du secondaire : 100 points moins des pénalités selon la gravité des incidents (barème à valider). */
final class ConductService
{
    public const PENALTY = ['minor' => 2.0, 'moderate' => 5.0, 'serious' => 15.0];

    /** @param list<array{severity:string}> $incidents @return array{score:float,mention:string,incident_count:int} */
    public function evaluate(array $incidents): array
    {
        $score = 100.0;
        foreach ($incidents as $i) {
            $score -= self::PENALTY[$i['severity']] ?? 0.0;
        }
        $score = max(0.0, round($score, 2));
        return ['score' => $score, 'mention' => $this->mention($score), 'incident_count' => count($incidents)];
    }

    public function mention(float $score): string
    {
        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 75 => 'Très bien',
            $score >= 60 => 'Bien',
            $score >= 50 => 'Passable',
            default => 'Insuffisant',
        };
    }
}
