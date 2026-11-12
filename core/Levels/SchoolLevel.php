<?php
declare(strict_types=1);

namespace CocoCore\Levels;

/**
 * Les trois niveaux d'enseignement de la RDC. Chaque niveau est un ÉTABLISSEMENT distinct
 * (gestion, personnel, évaluation, examens et frais propres) : voir LevelProfile et documentation/NIVEAUX.md.
 */
enum SchoolLevel: string
{
    case MATERNEL = 'maternel';
    case PRIMAIRE = 'primaire';
    case SECONDAIRE = 'secondaire';

    /** Accepte aussi « maternelle ». */
    public static function parse(string $value): self
    {
        return match (mb_strtolower(trim($value))) {
            'maternel', 'maternelle' => self::MATERNEL,
            'primaire' => self::PRIMAIRE,
            'secondaire' => self::SECONDAIRE,
            default => throw new \InvalidArgumentException('Niveau inconnu : ' . $value),
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::MATERNEL => 'École maternelle',
            self::PRIMAIRE => 'École primaire',
            self::SECONDAIRE => 'École secondaire',
        };
    }

    /** Titre du chef d'établissement. */
    public function headTitle(): string
    {
        return $this === self::SECONDAIRE ? 'Préfet' : 'Directeur';
    }

    /** Rôle applicatif du chef d'établissement. */
    public function headRole(): string
    {
        return $this === self::SECONDAIRE ? 'prefet' : 'director';
    }
}
