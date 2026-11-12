<?php
declare(strict_types=1);

namespace CocoCore\Levels;

/**
 * Profil d'un niveau d'enseignement : ce qui le DISTINGUE (âge, évaluation, classement, examen national, modules).
 * Valeurs par défaut issues du système éducatif RDC ; celles marquées « à valider » doivent être confirmées avec l'école/l'inspection.
 */
interface LevelProfile
{
    public function level(): SchoolLevel;

    /** @return list<array{code:string,label:string,rank:int}> classes dans l'ordre */
    public function classLadder(): array;

    /** Âge d'admission en première classe du niveau, en années révolues. @return array{min:int,max:int} */
    public function admissionAge(): array;

    /** 'qualitative' (maternel) ou 'numeric'. */
    public function evaluationMode(): string;

    public function schemeId(): string;

    public function usesRanking(): bool;

    /** Seuil de réussite en % (null si aucune note). */
    public function passMark(): ?float;

    /** 'TENAFEP', 'EXETAT' ou null. */
    public function nationalExam(): ?string;

    /** Classe terminale qui présente l'examen national (code), ou null. */
    public function nationalExamClass(): ?string;

    /** Effectif maximal conseillé par division (à valider avec la réglementation locale). */
    public function recommendedClassSize(): int;

    /** @return list<string> identifiants des modules propres à ce niveau */
    public function modules(): array;
}
