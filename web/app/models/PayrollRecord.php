<?php
/**
 * PayrollRecord model (Finance module).
 */
declare(strict_types=1);

class PayrollRecord extends Model
{
    protected string $table = 'payrolls';

    /**
     * List payroll records with employee names.
     */
    public function listAll(): array
    {
        return $this->db->fetchAll(
            'SELECT p.*,
                    COALESCE(t.first_name, st.first_name) AS first_name,
                    COALESCE(t.last_name, st.last_name) AS last_name,
                    COALESCE(t.employee_id, st.employee_id) AS employee_id
             FROM payrolls p
             LEFT JOIN teachers t ON t.id = p.employee_id AND p.employee_type = "teacher"
             LEFT JOIN staff st ON st.id = p.employee_id AND p.employee_type = "staff"
             ORDER BY p.id DESC'
        );
    }

    /**
     * Total payroll amount.
     */
    public function totalAmount(): float
    {
        return (float) $this->db->fetchColumn('SELECT COALESCE(SUM(salary_amount), 0) FROM payrolls');
    }
}
