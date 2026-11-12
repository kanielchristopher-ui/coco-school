<?php
declare(strict_types=1);

namespace CocoCore\Modules\Grading;

/** Barème des mentions (RDC : E, TB, B, AB, S, Éch ; réussite à 50 %). */
final class GradeScale
{
    /** @param list<array{grade:string,min_percent:float|int|string,max_percent:float|int|string,point?:float|int|string,remarks?:string}> $rows */
    public function __construct(private array $rows, private float $passMark = 50.0)
    {
        usort($this->rows, fn($a, $b) => (float) $b['min_percent'] <=> (float) $a['min_percent']);
    }

    /** @return array{grade:string,point:float,remarks:string} */
    public function mention(float $percent): array
    {
        foreach ($this->rows as $r) {
            if ($percent >= (float) $r['min_percent'] && $percent <= (float) $r['max_percent'] + 0.0099) {
                return ['grade' => (string) $r['grade'], 'point' => (float) ($r['point'] ?? 0), 'remarks' => (string) ($r['remarks'] ?? '')];
            }
        }
        return ['grade' => 'Éch', 'point' => 0.0, 'remarks' => 'Échec'];
    }

    public function passed(float $percent): bool
    {
        return $percent >= $this->passMark;
    }
}
