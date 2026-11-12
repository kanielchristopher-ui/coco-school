<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'My Fees / Receipts';
$className = $student['class_name'] ?? '';
$totalDue = 0.0;
$totalPaid = 0.0;
foreach ($fees as $f) {
    $totalDue += (float) $f['amount'];
    $totalPaid += (float) $f['amount_paid'];
}
$balance = max(0.0, $totalDue - $totalPaid);
?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body bg-soft-indigo rounded p-4">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="stat-icon bg-primary-subtle text-primary"><i class="fas fa-money-bill-wave"></i></div>
            <div>
                <h5 class="mb-0 fw-bold">My Fees / Receipts</h5>
                <small class="text-body d-block">
                    <i class="fas fa-user me-1"></i><?= e(studentName($student)) ?>
                    <?= $student['admission_no'] ? ' &bull; ' . e($student['admission_no']) : '' ?>
                    <?= $className ? ' &bull; ' . e($className) : '' ?>
                </small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <div class="text-muted small">Total Due</div>
                    <div class="fw-bold fs-5"><?= currency($totalDue) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success"><i class="fas fa-hand-holding-usd"></i></div>
                <div>
                    <div class="text-muted small">Total Paid</div>
                    <div class="fw-bold fs-5"><?= currency($totalPaid) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning-subtle text-warning"><i class="fas fa-scale-balanced"></i></div>
                <div>
                    <div class="text-muted small">Outstanding Balance</div>
                    <div class="fw-bold fs-5"><?= currency($balance) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary bg-gradient text-white">
        <h6 class="mb-0 fw-semibold text-white"><i class="fas fa-money-check-alt me-2"></i>My Fee Statement</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-primary">
                <tr>
                    <th>Fee Type</th>
                    <th>Amount Due</th>
                    <th>Amount Paid</th>
                    <th>Balance</th>
                    <th>Payment Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($fees)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No fees recorded for you.</td></tr>
                <?php endif; ?>
                <?php foreach ($fees as $f): ?>
                    <?php $due = (float) $f['amount']; $paid = (float) $f['amount_paid']; $bal = max(0.0, $due - $paid); ?>
                    <tr>
                        <td>
                            <strong><?= e($f['fee_type']) ?></strong>
                            <?php if ($f['month']): ?><div class="small text-muted"><?= e($f['month']) ?></div><?php endif; ?>
                            <?php if ($f['academic_session']): ?><div class="small text-muted"><?= e($f['academic_session']) ?></div><?php endif; ?>
                        </td>
                        <td><?= currency($due) ?></td>
                        <td><?= currency($paid) ?></td>
                        <td class="<?= $bal > 0 ? 'text-danger fw-bold' : 'text-success fw-bold' ?>"><?= currency($bal) ?></td>
                        <td><?= formatDate($f['payment_date'] ?? null) ?></td>
                        <td><?= statusBadge($f['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-primary bg-gradient text-white">
        <h6 class="mb-0 fw-semibold text-white"><i class="fas fa-file-invoice me-2"></i>Payment History</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-primary">
                <tr>
                    <th>Receipt No</th>
                    <th>Fee Type</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Received By</th>
                    <th class="text-end">Receipt</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($receipts)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No payments recorded for you.</td></tr>
                <?php endif; ?>
                <?php foreach ($receipts as $r): ?>
                    <?php $cashier = trim(($r['cashier_first'] ?? '') . ' ' . ($r['cashier_last'] ?? '')); ?>
                    <tr>
                        <td>
                            <strong><?= e($r['receipt_no']) ?></strong>
                            <?php if (($r['status'] ?? 'active') === 'voided'): ?>
                                <span class="badge bg-danger ms-1">Voided</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($r['fee_type']) ?></td>
                        <td><?= currency((float) $r['amount_paid']) ?></td>
                        <td><?= formatDate($r['payment_date']) ?></td>
                        <td><?= e(ucwords(str_replace('_', ' ', $r['payment_method']))) ?></td>
                        <td><?= e($cashier ?: '—') ?></td>
                        <td class="text-end text-nowrap">
                            <a href="<?= url('finance/my-receipt/' . (int) $r['id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-print me-1"></i>View Receipt</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
