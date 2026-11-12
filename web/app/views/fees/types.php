<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Fee Types';
?>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-plus me-2"></i>Add Fee Type</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('fees/store-type') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <label class="form-label required">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Tuition Fee" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Default Amount</label>
                        <input type="number" name="amount" class="form-control" min="0" step="0.01" value="0">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Frequency</label>
                        <select name="frequency" class="form-select">
                            <?php foreach (['one_time', 'monthly', 'quarterly', 'yearly'] as $freq): ?>
                                <option value="<?= $freq ?>" <?= $freq === 'monthly' ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $freq)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <button class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Add Fee Type</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-tags me-2"></i>Fee Types</h6>
                <span class="badge bg-primary"><?= count($rows) ?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Name</th><th>Amount</th><th>Frequency</th><th>Status</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No fee types defined.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($rows as $t): ?>
                            <tr>
                                <td>
                                    <strong><?= e($t['name']) ?></strong>
                                    <?php if (!empty($t['description'])): ?>
                                        <div class="small text-muted"><?= e($t['description']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= currency($t['amount']) ?></td>
                                <td><?= e(ucwords(str_replace('_', ' ', $t['frequency']))) ?></td>
                                <td><?= $t['status'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('fees/destroy-type/' . $t['id']) ?>')"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
