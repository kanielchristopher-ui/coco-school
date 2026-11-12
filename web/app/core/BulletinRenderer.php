<?php
/**
 * Construit le HTML (déjà échappé) du bulletin à partir de l'instantané data_json.
 * Utilisé par le modèle par défaut ET fourni comme bloc {{{marks_table}}} aux modèles .ccplg.
 */
class BulletinRenderer
{
    private static function n(?float $v): string
    {
        if ($v === null) {
            return '';
        }
        return rtrim(rtrim(number_format($v, 2, (string) Lang::meta('decimal', '.'), ''), '0'), (string) Lang::meta('decimal', '.'));
    }

    /** Tableau des notes : branches × étapes (+ total, + total général). */
    public static function table(array $d): string
    {
        $cols = $d['columns'];
        $year = !empty($d['show_year']);
        $h = '<table class="bl-table"><thead><tr><th class="bl-left" rowspan="2">' . e(t('Subjects')) . '</th>';
        foreach ($cols as $c) {
            $h .= '<th>' . e($c['label']) . '</th>';
        }
        $h .= '<th>' . e($d['period']['total_label']) . '</th>';
        if ($year) {
            $h .= '<th>' . e(t('Overall total')) . '</th>';
        }
        $h .= '</tr><tr class="bl-max">';
        $periodMax = 0.0;
        foreach ($cols as $c) {
            $h .= '<th>' . e(t('Max') . ' ' . self::n((float) $c['max'])) . '</th>';
            $periodMax += (float) $c['max'];
        }
        $h .= '<th>' . e(self::n($periodMax)) . '</th>';
        if ($year) {
            $h .= '<th></th>';
        }
        $h .= '</tr></thead><tbody>';
        foreach ($d['rows'] as $r) {
            $h .= '<tr><td class="bl-left">' . e($r['subject']) . '</td>';
            foreach ($cols as $c) {
                $cell = $r['cells'][$c['id']] ?? null;
                $h .= '<td>' . ($cell ? e(self::n((float) $cell['p'])) : '') . '</td>';
            }
            $h .= '<td class="bl-total">' . ($r['total'] ? e(self::n((float) $r['total']['p'])) : '') . '</td>';
            if ($year) {
                $h .= '<td class="bl-total">' . ($r['year'] ? e(self::n((float) $r['year']['p'])) : '') . '</td>';
            }
            $h .= '</tr>';
        }
        $t = $d['totals'];
        $span = count($cols);
        $h .= '</tbody><tfoot>';
        $h .= '<tr><th class="bl-left">' . e(t('TOTAL')) . '</th><td colspan="' . $span . '"></td><th>' . e(self::n((float) $t['points'])) . ' / ' . e(self::n((float) $t['max'])) . '</th>'
            . ($year ? '<th>' . e(self::n((float) $t['year_points'])) . ' / ' . e(self::n((float) $t['year_max'])) . '</th>' : '') . '</tr>';
        $h .= '<tr><th class="bl-left">' . e(t('Percentage')) . '</th><td colspan="' . $span . '"></td><th>' . ($t['percent'] !== null ? e(self::n((float) $t['percent'])) . ' %' : '') . '</th>'
            . ($year ? '<th>' . ($t['year_percent'] !== null ? e(self::n((float) $t['year_percent'])) . ' %' : '') . '</th>' : '') . '</tr>';
        $h .= '<tr><th class="bl-left">' . e(t('Rank')) . '</th><td colspan="' . ($span + 1 + ($year ? 1 : 0)) . '" class="bl-left">'
            . ($t['rank'] ? e($t['rank'] . ' / ' . $t['class_size']) : '—') . '</td></tr>';
        $h .= '<tr><th class="bl-left">' . e(t('Grade')) . '</th><td colspan="' . ($span + 1 + ($year ? 1 : 0)) . '" class="bl-left">'
            . e(trim(($t['grade'] ?? '') . ' ' . ($t['mention'] ?? ''))) . '</td></tr>';
        return $h . '</tfoot></table>';
    }

    public static function signatures(array $d): string
    {
        $role = ($d['class']['cycle'] ?? 'primaire') === 'secondaire' ? t('The Prefect') : t('The School Director');
        return '<div class="bl-sign"><div><span>' . e(t('Class teacher')) . '</span></div><div><span>' . e(t('Parent signature')) . '</span></div><div><span>' . e($role) . '</span></div></div>';
    }

    /** Feuille complète par défaut (en-tête officiel RDC + identité + tableau + signatures). */
    public static function defaultSheet(array $d, array $school): string
    {
        $s = $d['student'];
        $h  = '<div class="bl-head">';
        $h .= '<div class="bl-gov">' . e(t('DEMOCRATIC REPUBLIC OF THE CONGO')) . '<br><small>' . e(t('MINISTRY OF PRIMARY, SECONDARY AND TECHNICAL EDUCATION')) . '</small></div>';
        $h .= '<div class="bl-school"><strong>' . e($school['name']) . '</strong><br><small>' . e($school['address']) . '</small></div>';
        $h .= '<h2 class="bl-title">' . e(t('REPORT CARD')) . ' — ' . e($d['period']['label']) . '</h2>';
        $h .= '<div class="bl-year">' . e(t('School year')) . ' ' . e($school['year']) . '</div></div>';
        $h .= '<table class="bl-id"><tr>'
            . '<td><b>' . e(t('Student')) . ' :</b> ' . e($s['name']) . '</td>'
            . '<td><b>' . e(t('Class')) . ' :</b> ' . e($d['class']['name']) . '</td></tr><tr>'
            . '<td><b>' . e(t('Gender')) . ' :</b> ' . e($s['gender'] === 'female' ? t('Female') : ($s['gender'] === 'male' ? t('Male') : '')) . '</td>'
            . '<td><b>' . e(t('PERM Number')) . ' :</b> ' . e($s['perm_number']) . '</td></tr><tr>'
            . '<td><b>' . e(t('Born')) . ' :</b> ' . e(trim(($s['birth_place'] ? $s['birth_place'] . ', ' : '') . ($s['dob'] ? formatDate($s['dob']) : ''))) . '</td>'
            . '<td><b>' . e(t('Official ID')) . ' :</b> ' . e($s['official_id']) . '</td></tr></table>';
        $h .= self::table($d);
        $h .= self::signatures($d);
        return $h;
    }

    /** Variables scalaires disponibles pour les modèles .ccplg. */
    public static function templateData(array $d, array $school): array
    {
        return [
            'school'  => ['name' => $school['name'], 'address' => $school['address'], 'year' => $school['year'], 'phone' => $school['phone']],
            'student' => $d['student'],
            'class'   => $d['class'],
            'period'  => $d['period'],
            'totals'  => $d['totals'],
        ];
    }
}
