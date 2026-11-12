<?php
/**
 * ClassRoom model (table: classes).
 */
class ClassRoom extends Model
{
    protected string $table = 'classes';

    /**
     * List classes with section counts, subject counts and class teacher name.
     */
    public function listAll(): array
    {
        return $this->db->fetchAll(
            'SELECT c.*,
                    t.first_name AS teacher_first, t.last_name AS teacher_last,
                    (SELECT COUNT(*) FROM sections s WHERE s.class_id = c.id) AS section_count,
                    (SELECT COUNT(*) FROM subjects su WHERE su.class_id = c.id) AS subject_count,
                    (SELECT COUNT(*) FROM students st WHERE st.class_id = c.id) AS student_count
             FROM classes c
             LEFT JOIN teachers t ON t.id = c.class_teacher_id
             ORDER BY FIELD(c.cycle, \'maternel\', \'primaire\', \'secondaire\'), CAST(c.numeric_name AS UNSIGNED) ASC, c.name ASC'
        );
    }

    /**
     * Sections belonging to a class.
     */
    public function sections(int $classId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM sections WHERE class_id = ? ORDER BY name ASC',
            [$classId]
        );
    }
}
