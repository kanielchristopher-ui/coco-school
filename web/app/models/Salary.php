<?php
/**
 * Salary model.
 */
class Salary extends Model
{
    protected string $table = 'salary';

    /**
     * List salary records with payee names.
     */
    public function listAll(): array
    {
        return $this->db->fetchAll(
            'SELECT sal.*,
                    COALESCE(t.first_name, st.first_name) AS first_name,
                    COALESCE(t.last_name, st.last_name) AS last_name,
                    COALESCE(t.employee_id, st.employee_id) AS employee_id
             FROM salary sal
             LEFT JOIN teachers t ON t.id = sal.teacher_id
             LEFT JOIN staff st ON st.id = sal.staff_id
             ORDER BY sal.year DESC, sal.month DESC'
        );
    }
}
