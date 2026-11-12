<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Student Fees';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <span class="text-muted small"><?= $pagination->total() ?> fee record(s)</span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('finance/reports?type=pending-fees') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-chart-line me-1"></i>Pending Report</a>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addFeeModal"><i class="fas fa-plus me-1"></i>Add Student Fee</button>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">Pending Fees</div>
                    <div class="fs-5 fw-bold text-warning"><?= currency($summary['pending']) ?></div>
                </div>
                <i class="fas fa-hourglass-half fa-2x text-warning opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">Partially Paid</div>
                    <div class="fs-5 fw-bold text-info"><?= currency($summary['partial']) ?></div>
                </div>
                <i class="fas fa-piggy-bank fa-2x text-info opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">Paid Fees</div>
                    <div class="fs-5 fw-bold text-success"><?= currency($summary['paid']) ?></div>
                </div>
                <i class="fas fa-check-circle fa-2x text-success opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('finance/fees') ?>" class="row g-2">
            <div class="col-md-4">
                <select name="class_id" class="form-select">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= $classId === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <?php foreach (['pending', 'partial', 'paid'] as $st): ?>
                        <option value="<?= $st ?>" <?= $status === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
                <a href="<?= url('finance/fees') ?>" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Student</th><th>Class</th><th>Fee Type</th><th>Amount</th><th>Session</th><th>Month</th><th>Due Date</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No student fees found.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $f): ?>
                    <tr>
                        <td>
                            <strong><?= e(studentName($f)) ?></strong>
                            <div class="small text-muted"><?= e($f['admission_no']) ?></div>
                        </td>
                        <td><?= $f['class_name'] ? e($f['class_name']) : '<span class="text-muted">—</span>' ?></td>
                        <td><?= e($f['fee_type']) ?></td>
                        <td><?= currency($f['amount']) ?></td>
                        <td><?= e($f['academic_session']) ?></td>
                        <td><?= $f['month'] ? e($f['month']) : '<span class="text-muted">—</span>' ?></td>
                        <td><?= formatDate($f['due_date']) ?></td>
                        <td><?= statusBadge($f['status']) ?></td>
                        <td class="text-end text-nowrap">
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteStudentFee(<?= (int) $f['id'] ?>, <?= !empty($f['has_related']) ? 'true' : 'false' ?>)" title="Delete"><i class="fas fa-trash"></i></button>
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

<!-- Add Student Fee Modal -->
<div class="modal fade" id="addFeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('finance/fees-store') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-money-check-alt me-2"></i>Add Student Fee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Student</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">Select Student</option>
                            <?php foreach ($students as $s): ?>
                                <option value="<?= (int) $s['id'] ?>"><?= e(studentName($s)) ?> (<?= e($s['admission_no']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label required">Fee Type</label>
                            <select name="fee_type" class="form-select" required>
                                <option value="">Select Fee Type</option>
                                <?php foreach ($feeTypes as $ft): ?>
                                    <option value="<?= e($ft) ?>"><?= e($ft) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label required">Amount</label>
                            <input type="number" name="amount" class="form-control" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Academic Session</label>
                        <select name="academic_session" class="form-select" required>
                            <option value="">Select Session</option>
                            <?php foreach ($sessions as $s): ?>
                                <option value="<?= e($s['name']) ?>"><?= e($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Month (Optional)</label>
                            <input type="month" name="month" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (['pending', 'partial', 'paid'] as $st): ?>
                                <option value="<?= $st ?>" <?= $st === 'pending' ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Fee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    window.deleteStudentFee = function (id, hasRelated) {
        const url = '<?= url('finance/fees-delete/') ?>' + id;
        const message = hasRelated
            ? 'This Student Fee has related payment records, receipts, and notifications. Deleting it will remove all related Finance records. Continue?'
            : 'Are you sure you want to delete this record? This action cannot be undone.';
        const modalEl = document.getElementById('confirmModal');
        if (!modalEl || !window.bootstrap) {
            if (window.confirm(message)) {
                window.location.href = url;
            }
            return;
        }
        const btn = document.getElementById('confirmActionBtn');
        const body = modalEl.querySelector('.modal-body');
        if (btn) {
            btn.href = url;
            btn.className = 'btn btn-danger btn-sm';
            btn.textContent = 'Yes, delete';
        }
        if (body) {
            body.textContent = message;
        }
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };
})();
</script>
