<?php
/**
 * ============================================================
 *  BulletinEngine
 *  Moteur des bulletins périodiques (idée issue de Coco School).
 *
 *  - Schémas d'évaluation versionnés par niveau et année scolaire
 *    (RDC primaire/maternel : 3 trimestres ; secondaire : 2 semestres).
 *  - Calcul des totaux, pourcentage, mention (barème « grades »), rang.
 *  - Instantané (snapshot) JSON stocké dans periodic_bulletins :
 *    un bulletin publié ne change JAMAIS silencieusement.
 *
 *  Un total n'est jamais calculé en supposant des périodes égales :
 *  chaque étape a un maximum et un poids explicites dans le schéma.
 * ============================================================
 */

declare(strict_types=1);

use CocoCore\Modules\Grading\EvaluationScheme;
use CocoCore\Modules\Grading\ReportCardCalculator;

class BulletinEngine
{
    public const LEVELS = ['primaire', 'secondaire'];

    /** Cycle de la classe (maternel/primaire/secondaire) → niveau du schéma. */
    public static function level(string $cycle): string
    {
        return $cycle === 'secondaire' ? 'secondaire' : 'primaire';
    }

    public static function levelLabel(string $level): string
    {
        return $level === 'secondaire' ? 'Secondary' : 'Nursery & Primary';
    }

    public static function currentSessionId(): int
    {
        $db = Database::getInstance();
        $id = (int) settings('academic_session_id', '0');
        if ($id > 0) {
            return $id;
        }
        return (int) $db->fetchColumn('SELECT id FROM academic_sessions WHERE is_current = 1 ORDER BY id DESC LIMIT 1');
    }

    // ------------------------------------------------------------------
    //  Schémas d'évaluation
    // ------------------------------------------------------------------

    /** Dernière version du schéma (config décodée) ou null. */
    public static function scheme(string $level, int $sessionId): ?array
    {
        $row = Database::getInstance()->fetch(
            'SELECT * FROM school_evaluation_schemes WHERE level_code = ? AND academic_year_id = ? ORDER BY version DESC LIMIT 1',
            [$level, $sessionId]
        );
        if (!$row) {
            return null;
        }
        $row['config'] = json_decode((string) $row['config_json'], true) ?: [];
        return $row;
    }

    public static function defaultConfig(string $level): array
    {
        $file = APP_PATH . '/config/evaluations/' . ($level === 'secondaire' ? 'rdc-secondaire' : 'rdc-primaire') . '.json';
        return json_decode((string) @file_get_contents($file), true) ?: [];
    }

    /** Crée la version 1 (ou une nouvelle version) à partir du modèle RDC. */
    public static function installDefault(string $level, int $sessionId, int $userId): int
    {
        return self::saveScheme($level, $sessionId, self::defaultConfig($level), $userId);
    }

    /** Toute modification crée une NOUVELLE version ; les anciens bulletins restent liés à l'ancienne. */
    public static function saveScheme(string $level, int $sessionId, array $config, int $userId): int
    {
        $db = Database::getInstance();
        $version = 1 + (int) $db->fetchColumn(
            'SELECT COALESCE(MAX(version), 0) FROM school_evaluation_schemes WHERE level_code = ? AND academic_year_id = ?',
            [$level, $sessionId]
        );
        return $db->insert('school_evaluation_schemes', [
            'school_id'        => 1,
            'level_code'       => $level,
            'academic_year_id' => $sessionId,
            'scheme_id'        => (string) ($config['scheme_id'] ?? ('rdc-' . $level)),
            'version'          => $version,
            'config_json'      => json_encode($config, JSON_UNESCAPED_UNICODE),
            'created_by'       => $userId,
        ]);
    }

