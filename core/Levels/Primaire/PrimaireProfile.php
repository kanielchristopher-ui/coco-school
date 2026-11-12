<?php
declare(strict_types=1);

namespace CocoCore\Levels\Primaire;

use CocoCore\Levels\LevelProfile;
use CocoCore\Levels\SchoolLevel;

/** École primaire : 6 ans en 3 degrés (élémentaire, moyen, terminal), admission à 6 ans révolus, 3 trimestres, TENAFEP en 6ème. */
final class PrimaireProfile implements LevelProfile
{
    public function level(): SchoolLevel
    {
        return SchoolLevel::PRIMAIRE;
    }

    public function classLadder(): array
    {
        $l = [];
        foreach (range(1, 6) as $n) {
            $l[] = ['code' => 'P' . $n, 'label' => $n === 1 ? '1ère Primaire' : $n . 'ème Primaire', 'rank' => $n];
        }
        return $l;
    }

    public function admissionAge(): array
    {
        return ['min' => 6, 'max' => 12];
    }

    public function evaluationMode(): string
    {
        return 'numeric';
    }

    public function schemeId(): string
    {
        return 'rdc-primaire';
    }

    public function usesRanking(): bool
    {
        return true;
    }

    public function passMark(): ?float
    {
        return 50.0;
    }

    public function nationalExam(): ?string
    {
        return 'TENAFEP';
    }

    public function nationalExamClass(): ?string
    {
        return 'P6';
    }

    public function recommendedClassSize(): int
    {
        return 50;
    }

    public function modules(): array
    {
        return ['students', 'guardians', 'attendance', 'bulletins', 'ranking', 'tenafep', 'promotion', 'discipline', 'fees', 'events', 'messages', 'transport', 'inventory'];
    }
}
