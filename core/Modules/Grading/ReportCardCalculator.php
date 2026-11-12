<?php
declare(strict_types=1);

namespace CocoCore\Modules\Grading;

/** Calcul pur (sans base de données) du tableau de notes d'un élève pour une période. */
final class ReportCardCalculator
{
    /**
     * @param list<array{id:int|string,name:string}> $subjects
     * @param array<int,array<string,array{0:float,1:float}>> $studentMarks subject_id => step_id => [points, max]
     * @return array{columns:array,rows:array,sums:array,percents:array,points:float,max:float,complete:bool}
     */
    public function table(array $subjects, array $studentMarks, EvaluationScheme $scheme, string $periodId): array
    {
        $steps = $scheme->inputSteps();
        $columns = [];
        $groups = [];                       // colId => [step ids] pour les colonnes « total »

        if ($periodId === 'YEAR') {
            foreach ($scheme->terms() as $term) {
                $ids = array_keys(array_filter($steps, fn($s) => $s['term'] === $term['id']));
                $total = end($term['steps']);
                $columns[] = ['id' => $total['id'], 'label' => $total['label'], 'kind' => 'total', 'max' => $this->sumMax($steps, $ids)];
                $groups[$total['id']] = $ids;
            }
            $all = array_keys($steps);
            $fin = $scheme->finalStep();
            $columns[] = ['id' => $fin['id'], 'label' => $fin['label'], 'kind' => 'total', 'max' => $this->sumMax($steps, $all)];
            $groups[$fin['id']] = $all;
        } else {
            foreach ($scheme->terms() as $term) {
                if ($term['id'] !== $periodId) {
                    continue;
                }
                foreach ($term['steps'] as $st) {
                    if ($st['type'] === 'total') {
                        $ids = array_keys(array_filter($steps, fn($s) => $s['term'] === $term['id']));
                        $columns[] = ['id' => $st['id'], 'label' => $st['label'], 'kind' => 'total', 'max' => $this->sumMax($steps, $ids)];
                        $groups[$st['id']] = $ids;
                    } else {
                        $columns[] = ['id' => $st['id'], 'label' => $st['label'], 'kind' => $st['type'], 'max' => (float) ($st['max_points'] ?? 10)];
                    }
                }
            }
        }

        $rows = [];
        $sums = array_fill(0, count($columns), 0.0);
        $counts = array_fill(0, count($columns), 0);
        $complete = true;

        foreach ($subjects as $sub) {
            $cells = [];
            $marks = $studentMarks[(int) $sub['id']] ?? [];
            foreach ($columns as $i => $col) {
                if ($col['kind'] === 'total') {
                    $pts = 0.0;
                    $any = false;
                    foreach ($groups[$col['id']] as $sid) {
                        if (isset($marks[$sid])) {
                            $pts += $marks[$sid][0] * $steps[$sid]['weight'];
                            $any = true;
                        } else {
                            $complete = false;
                        }
                    }
                    $cells[] = ['v' => $any ? round($pts, 2) : null, 'kind' => 'total'];
                    if ($any) {
                        $sums[$i] += $pts;
                        $counts[$i]++;
                    }
                } else {
                    $v = $marks[$col['id']][0] ?? null;
                    $cells[] = ['v' => $v, 'kind' => $col['kind']];
                    if ($v !== null) {
                        $sums[$i] += $v;
                        $counts[$i]++;
                    }
                }
            }
            $rows[] = ['subject_id' => (int) $sub['id'], 'subject' => $sub['name'], 'cells' => $cells];
        }

        $last = count($columns) - 1;
        $nSub = max(1, count($subjects));
        $sumCells = [];
        $pctCells = [];
        foreach ($columns as $i => $col) {
            $sumCells[] = ['v' => $counts[$i] > 0 ? round($sums[$i], 2) : null, 'kind' => $col['kind']];
            $den = $col['max'] * $nSub;
            $pctCells[] = ['v' => ($counts[$i] > 0 && $den > 0) ? round($sums[$i] / $den * 100, 1) : null, 'kind' => $col['kind']];
        }
        $grandPoints = $last >= 0 ? $sums[$last] : 0.0;
        $grandMax = $last >= 0 ? $columns[$last]['max'] * $nSub : 0.0;

        return [
            'columns' => $columns, 'rows' => $rows, 'sums' => $sumCells, 'percents' => $pctCells,
            'points' => round($grandPoints, 2), 'max' => round($grandMax, 2),
            'complete' => $complete && count($subjects) > 0 && $last >= 0 && $counts[$last] === count($subjects),
        ];
    }

    private function sumMax(array $steps, array $ids): float
    {
        $t = 0.0;
        foreach ($ids as $id) {
            $t += $steps[$id]['max'] * $steps[$id]['weight'];
        }
        return round($t, 2);
    }
}
