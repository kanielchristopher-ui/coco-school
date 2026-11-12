<?php
declare(strict_types=1);

namespace CocoCore\Levels\Secondaire;

/** Examen d'État (fin des humanités, 4ème). Mentions par défaut à valider avec l'inspection. */
final class ExetatService
{
    public const PASS = 50.0;
    private const MENTIONS = [[80.0, 'Plus grande distinction'], [70.0, 'Grande distinction'], [60.0, 'Distinction'], [50.0, 'Satisfaction']];

    public function eligible(string $classCode, bool $yearPassed): bool
    {
        return $classCode === 'H4' && $yearPassed;
    }

    public function passed(float $percent): bool
    {
        return $percent >= self::PASS;
    }

    public function mention(float $percent): string
    {
        foreach (self::MENTIONS as [$min, $label]) {
            if ($percent >= $min) {
                return $label;
            }
        }
        return 'Échec';
    }

    public function candidateNumber(int $year, string $schoolCode, string $optionCode, int $sequence): string
    {
        return sprintf('EXE-%d-%s-%s-%04d', $year, preg_replace('/[^A-Z0-9]/', '', strtoupper($schoolCode)), strtoupper($optionCode), $sequence);
    }
}
