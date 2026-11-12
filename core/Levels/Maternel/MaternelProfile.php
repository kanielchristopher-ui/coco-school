<?php
declare(strict_types=1);

namespace CocoCore\Levels\Maternel;

use CocoCore\Levels\LevelProfile;
use CocoCore\Levels\SchoolLevel;

/** École maternelle : cycle de 3 ans (3 à 5 ans), non obligatoire, évaluation QUALITATIVE, aucun classement ni examen. */
final class MaternelProfile implements LevelProfile
{
    public function level(): SchoolLevel
    {
        return SchoolLevel::MATERNEL;
    }

    public function classLadder(): array
    {
        return [
            ['code' => 'M1', 'label' => '1ère Maternelle (petite section)', 'rank' => 1],
            ['code' => 'M2', 'label' => '2ème Maternelle (moyenne section)', 'rank' => 2],
            ['code' => 'M3', 'label' => '3ème Maternelle (grande section)', 'rank' => 3],
        ];
    }

    public function admissionAge(): array
    {
        return ['min' => 3, 'max' => 5];
    }

    public function evaluationMode(): string
    {
        return 'qualitative';
    }

    public function schemeId(): string
    {
        return 'qualitative-maternel';
    }

    public function usesRanking(): bool
    {
        return false;
    }

    public function passMark(): ?float
    {
        return null;
    }

    public function nationalExam(): ?string
    {
        return null;
    }

    public function nationalExamClass(): ?string
    {
        return null;
    }

    public function recommendedClassSize(): int
    {
        return 30;
    }

    public function modules(): array
    {
        return ['students', 'guardians', 'attendance', 'development', 'health', 'pickup', 'daily_reports', 'fees', 'events', 'messages', 'transport'];
    }
}
