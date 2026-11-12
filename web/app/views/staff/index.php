<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Staff';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <span class="text-muted small"><?= $total ?> staff member(s)</span>
    <a href="<?= url('staff/create') ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add Staff</a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('staff/index') ?>" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control" placeholder="Search name, employee ID or email..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <?php foreach (['active', 'inactive', 'left'] as $st): ?>
                        <option value="<?= $st ?>" <?= $status === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
                <a href="<?= url('staff/index') ?>" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Staff</th><th>Employee ID</th><th>Department</th><th>Designation</th><th>Phone</th><th>Salary</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No staff records found.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $s): ?>
                    <tr>
                        <td>
                            <strong><?= e(trim($s['first_name'] . ' ' . ($s['last_name'] ?? ''))) ?></strong>
                            <div class="small text-muted"><?= e($s['email'] ?? '') ?></div>
                        </td>
                        <td><?= e($s['employee_id'] ?? '—') ?></td>
                        <td><?= e($s['department'] ?: '—') ?></td>
                        <td><?= e($s['designation'] ?: '—') ?></td>
                        <td><?= e($s['phone'] ?: '—') ?></td>
                        <td><?= currency((float) ($s['basic_salary'] ?? 0)) ?></td>
                        <td><?= statusBadge($s['status']) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="<?= url('staff/edit/' . $s['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('staff/delete/' . $s['id']) ?>')" title="Delete"><i class="fas fa-trash"></i></button>
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
