<?php
declare(strict_types=1);

use CocoCore\Modules\Grading\EvaluationScheme;
use CocoCore\Modules\Grading\GradeScale;
use CocoCore\Modules\Grading\ReportCardCalculator;

return function (): void {
    $cfg = json_decode((string) file_get_contents(__DIR__ . '/../../web/app/config/evaluations/rdc-primaire.json'), true);
    $scheme = new EvaluationScheme($cfg);

    T::same('3 trimestres (pas de total annuel dans le schéma primaire)', ['T1', 'T2', 'T3'], array_keys($scheme->periods()));
    T::same('9 étapes saisissables', 9, count($scheme->inputSteps()));
    T::same('étape P1 appartient à T1', 'T1', $scheme->termOfStep('P1'));

    $subjects = [['id' => 1, 'name' => 'Français'], ['id' => 2, 'name' => 'Mathématiques']];
    $marks = [
        1 => ['P1' => [8.0, 10.0], 'P2' => [9.0, 10.0], 'EX1' => [16.0, 20.0]],
        2 => ['P1' => [5.0, 10.0], 'P2' => [6.0, 10.0], 'EX1' => [10.0, 20.0]],
    ];
    $calc = new ReportCardCalculator();
    $t = $calc->table($subjects, $marks, $scheme, 'T1');
    T::same('colonnes T1', ['P1', 'P2', 'EX1', 'TOTAL_T1'], array_column($t['columns'], 'id'));
    T::same('maximum 2 x 40', 80.0, $t['max']);
    T::same('total obtenu', 54.0, $t['points']);
    T::check('complet', $t['complete'] === true);

    $inc = $calc->table($subjects, [1 => $marks[1]], $scheme, 'T1');
    T::check('incomplet si un cours sans notes', $inc['complete'] === false);

    $cfgY = $cfg;
    $cfgY['final_steps'] = [['id' => 'TOTAL_GENERAL', 'label' => 'Total général']];
    T::same('YEAR ajouté si total général configuré', ['T1', 'T2', 'T3', 'YEAR'], array_keys((new EvaluationScheme($cfgY))->periods()));
    $year = $calc->table($subjects, $marks, new EvaluationScheme($cfgY), 'YEAR');
    T::check('période YEAR incomplète (T2, T3 vides)', $year['complete'] === false);

    // poids explicite : un examen compté double
    $cfg2 = $cfg;
    foreach ($cfg2['terms'] as $ti => $term) {
        foreach ($term['steps'] as $si => $st) {
            if ($st['id'] === 'EX1') {
                $cfg2['terms'][$ti]['steps'][$si]['weight'] = 2;
            }
        }
    }
    $w = $calc->table($subjects, $marks, new EvaluationScheme($cfg2), 'T1');
    T::same('poids 2 sur EX1 : total = 8+9+32 + 5+6+20 = 80', 80.0, $w['points']);

    $scale = new GradeScale([
        ['grade' => 'E', 'min_percent' => 90, 'max_percent' => 100, 'point' => 4, 'remarks' => 'Excellent'],
        ['grade' => 'B', 'min_percent' => 70, 'max_percent' => 79.99, 'point' => 3, 'remarks' => 'Bien'],
        ['grade' => 'S', 'min_percent' => 50, 'max_percent' => 59.99, 'point' => 2, 'remarks' => 'Satisfaisant'],
        ['grade' => 'Éch', 'min_percent' => 0, 'max_percent' => 49.99, 'point' => 0, 'remarks' => 'Échec'],
    ]);
    T::same('mention 95 %', 'E', $scale->mention(95)['grade']);
    T::same('mention 75 %', 'B', $scale->mention(75)['grade']);
    T::same('mention 50 %', 'S', $scale->mention(50)['grade']);
    T::same('mention 12 %', 'Éch', $scale->mention(12)['grade']);
    T::check('50 % = réussite', $scale->passed(50));
    T::check('49,9 % = échec', !$scale->passed(49.9));
};
