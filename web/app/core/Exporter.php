<?php
/**
 * ============================================================
 *  Exporter
 *  CSV and Excel (HTML table .xls) export helpers.
 *  PDF export is handled in the browser via the Print dialog.
 * ============================================================
 */

declare(strict_types=1);

class Exporter
{
    /**
     * Send a CSV download.
     */
    public static function csv(string $filename, array $headers, array $rows): void
    {
        $headers  = array_map(fn($h) => t((string) $h), $headers);
        $filename = $filename . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
        fputcsv($out, $headers);

        foreach ($rows as $row) {
            fputcsv($out, array_map('strval', $row));
        }
        fclose($out);
        exit;
    }

    /**
     * Send an Excel-compatible file (HTML table with .xls extension).
     */
    public static function excel(string $filename, array $headers, array $rows): void
    {
        $headers  = array_map(fn($h) => t((string) $h), $headers);
        $filename = $filename . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo '<html><head><meta charset="utf-8"></head><body>';
        echo '<table border="1">';
        echo '<tr>';
        foreach ($headers as $h) {
            echo '<th>' . e($h) . '</th>';
        }
        echo '</tr>';
        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . e((string) $cell) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table></body></html>';
        exit;
    }
}
