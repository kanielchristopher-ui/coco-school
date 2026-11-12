<?php
/**
 * StudentFee model (Finance module).
 */
declare(strict_types=1);

class StudentFee extends Model
{
    protected string $table = 'student_fees';

    /**
     * List student fees with student and class info.
     */
    public function listAll(?string $status = null, ?int $classId = null): array
    {
        $sql = 'SELECT f.*, s.first_name, s.last_name, s.admission_no, c.name AS class_name
                FROM student_fees f
                INNER JOIN students s ON s.id = f.student_id
                LEFT JOIN classes c ON c.id = f.class_id
                WHERE 1 = 1';
        $params = [];

        if ($status) {
            $sql .= ' AND f.status = ?';
            $params[] = $status;
        }
        if ($classId) {
            $sql .= ' AND f.class_id = ?';
            $params[] = $classId;
        }

        $sql .= ' ORDER BY f.id DESC';
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Total amount recorded for a given status.
     */
    public function totalAmount(string $status): float
    {
        return (float) $this->db->fetchColumn(
            'SELECT COALESCE(SUM(amount), 0) FROM student_fees WHERE status = ?',
            [$status]
        );
    }

    /**
     * Grand total of all student fees.
     */
    public function grandTotal(): float
    {
        return (float) $this->db->fetchColumn('SELECT COALESCE(SUM(amount), 0) FROM student_fees');
    }

    /**
     * Auto-synchronize Student Fee status after a payment is recorded in
     * Fee Collection. The fee is matched to the payment the same way the
     * student portal computes amounts paid: same student, same fee type,
     * and (for monthly fees) the same billing month as the payment date.
     *
     * - accumulated amount paid >= amount due  =>  paid
     * - accumulated amount paid > 0            =>  partial
     * - no payments yet                        =>  pending
     */
    public function syncStatusAfterPayment(int $studentId, string $feeType, string $paymentDate): void
    {
        $fees = $this->db->fetchAll(
            'SELECT f.* FROM student_fees f
             WHERE f.student_id = ? AND f.fee_type = ?
               AND (f.month IS NULL OR DATE_FORMAT(?, "%Y-%m") = f.month)',
            [$studentId, $feeType, $paymentDate]
        );

        foreach ($fees as $fee) {
            $totalPaid = (float) $this->db->fetchColumn(
                'SELECT COALESCE(SUM(fc.amount_paid), 0)
                 FROM fee_collections fc
                 WHERE fc.student_id = ? AND fc.fee_type = ?
                   AND fc.status = "active"
                   AND (? IS NULL OR DATE_FORMAT(fc.payment_date, "%Y-%m") = ?)',
                [$studentId, $feeType, $fee['month'] ?: null, $fee['month']]
            );

            $status = $totalPaid >= (float) $fee['amount'] ? 'paid' : ($totalPaid > 0 ? 'partial' : 'pending');
            if ($status !== $fee['status']) {
                $this->update((int) $fee['id'], ['status' => $status]);
            }
        }
    }
}
