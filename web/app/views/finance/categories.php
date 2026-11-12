<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Expense Categories';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <span class="text-muted small"><?= count($rows) ?> category(ies)</span>
    </div>
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal"><i class="fas fa-plus me-1"></i>Add Category</button>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Name</th><th>Description</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No categories yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $i => $c): ?>
                    <tr>
                        <td class="text-muted"><?= $i + 1 ?></td>
                        <td><strong><?= e($c['name']) ?></strong></td>
                        <td><?= $c['description'] ? e($c['description']) : '<span class="text-muted">—</span>' ?></td>
                        <td class="text-end text-nowrap">
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('finance/categories-delete/' . $c['id']) ?>')" title="Delete"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('finance/categories-store') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-tags me-2"></i>Add Expense Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Generator Fuel" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="Optional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
