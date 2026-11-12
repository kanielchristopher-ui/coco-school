<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Payroll';
?>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-money-check-alt me-2"></i>Salary Records</h6>
                <span class="badge bg-primary"><?= count($rows) ?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Employee</th><th>Period</th><th>Basic</th><th>Net</th><th>Status</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No salary records yet. Generate one on the right.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td>
                                    <strong><?= e(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?></strong>
                                    <div class="small text-muted"><?= e($r['employee_id'] ?? '—') ?> &bull; <?= e($r['department'] ?? '—') ?></div>
                                </td>
                                <td><?= e(ldate('F Y', mktime(0, 0, 0, (int) $r['month'], 1, (int) $r['year']))) ?></td>
                                <td><?= currency($r['basic_salary']) ?></td>
                                <td class="fw-bold text-success"><?= currency($r['net_salary']) ?></td>
                                <td><?= statusBadge($r['status']) ?></td>
                                <td class="text-end text-nowrap">
                                    <a href="<?= url('payroll/payslip/' . $r['id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Payslip"><i class="fas fa-file-alt"></i></a>
                                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('payroll/destroy/' . $r['id']) ?>')" title="Delete"><i class="fas fa-trash"></i></button>
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
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-plus me-2"></i>Generate Salary</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('payroll/store') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <label class="form-label">Teacher</label>
                        <select name="teacher_id" class="form-select">
                            <option value="">Select Teacher</option>
                            <?php foreach ($teachers as $t): ?>
                                <option value="<?= (int) $t['id'] ?>"><?= e($t['first_name'] . ' ' . $t['last_name']) ?> (<?= e($t['employee_id']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Staff Member</label>
                        <select name="staff_id" class="form-select">
                            <option value="">Select Staff</option>
                            <?php foreach ($staff as $s): ?>
                                <option value="<?= (int) $s['id'] ?>"><?= e($s['first_name'] . ' ' . $s['last_name']) ?> (<?= e($s['employee_id']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label required">Month</label>
                            <select name="month" class="form-select" required>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= $m === (int) date('n') ? 'selected' : '' ?>><?= ldate('F', mktime(0, 0, 0, $m, 1)) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label required">Year</label>
                            <input type="number" name="year" class="form-control" min="2000" max="2100" value="<?= date('Y') ?>" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Allowances</label>
                        <input type="number" name="allowances" class="form-control" min="0" step="0.01" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deductions</label>
                        <input type="number" name="deductions" class="form-control" min="0" step="0.01" value="0">
                    </div>
                    <button class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Generate Salary</button>
                </form>
            </div>
        </div>
    </div>
</div>