    /**
     * Valide et normalise une configuration envoyée depuis le formulaire.
     * Seuls libellés, maxima et poids sont modifiables (structure fixe).
     */
    public static function applyFormToConfig(array $config, array $post): array
    {
        foreach ($config['terms'] as $ti => $term) {
            foreach ($term['steps'] as $si => $step) {
                $id = $step['id'];
                $label = trim((string) ($post['label'][$id] ?? ''));
                if ($label !== '') {
                    $config['terms'][$ti]['steps'][$si]['label'] = mb_substr($label, 0, 60);
                }
                if (in_array($step['type'], ['period', 'exam'], true)) {
                    $max = (float) str_replace(',', '.', (string) ($post['max'][$id] ?? $step['max_points'] ?? 10));
                    $weight = (float) str_replace(',', '.', (string) ($post['weight'][$id] ?? $step['weight'] ?? 1));
                    $config['terms'][$ti]['steps'][$si]['max_points'] = max(0.5, min(1000, $max));
                    $config['terms'][$ti]['steps'][$si]['weight'] = max(0.1, min(10, $weight));
                }
            }
        }
        return $config;
    }

    /** Étapes saisissables : calcul délégué au Core (CocoCore\Modules\Grading). */
    public static function inputSteps(array $config): array
    {
        return (new EvaluationScheme($config))->inputSteps();
    }

    /** Périodes de bulletin (T1..T3 ou S1, S2, + YEAR) : Core. */
    public static function periods(array $config): array
    {
        return (new EvaluationScheme($config))->periods();
    }

    public static function termOfStep(array $config, string $stepId): ?string
    {
        return (new EvaluationScheme($config))->termOfStep($stepId);
    }

    /** points[student][subject][step] = [pts, max] pour toute la classe. */
    public static function classMarks(int $classId, int $sessionId): array
    {
        $out = [];
        foreach (Database::getInstance()->fetchAll(
            'SELECT student_id, subject_id, step_id, points_obtained, max_points FROM student_marks WHERE class_id = ? AND academic_year_id = ?',
            [$classId, $sessionId]
        ) as $r) {
            $out[(int) $r['student_id']][(int) $r['subject_id']][$r['step_id']] = [(float) $r['points_obtained'], (float) $r['max_points']];
        }
        return $out;
    }

    /** Tableau de notes d'un élève pour une période : calcul pur dans le Core. */
    public static function table(array $subjects, array $studentMarks, array $config, string $periodId): array
    {
        return (new ReportCardCalculator())->table($subjects, $studentMarks, new EvaluationScheme($config), $periodId);
    }

