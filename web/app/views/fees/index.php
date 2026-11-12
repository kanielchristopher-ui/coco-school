<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Fees & Payments';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <span class="text-muted small"><?= $total ?> invoice(s)</span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('fees/types') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-tags me-1"></i>Fee Types</a>
        <a href="<?= url('fees/receipts') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-search me-1"></i>Receipts</a>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#generateInvoiceModal"><i class="fas fa-plus me-1"></i>Generate Invoice</button>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('fees/index') ?>" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control" placeholder="Search invoice no or student..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <?php foreach (['pending', 'partial', 'paid', 'cancelled'] as $st): ?>
                        <option value="<?= $st ?>" <?= $status === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
                <a href="<?= url('fees/index') ?>" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Invoice</th><th>Student</th><th>Fee Type</th><th>Amount</th><th>Due Date</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No invoices found.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $f): ?>
                    <?php $net = $f['amount'] - $f['discount_amount'] - $f['scholarship']; ?>
                    <tr>
                        <td><strong><?= e($f['invoice_no']) ?></strong></td>
                        <td>
                            <strong><?= e($f['first_name'] . ' ' . $f['last_name']) ?></strong>
                            <div class="small text-muted"><?= e($f['admission_no']) ?></div>
                        </td>
                        <td><?= $f['fee_type_name'] ? e($f['fee_type_name']) : '<span class="text-muted">—</span>' ?></td>
                        <td><?= currency($net) ?></td>
                        <td><?= formatDate($f['due_date']) ?></td>
                        <td><?= statusBadge($f['status']) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="<?= url('fees/show/' . $f['id']) ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pagination->pages() > 1): ?>
        <div class="card-footer bg-white"><?= $pagination->render() ?></div>
    <?php endif; ?>
</div>

<!-- Generate Invoice Modal -->
<div class="modal fade" id="generateInvoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('fees/generate-invoice') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i>Generate Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Student</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">Select Student</option>
                            <?php foreach ($students as $s): ?>
                                <option value="<?= (int) $s['id'] ?>"><?= e($s['first_name'] . ' ' . $s['last_name']) ?> (<?= e($s['admission_no']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fee Type</label>
                        <select name="fee_type_id" class="form-select">
                            <option value="">General</option>
                            <?php foreach ($feeTypes as $ft): ?>
                                <option value="<?= (int) $ft['id'] ?>"><?= e($ft['name']) ?> (<?= currency($ft['amount']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label required">Amount</label>
                            <input type="number" name="amount" class="form-control" min="0" step="0.01" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Generate</button>
                </div>
            </form>
        </div>
    </div>
</div>
