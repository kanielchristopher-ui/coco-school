<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Fee Collection';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <span class="text-muted small"><?= $pagination->total() ?> payment(s)</span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('finance/receipts') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-file-invoice me-1"></i>Payment Receipts</a>
        <?php if ($voidedCount > 0): ?>
            <form method="POST" action="<?= url('finance/collections-delete-voided') ?>" class="d-inline" onsubmit="return confirm('Delete all <?= (int) $voidedCount ?> voided payment record(s) and their related receipts/notifications? This action cannot be undone.');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove all voided payment records"><i class="fas fa-trash-alt me-1"></i>Remove Voided (<?= (int) $voidedCount ?>)</button>
            </form>
        <?php endif; ?>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#collectModal"><i class="fas fa-hand-holding-usd me-1"></i>Record Payment</button>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body d-flex align-items-center justify-content-between py-3">
        <div>
            <div class="text-muted small">Total Collected</div>
            <div class="fs-5 fw-bold text-success"><?= currency($totalCollected) ?></div>
        </div>
        <i class="fas fa-hand-holding-usd fa-2x text-success opacity-50"></i>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('finance/collections') ?>" class="row g-2">
            <div class="col-md-4">
                <select name="class_id" class="form-select">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= $classId === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
                <a href="<?= url('finance/collections') ?>" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Receipt No</th><th>Student</th><th>Class</th><th>Fee Type</th><th>Amount</th><th>Date</th><th>Method</th><th>Collected By</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="10" class="text-center text-muted py-4">No payments recorded yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r): ?>
                    <?php $isVoided = ($r['status'] ?? 'active') === 'voided'; ?>
                    <tr class="<?= $isVoided ? 'table-light text-muted' : '' ?>">
                        <td>
                            <strong><?= e($r['receipt_no']) ?></strong>
                            <?php if ($isVoided): ?>
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
                        <td><?= e(trim(($r['cashier_first'] ?? '') . ' ' . ($r['cashier_last'] ?? ''))) ?: '<span class="text-muted">—</span>' ?></td>
                        <td><?= $isVoided ? '<span class="badge bg-danger">Voided</span>' : '<span class="badge bg-success">Completed</span>' ?></td>
                        <td class="text-end text-nowrap">
                            <a href="<?= url('finance/receipt/' . $r['id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Receipt"><i class="fas fa-file-invoice"></i></a>
                            <?php if ($isVoided): ?>
                                <form method="POST" action="<?= url('finance/collections-delete/' . (int) $r['id']) ?>" class="d-inline" onsubmit="return confirm('Delete this voided payment record and its related receipt/notification? This action cannot be undone.');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Voided Payment"><i class="fas fa-trash"></i></button>
                                </form>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-outline-primary editPaymentBtn" title="Edit Payment"
                                        data-action="<?= url('finance/collections-update/' . (int) $r['id']) ?>"
                                        data-student="<?= e(studentName($r)) ?>"
                                        data-fee-type="<?= e($r['fee_type']) ?>"
                                        data-amount="<?= e((string) $r['amount_paid']) ?>"
                                        data-date="<?= e($r['payment_date']) ?>"
                                        data-method="<?= e($r['payment_method']) ?>"
                                        data-notes="<?= e((string) ($r['notes'] ?? '')) ?>"><i class="fas fa-edit"></i></button>
                                <form method="POST" action="<?= url('finance/collections-void/' . (int) $r['id']) ?>" class="d-inline" onsubmit="return confirm('Void this payment? The receipt will be marked VOIDED and the student will be notified. This action cannot be undone.');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Void Payment"><i class="fas fa-ban"></i></button>
                                </form>
                            <?php endif; ?>
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