    /** Construit l'instantané complet d'un bulletin (élève, classe, période). */
    public static function snapshot(int $studentId, int $sessionId, string $periodId): array
    {
        $db = Database::getInstance();
        $student = $db->fetch(
            'SELECT s.*, c.name AS class_name, c.cycle FROM students s INNER JOIN classes c ON c.id = s.class_id WHERE s.id = ?',
            [$studentId]
        );
        if (!$student) {
            throw new RuntimeException('Student not found.');
        }
        $level = self::level((string) $student['cycle']);
        $scheme = self::scheme($level, $sessionId);
        if (!$scheme) {
            throw new RuntimeException('No evaluation scheme for this level and year.');
        }
        $config = $scheme['config'];
        if (!isset(self::periods($config)[$periodId])) {
            throw new RuntimeException('Unknown period.');
        }
        $classId = (int) $student['class_id'];
        $subjects = $db->fetchAll('SELECT id, name FROM subjects WHERE class_id = ? ORDER BY id', [$classId]);
        $marks = self::classMarks($classId, $sessionId);

        $mine = self::table($subjects, $marks[$studentId] ?? [], $config, $periodId);

        // Rang : tous les élèves actifs de la classe ayant au moins une note
        $totals = [];
        foreach ($db->fetchAll('SELECT id FROM students WHERE class_id = ? AND status = \'active\'', [$classId]) as $st) {
            $sid = (int) $st['id'];
            if (!empty($marks[$sid])) {
                $totals[$sid] = self::table($subjects, $marks[$sid], $config, $periodId)['points'];
            }
        }
        $rank = 1;
        foreach ($totals as $pts) {
            if ($pts > $mine['points']) {
                $rank++;
            }
        }
        $percent = $mine['max'] > 0 ? round($mine['points'] / $mine['max'] * 100, 1) : 0.0;
        $session = $db->fetch('SELECT id, name FROM academic_sessions WHERE id = ?', [$sessionId]);

        return [
            'v'       => 1,
            'student' => [
                'id' => $studentId, 'name' => studentName($student), 'perm' => $student['perm_number'] ?? '',
                'official_id' => $student['official_id'] ?? '', 'class' => $student['class_name'], 'class_id' => $classId,
                'gender' => $student['gender'] ?? '', 'dob' => $student['dob'] ?? '', 'birth_place' => $student['birth_place'] ?? '',
            ],
            'session' => ['id' => $sessionId, 'name' => $session['name'] ?? ''],
            'period'  => ['id' => $periodId, 'label' => self::periods($config)[$periodId]],
            'scheme_version' => (int) $scheme['version'],
            'columns' => $mine['columns'], 'rows' => $mine['rows'], 'sums' => $mine['sums'], 'percents' => $mine['percents'],
            'totals'  => [
                'points' => $mine['points'], 'max' => $mine['max'], 'percent' => $percent, 'rank' => $rank,
                'class_size' => max(1, count($totals)), 'complete' => $mine['complete'],
            ],
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    // ------------------------------------------------------------------
    //  Présentation (données pour le modèle) — formatage selon la langue
    // ------------------------------------------------------------------

    public static function present(array $snap, array $bulletin): array
    {
        $fmt = function ($v): string {
            if ($v === null || $v === '') {
                return '—';
            }
            $s = rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
            return Lang::meta('decimal', '.') === ',' ? str_replace('.', ',', $s) : $s;
        };
        $cells = fn(array $list) => array_map(fn($c) => ['value' => $fmt($c['v']), 'kind' => $c['kind']], $list);

        $percent = (float) $snap['totals']['percent'];
        $mention = $snap['totals']['points'] > 0 ? (gradeFor($percent)['remarks'] ?? '') : '';
        $level = self::level((string) (Database::getInstance()->fetchColumn('SELECT cycle FROM classes WHERE id = ?', [$snap['student']['class_id']]) ?: 'primaire'));

        return [
            'school'  => [
                'name' => settings('school_name', APP_NAME), 'address' => settings('school_address', ''),
                'phone' => settings('school_phone', ''), 'motto' => settings('school_motto', ''),
            ],
            'student' => [
                'name' => $snap['student']['name'], 'perm' => $snap['student']['perm'] ?: '—', 'class' => $snap['student']['class'],
                'gender' => $snap['student']['gender'] === 'female' ? t('Female') : ($snap['student']['gender'] === 'male' ? t('Male') : ''),
                'dob' => $snap['student']['dob'] ? formatDate($snap['student']['dob']) : '—', 'birth_place' => $snap['student']['birth_place'] ?: '—',
            ],
            'session' => $snap['session'],
            'period'  => $snap['period'],
            'columns' => array_map(fn($c) => ['label' => t($c['label']), 'max' => $fmt($c['max']), 'kind' => $c['kind']], $snap['columns']),
            'rows'    => array_map(fn($r) => ['subject' => $r['subject'], 'cells' => $cells($r['cells'])], $snap['rows']),
            'sums'    => $cells($snap['sums']),
            'percents' => array_map(fn($c) => ['value' => $c['v'] === null ? '—' : $fmt($c['v']) . ' %', 'kind' => $c['kind']], $snap['percents']),
            'totals'  => [
                'percent' => $fmt($percent), 'mention' => $mention !== '' ? t($mention) : '—',
                'rank' => $snap['totals']['rank'], 'class_size' => $snap['totals']['class_size'],
                'decision' => $snap['totals']['points'] > 0 ? ($percent >= 50 ? t('Passed') : t('Failed')) : '—',
            ],
            'signature' => ['title' => $level === 'secondaire' ? t('The Prefect') : t('The Director'), 'name' => settings('principal_name', '')],
            'meta' => [
                'number' => (string) ($bulletin['id'] ?? ''), 'version' => (string) ($bulletin['version'] ?? 1),
                'date' => formatDate(substr((string) $snap['generated_at'], 0, 10)),
            ],
        ];
    }
}
