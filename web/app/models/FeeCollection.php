<?php
/**
 * FeeCollection model (Finance module).
 */
declare(strict_types=1);

class FeeCollection extends Model
{
    protected string $table = 'fee_collections';

    /**
     * List collections with student, class and cashier info.
     */
    public function listAll(?int $classId = null): array
    {
        $sql = 'SELECT fc.*, s.first_name, s.last_name, s.admission_no, c.name AS class_name,
                       u.first_name AS cashier_first, u.last_name AS cashier_last
                FROM fee_collections fc
                INNER JOIN students s ON s.id = fc.student_id
                LEFT JOIN classes c ON c.id = fc.class_id
                LEFT JOIN users u ON u.id = fc.collected_by
                WHERE 1 = 1';
        $params = [];

        if ($classId) {
            $sql .= ' AND fc.class_id = ?';
            $params[] = $classId;
        }

        $sql .= ' ORDER BY fc.id DESC';
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Next sequential receipt number for the current year.
     */
    public function nextReceiptNo(): string
    {
        $prefix = 'RCT-' . date('Y') . '-';
        $last = $this->db->fetch(
            'SELECT receipt_no FROM fee_collections ORDER BY id DESC LIMIT 1'
        );
        $next = 1;
        if ($last && str_starts_with($last['receipt_no'], $prefix)) {
            $num = (int) substr($last['receipt_no'], strlen($prefix));
            $next = $num + 1;
        }
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Total amount collected (voided payments are excluded).
     */
    public function totalCollected(): float
    {
        return (float) $this->db->fetchColumn(
            "SELECT COALESCE(SUM(amount_paid), 0) FROM fee_collections WHERE status = 'active'"
        );
    }
}
