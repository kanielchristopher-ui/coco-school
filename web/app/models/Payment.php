<?php
/**
 * Payment model.
 */
class Payment extends Model
{
    protected string $table = 'payments';

    /**
     * Payments with student and user names.
     */
    public function listAll(): array
    {
        return $this->db->fetchAll(
            'SELECT p.*, s.first_name, s.last_name, f.invoice_no
             FROM payments p
             INNER JOIN students s ON s.id = p.student_id
             INNER JOIN fees f ON f.id = p.fee_id
             ORDER BY p.id DESC'
        );
    }

    /**
     * Next receipt number.
     */
    public function nextReceiptNo(): string
    {
        $prefix = 'RCP-' . date('Y') . '-';
        $last = $this->db->fetch(
            'SELECT receipt_no FROM payments ORDER BY id DESC LIMIT 1'
        );
        $next = 1;
        if ($last) {
            $num = (int) substr($last['receipt_no'], strlen($prefix));
            $next = $num + 1;
        }
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Payments for a student.
     */
    public function forStudent(int $studentId): array
    {
        return $this->db->fetchAll(
            'SELECT p.*, f.invoice_no, ft.name AS fee_type_name
             FROM payments p
             INNER JOIN fees f ON f.id = p.fee_id
             LEFT JOIN fee_types ft ON ft.id = f.fee_type_id
             WHERE p.student_id = ?
             ORDER BY p.payment_date DESC',
            [$studentId]
        );
    }

    /**
     * Total collected for a month/year.
     */
    public function monthlyTotal(int $month, int $year): float
    {
        return (float) $this->db->fetchColumn(
            'SELECT COALESCE(SUM(amount), 0) FROM payments WHERE MONTH(payment_date) = ? AND YEAR(payment_date) = ?',
            [$month, $year]
        );
    }
}
