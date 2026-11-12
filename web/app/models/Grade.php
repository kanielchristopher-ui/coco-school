<?php
/**
 * Grade model - grading scale (table: grades) plus exam result
 * helpers that store simple marks rows in the results table.
 */
class Grade extends Model
{
    protected string $table = 'grades';

    /**
     * Create a simple exam result row (standalone, no exam link).
     */
    public function storeResult(array $data): int
    {
        return $this->db->insert('results', [
            'exam_id'        => null,
            'student_id'     => (int) $data['student_id'],
            'subject_id'     => (int) $data['subject_id'],
            'result_date'    => $data['result_date'] ?? null,
            'marks_obtained' => (float) $data['marks_obtained'],
            'max_marks'      => 100,
            'grade'          => null,
            'remarks'        => null,
            'teacher_comments' => null,
        ]);
    }

    /**
     * Update a simple exam result row (only rows without an exam link).
     */
    public function updateResult(int $id, array $data): bool
    {
        return $this->db->update(
            'results',
            [
                'student_id'     => (int) $data['student_id'],
                'subject_id'     => (int) $data['subject_id'],
                'result_date'    => $data['result_date'] ?? null,
                'marks_obtained' => (float) $data['marks_obtained'],
            ],
            'id = :pk AND exam_id IS NULL',
            [':pk' => $id]
        ) >= 0;
    }

    /**
     * Delete a simple exam result row (only rows without an exam link).
     */
    public function deleteResult(int $id): bool
    {
        return $this->db->delete('results', 'id = ? AND exam_id IS NULL', [$id]) > 0;
    }

    /**
     * All grades ordered by minimum percentage descending.
     */
    public function all(string $orderBy = '', string $direction = 'ASC'): array
    {
        return $this->db->fetchAll('SELECT * FROM grades ORDER BY min_percent DESC');
    }

    /**
     * Resolve the grade for a percentage from the stored scale.
     * Returns the highest band whose min_percent <= percentage.
     */
    public function forPercentage(float $percentage): array
    {
        $row = $this->db->fetch(
            'SELECT * FROM grades
             WHERE min_percent <= ? AND is_active = 1
             ORDER BY min_percent DESC LIMIT 1',
            [$percentage]
        );
        return $row ?: ['grade' => 'F', 'point' => 0.0, 'remarks' => 'Fail'];
    }
}
