<?php
/**
 * Attendance model.
 */
class Attendance extends Model
{
    protected string $table = 'attendance';

    /**
     * Attendance records for a class/section on a given date.
     * When $subjectId is provided, only that subject's records are returned.
     */
    public function forDate(int $classId, ?int $sectionId, string $date, ?int $subjectId = null): array
    {
        $sql = 'SELECT a.*, s.first_name, s.last_name, su.name AS subject_name
             FROM attendance a
             INNER JOIN students s ON s.id = a.student_id
             LEFT JOIN subjects su ON su.id = a.subject_id
             WHERE a.class_id = ? AND a.date = ?';
        $params = [$classId, $date];
        if ($subjectId !== null && $subjectId > 0) {
            $sql .= ' AND a.subject_id = ?';
            $params[] = $subjectId;
        }
        $sql .= ' ORDER BY s.id ASC';

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Bulk upsert attendance for a set of students.
     * Records are keyed by (student_id, date, subject_id): re-saving the same
     * subject for a student on a date replaces that subject's record, while a
     * different subject on the same date is kept as a separate record.
     */
    public function bulkUpsert(array $records): void
    {
        $this->db->beginTransaction();
        try {
            foreach ($records as $r) {
                $subject = $r['subject_id'] ?? null;
                if ($subject === null) {
                    $this->db->delete(
                        'attendance',
                        'student_id = ? AND date = ? AND subject_id IS NULL',
                        [$r['student_id'], $r['date']]
                    );
                } else {
                    $this->db->delete(
                        'attendance',
                        'student_id = ? AND date = ? AND subject_id = ?',
                        [$r['student_id'], $r['date'], $subject]
                    );
                }
                $this->db->insert('attendance', [
                    'student_id' => $r['student_id'],
                    'class_id'   => $r['class_id'],
                    'section_id' => $r['section_id'],
                    'subject_id' => $subject,
                    'date'       => $r['date'],
                    'status'     => $r['status'],
                    'remark'     => $r['remark'] ?? null,
                    'taken_by'   => $r['taken_by'] ?? null,
                ]);
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Monthly summary per student.
     */
    public function monthlySummary(int $studentId, int $month, int $year): array
    {
        return $this->db->fetchAll(
            'SELECT status, COUNT(*) AS total FROM attendance
             WHERE student_id = ? AND MONTH(date) = ? AND YEAR(date) = ?
             GROUP BY status',
            [$studentId, $month, $year]
        );
    }
}
