<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Invoice ' . $invoice['invoice_no'];
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <a href="<?= url('fees/index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>All Invoices</a>
    <div class="d-flex gap-2">
        <?php if ($balance > 0): ?>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal"><i class="fas fa-money-bill me-1"></i>Record Payment</button>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Invoice Details</h6>
                <?= statusBadge($invoice['status']) ?>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr><th class="text-muted">Invoice No</th><td><strong><?= e($invoice['invoice_no']) ?></strong></td></tr>
                        <tr><th class="text-muted">Student</th><td><?= e($invoice['first_name'] . ' ' . $invoice['last_name']) ?> <small class="text-muted">(<?= e($invoice['admission_no']) ?>)</small></td></tr>
                        <tr><th class="text-muted">Class</th><td><?= e($invoice['class_name'] ?: '—') ?></td></tr>
                        <tr><th class="text-muted">Fee Type</th><td><?= e($invoice['fee_type_name'] ?: 'General') ?></td></tr>
                        <tr><th class="text-muted">Due Date</th><td><?= formatDate($invoice['due_date']) ?></td></tr>
                    </tbody>
                </table>
                <hr>
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr><th class="text-muted">Amount</th><td class="text-end"><?= currency($invoice['amount']) ?></td></tr>
                        <tr><th class="text-muted">Discount</th><td class="text-end text-danger">-<?= currency($invoice['discount_amount']) ?></td></tr>
                        <tr><th class="text-muted">Scholarship</th><td class="text-end text-danger">-<?= currency($invoice['scholarship']) ?></td></tr>
                        <tr><th class="text-muted">Net Amount</th><td class="text-end fw-bold"><?= currency($netAmount) ?></td></tr>
                        <tr><th class="text-muted text-success">Paid</th><td class="text-end text-success fw-bold"><?= currency($paid) ?></td></tr>
                        <tr class="table-light">
                            <th class="<?= $balance > 0 ? 'text-danger' : 'text-success' ?>">Balance</th>
                            <td class="text-end fw-bold <?= $balance > 0 ? 'text-danger' : 'text-success' ?>"><?= currency($balance) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Payment History</h6></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Receipt</th><th>Date</th><th>Method</th><th>Amount</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No payments recorded yet.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($payments as $p): ?>
                            <tr>
                                <td><strong><?= e($p['receipt_no']) ?></strong></td>
                                <td><?= formatDate($p['payment_date']) ?></td>
                                <td><?= e(ucfirst($p['payment_method'])) ?></td>
                                <td class="text-success fw-bold"><?= currency($p['amount']) ?></td>
                                <td class="text-end">
                                    <a href="<?= url('fees/receipt/' . $p['id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print Receipt"><i class="fas fa-print"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('fees/add-payment/' . $invoice['id']) ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-money-bill me-2"></i>Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2">Outstanding balance: <strong><?= currency($balance) ?></strong></div>
                    <div class="mb-3">
                        <label class="form-label required">Amount</label>
                        <input type="number" name="amount" class="form-control" min="0.01" max="<?= $balance ?>" step="0.01" value="<?= $balance ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <?php foreach (['cash', 'bank_transfer', 'cheque', 'card', 'online'] as $m): ?>
                                <option value="<?= $m ?>"><?= ucwords(str_replace('_', ' ', $m)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transaction ID</label>
                        <input type="text" name="transaction_id" class="form-control">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Remark</label>
                        <textarea name="remark" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i>Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
