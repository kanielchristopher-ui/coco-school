<?php
declare(strict_types=1);

namespace CocoCore\Levels;

/** Échelle des classes et passages entre niveaux : M1→M2→M3→P1…P6→EB7→EB8→H1…H4→(Exetat/sortie). */
final class ClassLadder
{
    /** @return list<string> */
    public static function codes(): array
    {
        $c = [];
        foreach (LevelRegistry::all() as $p) {
            foreach ($p->classLadder() as $row) {
                $c[] = $row['code'];
            }
        }
        return $c;
    }

    public static function levelOf(string $code): SchoolLevel
    {
        return match (true) {
            str_starts_with($code, 'M') => SchoolLevel::MATERNEL,
            str_starts_with($code, 'P') => SchoolLevel::PRIMAIRE,
            str_starts_with($code, 'EB'), str_starts_with($code, 'H') => SchoolLevel::SECONDAIRE,
            default => throw new \InvalidArgumentException('Code de classe inconnu : ' . $code),
        };
    }

    /** Classe suivante, ou null après la dernière classe (fin des humanités). */
    public static function next(string $code): ?string
    {
        $all = self::codes();
        $i = array_search($code, $all, true);
        if ($i === false) {
            throw new \InvalidArgumentException('Code de classe inconnu : ' . $code);
        }
        return $all[$i + 1] ?? null;
    }

    /** Vrai si le passage change de niveau (donc d'établissement) : M3→P1, P6→EB7. */
    public static function changesLevel(string $code): bool
    {
        $n = self::next($code);
        return $n !== null && self::levelOf($n) !== self::levelOf($code);
    }

    /** Déduit le code d'une classe depuis son nom (« 3ème Maternelle », « 1ère Primaire », « 7ème Éducation de base (CTEB) », « 2ème Humanités … »). */
    public static function codeFromName(string $name): ?string
    {
        $n = mb_strtolower($name);
        if (!preg_match('/(\d+)\s*(?:ère|er|ème|eme|e)\b/u', $n, $m)) {
            return null;
        }
        $k = (int) $m[1];
        return match (true) {
            str_contains($n, 'matern') && $k >= 1 && $k <= 3 => 'M' . $k,
            str_contains($n, 'primaire') && $k >= 1 && $k <= 6 => 'P' . $k,
            (str_contains($n, 'éducation de base') || str_contains($n, 'education de base') || str_contains($n, 'cteb')) && ($k === 7 || $k === 8) => 'EB' . $k,
            str_contains($n, 'humanit') && $k >= 1 && $k <= 4 => 'H' . $k,
            default => null,
        };
    }
}