<!-- Record Payment Modal -->
<div class="modal fade" id="collectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('finance/collections-store') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-hand-holding-usd me-2"></i>Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Outstanding Fee</label>
                        <select name="fee_id" id="collectFeeId" class="form-select">
                            <option value="">-- Select a pending / partially paid fee --</option>
                            <?php foreach ($availableFees as $af): ?>
                                <option value="<?= (int) $af['id'] ?>"
                                    data-student="<?= (int) $af['student_id'] ?>"
                                    data-fee-type="<?= e($af['fee_type']) ?>"
                                    data-amount="<?= e(number_format($af['remaining'], 2, '.', '')) ?>"
                                    data-month="<?= e((string) ($af['month'] ?? '')) ?>">
                                    <?= e(studentName($af)) ?> (<?= e($af['admission_no']) ?>) — <?= e($af['fee_type']) ?><?= $af['month'] ? ' — ' . e($af['month']) : '' ?> — <?= currency($af['remaining']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Only fees that are still pending or partially paid are listed. Fully paid fees cannot be paid again.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Student</label>
                        <select name="student_id" id="collectStudent" class="form-select" required>
                            <option value="">Select Student</option>
                            <?php foreach ($students as $s): ?>
                                <option value="<?= (int) $s['id'] ?>"><?= e(studentName($s)) ?> (<?= e($s['admission_no']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label required">Fee Type</label>
                            <select name="fee_type" id="collectFeeType" class="form-select" required>
                                <option value="">Select Fee Type</option>
                                <?php foreach ($feeTypes as $ft): ?>
                                    <option value="<?= e($ft) ?>"><?= e($ft) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label required">Amount Paid</label>
                            <input type="number" name="amount_paid" id="collectAmount" class="form-control" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label required">Payment Date</label>
                            <input type="date" name="payment_date" id="collectDate" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label required">Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                <?php foreach ($methods as $key => $label): ?>
                                    <option value="<?= $key ?>"><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional note"></textarea>
                    </div>
                    <div class="alert alert-light border small mb-0"><i class="fas fa-info-circle me-1"></i>A payment receipt will be generated automatically after saving.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save & Generate Receipt</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Payment Modal -->
<div class="modal fade" id="editPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editPaymentForm" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Student</label>
                        <input type="text" id="editStudentName" class="form-control" readonly>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label required">Fee Type</label>
                            <select name="fee_type" id="editFeeType" class="form-select" required>
                                <option value="">Select Fee Type</option>
                                <?php foreach ($feeTypes as $ft): ?>
                                    <option value="<?= e($ft) ?>"><?= e($ft) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label required">Amount Paid</label>
                            <input type="number" name="amount_paid" id="editAmount" class="form-control" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label required">Payment Date</label>
                            <input type="date" name="payment_date" id="editDate" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label required">Payment Method</label>
                            <select name="payment_method" id="editMethod" class="form-select" required>
                                <?php foreach ($methods as $key => $label): ?>
                                    <option value="<?= $key ?>"><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="editNotes" class="form-control" rows="2" placeholder="Optional note"></textarea>
                    </div>
                    <div class="alert alert-light border small mb-0"><i class="fas fa-info-circle me-1"></i>The existing receipt is updated automatically. No duplicate receipt is created.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('editPaymentModal');
    const form = document.getElementById('editPaymentForm');
    if (!modal || !form) return;
    document.querySelectorAll('.editPaymentBtn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            form.action = btn.getAttribute('data-action') || '';
            document.getElementById('editStudentName').value = btn.getAttribute('data-student') || '';
            document.getElementById('editFeeType').value = btn.getAttribute('data-fee-type') || '';
            document.getElementById('editAmount').value = btn.getAttribute('data-amount') || '';
            document.getElementById('editDate').value = btn.getAttribute('data-date') || '';
            document.getElementById('editMethod').value = btn.getAttribute('data-method') || '';
            document.getElementById('editNotes').value = btn.getAttribute('data-notes') || '';
            bootstrap.Modal.getOrCreateInstance(modal).show();
        });
    });
})();
</script>

<script>
(function () {
    const feeSelect = document.getElementById('collectFeeId');
    if (!feeSelect) return;
    feeSelect.addEventListener('change', function () {
        const opt = feeSelect.selectedOptions[0];
        if (!opt || !opt.value) return;
        const studentEl = document.getElementById('collectStudent');
        const feeTypeEl = document.getElementById('collectFeeType');
        const amountEl  = document.getElementById('collectAmount');
        const dateEl    = document.getElementById('collectDate');
        if (studentEl) studentEl.value = opt.getAttribute('data-student') || '';
        if (feeTypeEl) feeTypeEl.value = opt.getAttribute('data-fee-type') || '';
        if (amountEl)  amountEl.value  = opt.getAttribute('data-amount') || '';
        const month = opt.getAttribute('data-month') || '';
        if (dateEl && /^\d{4}-\d{2}$/.test(month)) {
            dateEl.value = month + '-01';
        }
    });
})();
</script>
