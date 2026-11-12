<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Staff Payroll';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <span class="text-muted small"><?= $pagination->total() ?> payroll record(s)</span>
    </div>
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#payrollModal"><i class="fas fa-plus me-1"></i>Add Payroll Record</button>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body d-flex align-items-center justify-content-between py-3">
        <div>
            <div class="text-muted small">Total Payroll</div>
            <div class="fs-5 fw-bold text-success"><?= currency($totalPayroll) ?></div>
        </div>
        <i class="fas fa-hand-holding-usd fa-2x text-success opacity-50"></i>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Employee</th><th>Position</th><th>Salary Amount</th><th>Month</th><th>Payment Date</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No payroll records yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td>
                            <strong><?= e(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''))) ?></strong>
                            <div class="small text-muted"><?= e($r['employee_id'] ?? '—') ?> &bull; <?= e(ucfirst($r['employee_type'])) ?></div>
                        </td>
                        <td><?= $r['position'] ? e($r['position']) : '<span class="text-muted">—</span>' ?></td>
                        <td class="fw-semibold"><?= currency($r['salary_amount']) ?></td>
                        <td><?= e(ldate('F Y', strtotime($r['month'] . '-01'))) ?></td>
                        <td><?= formatDate($r['payment_date']) ?></td>
                        <td><?= statusBadge($r['status']) ?></td>
                        <td class="text-end text-nowrap">
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('finance/payroll-delete/' . $r['id']) ?>')" title="Delete"><i class="fas fa-trash"></i></button>
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

<!-- Add Payroll Record Modal -->
<div class="modal fade" id="payrollModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('finance/payroll-store') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-hand-holding-usd me-2"></i>Add Payroll Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Employee Type</label>
                        <select name="employee_type" class="form-select" required>
                            <option value="teacher">Teacher</option>
                            <option value="staff">School Staff</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <optgroup label="Teachers">
                                <?php foreach ($teachers as $t): ?>
                                    <option value="<?= (int) $t['id'] ?>" data-type="teacher"><?= e($t['first_name'] . ' ' . $t['last_name']) ?> (<?= e($t['employee_id']) ?>)</option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="School Staff">
                                <?php foreach ($staff as $s): ?>
                                    <option value="<?= (int) $s['id'] ?>" data-type="staff"><?= e($s['first_name'] . ' ' . $s['last_name']) ?> (<?= e($s['employee_id']) ?>)</option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Salary Amount</label>
                        <input type="number" name="salary_amount" class="form-control" min="0" step="0.01" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label required">Month</label>
                            <select name="month" class="form-select" required>
                                <?php foreach ($months as $m): ?>
                                    <option value="<?= $m ?>" <?= $m === date('Y-m') ? 'selected' : '' ?>><?= e(ldate('F Y', strtotime($m . '-01'))) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label required">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label required">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="paid">Paid</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Notes</label>
                            <input type="text" name="notes" class="form-control" placeholder="Optional">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Payroll</button>
                </div>
            </form>
        </div>
    </div>
</div>
