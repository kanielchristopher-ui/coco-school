<?php
declare(strict_types=1);

namespace CocoCore\Levels\Secondaire;

use CocoCore\Levels\LevelProfile;
use CocoCore\Levels\SchoolLevel;

/** École secondaire (cycle long) : 2 ans d'éducation de base (7ème-8ème) + 4 ans d'humanités par option, 2 semestres, Exetat en 4ème humanités. */
final class SecondaireProfile implements LevelProfile
{
    public function level(): SchoolLevel
    {
        return SchoolLevel::SECONDAIRE;
    }

    public function classLadder(): array
    {
        return [
            ['code' => 'EB7', 'label' => '7ème Éducation de base', 'rank' => 1],
            ['code' => 'EB8', 'label' => '8ème Éducation de base', 'rank' => 2],
            ['code' => 'H1', 'label' => '1ère Humanités', 'rank' => 3],
            ['code' => 'H2', 'label' => '2ème Humanités', 'rank' => 4],
            ['code' => 'H3', 'label' => '3ème Humanités', 'rank' => 5],
            ['code' => 'H4', 'label' => '4ème Humanités', 'rank' => 6],
        ];
    }

    public function admissionAge(): array
    {
        return ['min' => 11, 'max' => 20];
    }

    public function evaluationMode(): string
    {
        return 'numeric';
    }

    public function schemeId(): string
    {
        return 'rdc-secondaire';
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
        return 'EXETAT';
    }

    public function nationalExamClass(): ?string
    {
        return 'H4';
    }

    public function recommendedClassSize(): int
    {
        return 45;
    }

    public function modules(): array
    {
        return ['students', 'guardians', 'attendance', 'bulletins', 'ranking', 'options', 'conduct', 'retake', 'exetat', 'timetable', 'promotion', 'discipline', 'fees', 'payroll', 'events', 'messages', 'transport', 'inventory'];
    }
}
