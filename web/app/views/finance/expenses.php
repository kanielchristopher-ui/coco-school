<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'School Expenses';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <span class="text-muted small"><?= $pagination->total() ?> expense(s)</span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('finance/categories') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-tags me-1"></i>Expense Categories</a>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#expenseModal"><i class="fas fa-plus me-1"></i>Record Expense</button>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body d-flex align-items-center justify-content-between py-3">
        <div>
            <div class="text-muted small">Total Expenses</div>
            <div class="fs-5 fw-bold text-danger"><?= currency($totalExpenses) ?></div>
        </div>
        <i class="fas fa-receipt fa-2x text-danger opacity-50"></i>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('finance/expenses') ?>" class="row g-2">
            <div class="col-md-4">
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= $categoryId === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
                <a href="<?= url('finance/expenses') ?>" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Date</th><th>Category</th><th>Description</th><th>Amount</th><th>Paid By</th><th>Notes</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No expenses recorded yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= formatDate($r['expense_date']) ?></td>
                        <td><?= e($r['category_name']) ?></td>
                        <td><?= e($r['description']) ?></td>
                        <td class="fw-semibold text-danger"><?= currency($r['amount']) ?></td>
                        <td><?= e(trim(($r['payer_first'] ?? '') . ' ' . ($r['payer_last'] ?? ''))) ?: '<span class="text-muted">—</span>' ?></td>
                        <td><?= $r['notes'] ? e($r['notes']) : '<span class="text-muted">—</span>' ?></td>
                        <td class="text-end text-nowrap">
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('finance/expenses-delete/' . $r['id']) ?>')" title="Delete"><i class="fas fa-trash"></i></button>
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

<!-- Record Expense Modal -->
<div class="modal fade" id="expenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('finance/expenses-store') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-receipt me-2"></i>Record Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Expense Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="What was it for?" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label required">Amount</label>
                            <input type="number" name="amount" class="form-control" min="0" step="0.01" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label required">Date</label>
                            <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional note"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>
