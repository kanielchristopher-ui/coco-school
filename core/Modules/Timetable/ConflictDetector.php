<?php
declare(strict_types=1);

namespace CocoCore\Modules\Timetable;

/** Détecte les conflits d'horaire : même enseignant, même salle ou même classe/division en même temps. */
final class ConflictDetector
{
    /**
     * @param list<array{id:int,day:int,start:string,end:string,teacher_id?:?int,room?:?string,class_id?:?int,section_id?:?int}> $slots
     * @return list<array{kind:string,a:int,b:int}>
     */
    public function find(array $slots): array
    {
        $conflicts = [];
        $n = count($slots);
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $a = $slots[$i];
                $b = $slots[$j];
                if ($a['day'] !== $b['day'] || !$this->overlap($a['start'], $a['end'], $b['start'], $b['end'])) {
                    continue;
                }
                if (!empty($a['teacher_id']) && ($a['teacher_id'] ?? null) === ($b['teacher_id'] ?? null)) {
                    $conflicts[] = ['kind' => 'teacher', 'a' => $a['id'], 'b' => $b['id']];
                }
                if (!empty($a['room']) && ($a['room'] ?? null) === ($b['room'] ?? null)) {
                    $conflicts[] = ['kind' => 'room', 'a' => $a['id'], 'b' => $b['id']];
                }
                if (!empty($a['class_id']) && ($a['class_id'] ?? null) === ($b['class_id'] ?? null) && ($a['section_id'] ?? null) === ($b['section_id'] ?? null)) {
                    $conflicts[] = ['kind' => 'class', 'a' => $a['id'], 'b' => $b['id']];
                }
            }
        }
        return $conflicts;
    }

    /** Deux plages « HH:MM » se chevauchent si l'une commence avant la fin de l'autre (fin exclue). */
    private function overlap(string $s1, string $e1, string $s2, string $e2): bool
    {
        return $s1 < $e2 && $s2 < $e1;
    }
}
