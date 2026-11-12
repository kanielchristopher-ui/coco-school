<?php
/**
 * Subject model.
 */
class Subject extends Model
{
    protected string $table = 'subjects';

    /**
     * List subjects with class and teacher names.
     */
    public function listAll(): array
    {
        return $this->db->fetchAll(
            'SELECT su.*, c.name AS class_name, t.first_name AS teacher_first, t.last_name AS teacher_last
             FROM subjects su
             LEFT JOIN classes c ON c.id = su.class_id
             LEFT JOIN teachers t ON t.id = su.teacher_id
             ORDER BY su.name ASC'
        );
    }

    /**
     * Subjects assigned to a specific teacher.
     */
    public function listForTeacher(int $teacherId): array
    {
        return $this->db->fetchAll(
            'SELECT su.*, c.name AS class_name, t.first_name AS teacher_first, t.last_name AS teacher_last
             FROM subjects su
             LEFT JOIN classes c ON c.id = su.class_id
             LEFT JOIN teachers t ON t.id = su.teacher_id
             WHERE su.teacher_id = ?
             ORDER BY su.name ASC',
            [$teacherId]
        );
    }

    /**
     * Classes in which a specific teacher teaches at least one subject.
     */
    public function classIdsForTeacher(int $teacherId): array
    {
        return array_map('intval', $this->db->fetchCol(
            'SELECT DISTINCT class_id FROM subjects WHERE teacher_id = ? AND class_id IS NOT NULL',
            [$teacherId]
        ));
    }
}
