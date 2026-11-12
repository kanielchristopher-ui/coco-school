<?php
declare(strict_types=1);

namespace CocoCore\Modules\Statistics;

use CocoCore\Modules\Grading\GradeScale;

/** Statistiques de résultats d'une classe / d'un niveau. */
final class ResultStatistics
{
    public function __construct(private GradeScale $scale)
    {
    }

    /**
     * @param list<array{percent:float,gender?:?string}> $students
     * @return array{count:int,mean:float,median:float,min:float,max:float,std_dev:float,pass_rate:float,by_mention:array<string,int>,by_gender:array<string,array{count:int,pass_rate:float}>}
     */
    public function summarize(array $students): array
    {
        $n = count($students);
        if ($n === 0) {
            return ['count' => 0, 'mean' => 0.0, 'median' => 0.0, 'min' => 0.0, 'max' => 0.0, 'std_dev' => 0.0, 'pass_rate' => 0.0, 'by_mention' => [], 'by_gender' => []];
        }
        $vals = array_map(fn($s) => (float) $s['percent'], $students);
        sort($vals);
        $mean = array_sum($vals) / $n;
        $mid = intdiv($n, 2);
        $median = $n % 2 === 1 ? $vals[$mid] : ($vals[$mid - 1] + $vals[$mid]) / 2;
        $var = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $vals)) / $n;

        $mentions = [];
        $gender = [];
        $passed = 0;
        foreach ($students as $s) {
            $p = (float) $s['percent'];
            $m = $this->scale->mention($p)['grade'];
            $mentions[$m] = ($mentions[$m] ?? 0) + 1;
            $ok = $this->scale->passed($p);
            $passed += $ok ? 1 : 0;
            $g = (string) ($s['gender'] ?? 'unknown');
            $gender[$g]['count'] = ($gender[$g]['count'] ?? 0) + 1;
            $gender[$g]['passed'] = ($gender[$g]['passed'] ?? 0) + ($ok ? 1 : 0);
        }
        $byGender = [];
        foreach ($gender as $g => $d) {
            $byGender[$g] = ['count' => $d['count'], 'pass_rate' => round($d['passed'] / $d['count'] * 100, 1)];
        }
        return [
            'count' => $n, 'mean' => round($mean, 2), 'median' => round($median, 2), 'min' => $vals[0], 'max' => $vals[$n - 1],
            'std_dev' => round(sqrt($var), 2), 'pass_rate' => round($passed / $n * 100, 1), 'by_mention' => $mentions, 'by_gender' => $byGender,
        ];
    }
}
