<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Promotions';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0"><i class="fas fa-arrow-circle-up me-2 text-primary"></i>Promotion Management</h5>
    </div>
    <a href="#" onclick="if (window.history.length > 1) { history.back(); } else { location.href='<?= url('dashboard/index') ?>'; } return false;" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-arrow-circle-up me-2"></i>Promote Students</h6>
                <span class="badge bg-primary"><?= count($students) ?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Official ID</th><th>Student</th><th>Current Class</th><th>Promote To</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No active students.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($students as $st): ?>
                            <tr>
                                <td><?= e($st['official_id'] ?? '—') ?></td>
                                <td><strong><?= e(trim($st['first_name'] . ' ' . $st['last_name'])) ?></strong></td>
                                <td><?= e($st['class_name'] ?? '—') ?></td>
                                <td style="min-width:200px;">
                                    <form method="POST" action="<?= url('results/promote/' . $st['id']) ?>" class="d-flex gap-2">
                                        <?= csrf_field() ?>
                                        <select name="to_class_id" class="form-select form-select-sm" required>
                                            <option value="">Select Class</option>
                                            <?php foreach ($classes as $c): ?>
                                                <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Remarks (optional)">
                                        <button class="btn btn-sm btn-primary text-nowrap"><i class="fas fa-arrow-up me-1"></i>Promote</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-history me-2"></i>Promotion History</h6>
                <span class="badge bg-primary"><?= count($history) ?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Student</th><th>From</th><th>To</th><th>Date</th><th>Remarks</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($history)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No promotions yet.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($history as $h): ?>
                            <tr>
                                <td><strong><?= e(trim($h['first_name'] . ' ' . $h['last_name'])) ?></strong></td>
                                <td><?= e($h['from_class'] ?? '—') ?></td>
                                <td><i class="fas fa-arrow-right text-primary"></i> <?= e($h['to_class'] ?? '—') ?></td>
                                <td><?= formatDate($h['promoted_date']) ?></td>
                                <td class="text-muted small"><?= e($h['remarks'] ?: '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
