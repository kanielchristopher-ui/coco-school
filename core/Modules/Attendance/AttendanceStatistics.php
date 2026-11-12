<?php
declare(strict_types=1);

namespace CocoCore\Modules\Attendance;

/** Taux de présence et alertes d'assiduité. Les congés autorisés ne comptent ni comme présence ni comme absence. */
final class AttendanceStatistics
{
    /** @param array{present?:int,late?:int,absent?:int,leave?:int} $counts */
    public function rate(array $counts): float
    {
        $attended = ($counts['present'] ?? 0) + ($counts['late'] ?? 0);
        $total = $attended + ($counts['absent'] ?? 0);
        return $total > 0 ? round($attended / $total * 100, 1) : 100.0;
    }

    /**
     * Élèves à signaler : au moins $threshold absences.
     * @param array<int,int> $absences studentId => nombre d'absences
     * @return array<int,int> studentId => absences, du plus absent au moins absent
     */
    public function alerts(array $absences, int $threshold = 10): array
    {
        $flagged = array_filter($absences, fn($n) => $n >= $threshold);
        arsort($flagged);
        return $flagged;
    }
}
