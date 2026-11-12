<?php
/**
 * Fee model.
 */
class Fee extends Model
{
    protected string $table = 'fees';

    /**
     * List invoices with student and fee type info.
     */
    public function listAll(?string $search = null, ?string $status = null): array
    {
        $sql = 'SELECT f.*, s.first_name, s.last_name, s.admission_no, ft.name AS fee_type_name
                FROM fees f
                INNER JOIN students s ON s.id = f.student_id
                LEFT JOIN fee_types ft ON ft.id = f.fee_type_id
                WHERE 1 = 1';
        $params = [];

        if ($search) {
            $sql .= ' AND (s.first_name LIKE ? OR s.last_name LIKE ? OR f.invoice_no LIKE ?)';
            $like = '%' . $search . '%';
            $params = [$like, $like, $like];
        }
        if ($status) {
            $sql .= ' AND f.status = ?';
            $params[] = $status;
        }

        $sql .= ' ORDER BY f.id DESC';
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Next invoice number.
     */
    public function nextInvoiceNo(): string
    {
        $prefix = 'INV-' . date('Y') . '-';
        $last = $this->db->fetch(
            'SELECT invoice_no FROM fees ORDER BY id DESC LIMIT 1'
        );
        $next = 1;
        if ($last) {
            $num = (int) substr($last['invoice_no'], strlen($prefix));
            $next = $num + 1;
        }
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Sum of amounts collected / pending.
     */
    public function totals(string $status): float
    {
        return (float) $this->db->fetchColumn(
            'SELECT COALESCE(SUM(amount - discount_amount - scholarship), 0) FROM fees WHERE status = ?',
            [$status]
        );
    }
}
