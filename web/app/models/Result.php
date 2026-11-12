<?php
/**
 * Result model.
 */
class Result extends Model
{
    protected string $table = 'results';

    /**
     * All results for an exam with student, subject names.
     */
    public function forExam(int $examId): array
    {
        return $this->db->fetchAll(
            'SELECT r.*, s.first_name, s.last_name, s.roll_no, sub.name AS subject_name
             FROM results r
             INNER JOIN students s ON s.id = r.student_id
             INNER JOIN subjects sub ON sub.id = r.subject_id
             WHERE r.exam_id = ?
             ORDER BY s.roll_no ASC, sub.name ASC',
            [$examId]
        );
    }

    /**
     * Aggregate per-student totals for an exam, ranked.
     */
    public function ranks(int $examId): array
    {
        return $this->db->fetchAll(
            'SELECT student_id,
                    s.first_name, s.last_name, s.roll_no,
                    ROUND(SUM(marks_obtained), 2) AS total,
                    ROUND(SUM(max_marks), 2) AS max_total,
                    ROUND((SUM(marks_obtained) / SUM(max_marks)) * 100, 2) AS percentage,
                    COUNT(*) AS subjects_count
             FROM results r
             INNER JOIN students s ON s.id = r.student_id
             WHERE r.exam_id = ?
             GROUP BY student_id
             ORDER BY percentage DESC',
            [$examId]
        );
    }

    /**
     * Results for a single student in an exam (with subjects).
     */
    public function forStudentExam(int $studentId, int $examId): array
    {
        return $this->db->fetchAll(
            'SELECT r.*, sub.name AS subject_name, sub.code AS subject_code
             FROM results r
             INNER JOIN subjects sub ON sub.id = r.subject_id
             WHERE r.student_id = ? AND r.exam_id = ?
             ORDER BY sub.name ASC',
            [$studentId, $examId]
        );
    }

    /**
     * Results for all students of one subject in one exam.
     */
    public function forStudentExamWithSubject(int $examId, int $subjectId): array
    {
        return $this->db->fetchAll(
            'SELECT r.*, s.first_name, s.last_name, s.roll_no
             FROM results r
             INNER JOIN students s ON s.id = r.student_id
             WHERE r.exam_id = ? AND r.subject_id = ?
             ORDER BY s.roll_no ASC',
            [$examId, $subjectId]
        );
    }

    /**
     * Subjects a teacher is assigned to (via subjects.teacher_id).
     */
    public function teacherSubjects(int $teacherId): array
    {
        return $this->db->fetchAll(
            'SELECT su.*, c.name AS class_name
             FROM subjects su
             LEFT JOIN classes c ON c.id = su.class_id
             WHERE su.teacher_id = ? AND su.class_id IS NOT NULL
             ORDER BY su.name ASC',
            [$teacherId]
        );
    }

    /**
     * Exams a teacher may enter marks for: only exams whose subject the
     * teacher teaches in the exam's own class.
     */
    public function teacherExams(int $teacherId, ?int $classId = null, ?int $subjectId = null): array
    {
        $sql = 'SELECT DISTINCT e.*, et.name AS type_name, c.name AS class_name,
                       sec.name AS section_name
                FROM exams e
                INNER JOIN subjects su ON su.id = e.subject_id
                                       AND su.class_id = e.class_id
                                       AND su.teacher_id = ?
                LEFT JOIN exam_types et ON et.id = e.exam_type_id
                LEFT JOIN classes c ON c.id = e.class_id
                LEFT JOIN sections sec ON sec.id = e.section_id
                WHERE su.class_id IS NOT NULL';
        $params = [$teacherId];

        if ($classId) {
            $sql .= ' AND e.class_id = ?';
            $params[] = $classId;
        }
        if ($subjectId) {
            $sql .= ' AND su.id = ?';
            $params[] = $subjectId;
        }

        $sql .= ' ORDER BY e.start_date DESC';
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Upsert a result row.
     */
    public function upsert(array $data): void
    {
        $existing = $this->db->fetch(
            'SELECT id FROM results WHERE exam_id = ? AND student_id = ? AND subject_id = ?',
            [$data['exam_id'], $data['student_id'], $data['subject_id']]
        );

        if ($existing) {
            $this->db->update('results', $data, 'id = :pk', [':pk' => $existing['id']]);
        } else {
            $this->db->insert('results', $data);
        }
    }

    /**
     * Attendance summary for a student across an exam date window
     * (used on the printable report card).
     */
    public function attendanceSummary(int $studentId, ?string $from = null, ?string $to = null): array
    {
        $sql = 'SELECT status, COUNT(*) AS total FROM attendance WHERE student_id = ?';
        $params = [$studentId];

        if ($from) {
            $sql .= ' AND date >= ?';
            $params[] = $from;
        }
        if ($to) {
            $sql .= ' AND date <= ?';
            $params[] = $to;
        }

        $sql .= ' GROUP BY status';
        return $this->db->fetchAll($sql, $params);
    }
}
