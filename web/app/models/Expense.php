<?php
/**
 * Expense model (Finance module).
 */
declare(strict_types=1);

class Expense extends Model
{
    protected string $table = 'expenses';

    /**
     * List expenses with category and payer info.
     */
    public function listAll(?int $categoryId = null): array
    {
        $sql = 'SELECT e.*, ec.name AS category_name,
                       u.first_name AS payer_first, u.last_name AS payer_last
                FROM expenses e
                INNER JOIN expense_categories ec ON ec.id = e.category_id
                LEFT JOIN users u ON u.id = e.paid_by
                WHERE 1 = 1';
        $params = [];

        if ($categoryId) {
            $sql .= ' AND e.category_id = ?';
            $params[] = $categoryId;
        }

        $sql .= ' ORDER BY e.id DESC';
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Total amount spent.
     */
    public function totalAmount(): float
    {
        return (float) $this->db->fetchColumn('SELECT COALESCE(SUM(amount), 0) FROM expenses');
    }
}
