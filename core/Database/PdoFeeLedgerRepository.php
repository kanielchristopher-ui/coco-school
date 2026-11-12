<?php
declare(strict_types=1);

namespace CocoCore\Database;

use CocoCore\Contracts\FeeLedgerRepositoryInterface;

/** Accès aux tables student_fees et fee_collections (module Finance existant). */
final class PdoFeeLedgerRepository implements FeeLedgerRepositoryInterface
{
    public function __construct(private Connection $db)
    {
    }

    public function findStudentFee(int $feeId): ?array
    {
        $r = $this->db->fetch('SELECT id, student_id, class_id, fee_type, amount, month, status FROM student_fees WHERE id = ? FOR UPDATE', [$feeId]);
        if ($r) {
            $r['id'] = (int) $r['id'];
            $r['student_id'] = (int) $r['student_id'];
            $r['class_id'] = $r['class_id'] !== null ? (int) $r['class_id'] : null;
            $r['amount'] = (float) $r['amount'];
        }
        return $r;
    }

    public function activePaid(array $fee): float
    {
        // Même règle d'imputation que l'application historique : frais mensuel = paiements du mois de facturation.
        return (float) $this->db->fetchValue(
            'SELECT COALESCE(SUM(amount_paid), 0) FROM fee_collections
             WHERE student_id = ? AND fee_type = ? AND status = \'active\'
               AND (? IS NULL OR DATE_FORMAT(payment_date, \'%Y-%m\') = ?)',
            [$fee['student_id'], $fee['fee_type'], $fee['month'], $fee['month']]
        );
    }

    public function nextReceiptNo(int $year): string
    {
        $prefix = 'RCT-' . $year . '-';
        $last = $this->db->fetchValue('SELECT receipt_no FROM fee_collections WHERE receipt_no LIKE ? ORDER BY id DESC LIMIT 1 FOR UPDATE', [$prefix . '%']);
        $next = $last ? ((int) substr((string) $last, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function insertCollection(array $collection): int
    {
        return $this->db->insert('fee_collections', $collection);
    }

    public function setFeeStatus(int $feeId, string $status): void
    {
        $this->db->run('UPDATE student_fees SET status = ? WHERE id = ?', [$status, $feeId]);
    }
}
