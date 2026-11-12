<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Receipt Search';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <a href="<?= url('fees/index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Invoices</a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="<?= url('fees/receipts') ?>" class="row g-2 align-items-end">
            <div class="col-md-8">
                <label class="form-label small mb-1">Search Receipts</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-receipt"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Receipt no, invoice no, admission no, or student name..." value="<?= e($q) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary"><i class="fas fa-search me-1"></i>Search</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Matching Receipts</h6></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Receipt No</th><th>Student</th><th>Class</th><th>Invoice</th><th>Date</th><th>Amount</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4"><?= $q !== '' ? 'No receipts match your search.' : 'Type a receipt or invoice number to search past receipts.' ?></td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><strong><?= e($r['receipt_no']) ?></strong></td>
                        <td><?= e($r['first_name'] . ' ' . $r['last_name']) ?> <small class="text-muted">(<?= e($r['admission_no']) ?>)</small></td>
                        <td><?= e($r['class_name'] ?: '—') ?></td>
                        <td><?= e($r['invoice_no']) ?></td>
                        <td><?= formatDate($r['payment_date']) ?></td>
                        <td class="text-success fw-bold"><?= currency((float) $r['amount']) ?></td>
                        <td class="text-end">
                            <a href="<?= url('fees/receipt/' . $r['id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print Receipt"><i class="fas fa-print"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
