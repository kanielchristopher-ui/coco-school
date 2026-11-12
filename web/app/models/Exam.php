<?php
/**
 * Exam model.
 */
class Exam extends Model
{
    protected string $table = 'exams';

    /**
     * List exams with type, class, section, session and subject names.
     */
    public function listAll(?int $classId = null, ?string $status = null): array
    {
        $sql = 'SELECT e.*, et.name AS type_name, c.name AS class_name,
                       s.name AS section_name, ss.name AS session_name, su.name AS subject_name
                FROM exams e
                LEFT JOIN exam_types et ON et.id = e.exam_type_id
                LEFT JOIN classes c ON c.id = e.class_id
                LEFT JOIN sections s ON s.id = e.section_id
                LEFT JOIN academic_sessions ss ON ss.id = e.session_id
                LEFT JOIN subjects su ON su.id = e.subject_id
                WHERE 1 = 1';
        $params = [];

        if ($classId) {
            $sql .= ' AND e.class_id = ?';
            $params[] = $classId;
        }
        if ($status) {
            $sql .= ' AND e.status = ?';
            $params[] = $status;
        }

        $sql .= ' ORDER BY e.start_date DESC, e.id DESC';
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Exams for subjects assigned to the given teacher: only exams whose
     * subject the teacher teaches in the exam's own class.
     */
    public function teacherExams(int $teacherId, ?int $classId = null, ?string $status = null): array
    {
        $sql = 'SELECT DISTINCT e.*, et.name AS type_name, c.name AS class_name,
                       s.name AS section_name, ss.name AS session_name, su.name AS subject_name
                FROM exams e
                INNER JOIN subjects tsu ON tsu.id = e.subject_id
                                       AND tsu.class_id = e.class_id
                                       AND tsu.teacher_id = ?
                LEFT JOIN exam_types et ON et.id = e.exam_type_id
                LEFT JOIN classes c ON c.id = e.class_id
                LEFT JOIN sections s ON s.id = e.section_id
                LEFT JOIN academic_sessions ss ON ss.id = e.session_id
                LEFT JOIN subjects su ON su.id = e.subject_id
                WHERE 1 = 1';
        $params = [$teacherId];

        if ($classId) {
            $sql .= ' AND e.class_id = ?';
            $params[] = $classId;
        }
        if ($status) {
            $sql .= ' AND e.status = ?';
            $params[] = $status;
        }

        $sql .= ' ORDER BY e.start_date DESC, e.id DESC';
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Find a single exam with type/class/section/session/subject names.
     */
    public function findWithDetails(int $id): ?array
    {
        return $this->db->fetch(
            'SELECT e.*, et.name AS type_name, c.name AS class_name,
                    s.name AS section_name, ss.name AS session_name, su.name AS subject_name
             FROM exams e
             LEFT JOIN exam_types et ON et.id = e.exam_type_id
             LEFT JOIN classes c ON c.id = e.class_id
             LEFT JOIN sections s ON s.id = e.section_id
             LEFT JOIN academic_sessions ss ON ss.id = e.session_id
             LEFT JOIN subjects su ON su.id = e.subject_id
             WHERE e.id = ?',
            [$id]
        );
    }

    /**
     * All subjects available for exam assignment.
     */
    public function allSubjects(): array
    {
        return $this->db->fetchAll('SELECT * FROM subjects ORDER BY name ASC');
    }

    /**
     * Subjects for the exam's class.
     */
    public function subjects(int $classId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM subjects WHERE class_id = ? ORDER BY name ASC',
            [$classId]
        );
    }

    /**
     * Academic sessions available for exam assignment.
     */
    public function sessions(): array
    {
        return $this->db->fetchAll('SELECT * FROM academic_sessions ORDER BY start_date DESC');
    }
}
