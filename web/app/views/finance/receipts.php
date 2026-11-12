<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Payment Receipts';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <span class="text-muted small"><?= count($rows) ?> receipt(s)</span>
    </div>
    <div>
        <a href="<?= url('finance/collections') ?>" class="btn btn-sm btn-primary"><i class="fas fa-hand-holding-usd me-1"></i>Record Payment</a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('finance/receipts') ?>" class="row g-2">
            <div class="col-md-6">
                <input type="text" name="q" class="form-control" placeholder="Search receipt no, admission no or student name..." value="<?= e($q) ?>">
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
                <a href="<?= url('finance/receipts') ?>" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Receipt No</th><th>Student</th><th>Class</th><th>Fee Type</th><th>Amount Paid</th><th>Date</th><th>Method</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No receipts found.<?= $q === '' ? ' Record a payment to generate one.' : '' ?></td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td>
                            <strong><?= e($r['receipt_no']) ?></strong>
                            <?php if (($r['status'] ?? 'active') === 'voided'): ?>
                                <span class="badge bg-danger ms-1">Voided</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= e(studentName($r)) ?></strong>
                            <div class="small text-muted"><?= e($r['admission_no']) ?></div>
                        </td>
                        <td><?= $r['class_name'] ? e($r['class_name']) : '<span class="text-muted">—</span>' ?></td>
                        <td><?= e($r['fee_type']) ?></td>
                        <td class="fw-semibold"><?= currency($r['amount_paid']) ?></td>
                        <td><?= formatDate($r['payment_date']) ?></td>
                        <td><?= e(ucwords(str_replace('_', ' ', $r['payment_method']))) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="<?= url('finance/receipt/' . $r['id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                            <a href="<?= url('finance/receipt/' . $r['id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print"><i class="fas fa-print"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
