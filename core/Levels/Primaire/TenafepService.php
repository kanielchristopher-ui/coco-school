<?php
declare(strict_types=1);

namespace CocoCore\Levels\Primaire;

/**
 * TENAFEP (test national de fin d'études primaires, 6ème année).
 * Certificat : 60 % des résultats de l'école + 40 % du test (pondération indiquée par les sources publiques ; à valider).
 */
final class TenafepService
{
    public const SCHOOL_WEIGHT = 0.60;
    public const EXAM_WEIGHT = 0.40;
    public const PASS = 50.0;

    public function eligible(string $classCode, bool $yearPassed): bool
    {
        return $classCode === 'P6' && $yearPassed;
    }

    public function finalPercent(float $schoolPercent, float $examPercent): float
    {
        return round($schoolPercent * self::SCHOOL_WEIGHT + $examPercent * self::EXAM_WEIGHT, 2);
    }

    public function passed(float $finalPercent): bool
    {
        return $finalPercent >= self::PASS;
    }

    /** Numéro de candidat : TEN-AAAA-CODEÉCOLE-NNNN. */
    public function candidateNumber(int $year, string $schoolCode, int $sequence): string
    {
        return sprintf('TEN-%d-%s-%04d', $year, preg_replace('/[^A-Z0-9]/', '', strtoupper($schoolCode)), $sequence);
    }
}
