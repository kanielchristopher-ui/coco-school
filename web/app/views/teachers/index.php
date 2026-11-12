<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Teachers';

// Build a query string preserving active filters (optionally overridden).
$buildQuery = function (array $overrides = []) use ($search, $department, $qualification, $status, $sort, $dir): string {
    $params = array_merge([
        'q'             => $search,
        'department'    => $department,
        'qualification' => $qualification,
        'status'        => $status,
        'sort'          => $sort,
        'dir'           => $dir,
    ], $overrides);
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    return $params ? '?' . http_build_query($params) : '';
};

// Render a sortable table header.
$sortHeader = function (string $key, string $label) use ($buildQuery, $sort, $dir): string {
    $nextDir = ($sort === $key && $dir === 'asc') ? 'desc' : 'asc';
    $icon = '';
    if ($sort === $key) {
        $icon = $dir === 'asc' ? ' <i class="fas fa-sort-up"></i>' : ' <i class="fas fa-sort-down"></i>';
    }
    return '<a class="text-decoration-none text-reset" href="' . e($buildQuery(['sort' => $key, 'dir' => $nextDir])) . '">' . e($label) . $icon . '</a>';
};
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h4 mb-0">Teachers</h1>
        <span class="text-muted small"><?= number_format($total) ?> teacher(s) found</span>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= url('teachers/export-pdf' . $buildQuery([])) ?>" class="btn btn-sm btn-outline-danger"><i class="fas fa-file-pdf me-1"></i>Export PDF</a>
        <a href="<?= url('teachers/export-excel' . $buildQuery([])) ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-file-excel me-1"></i>Export Excel</a>
        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fas fa-print me-1"></i>Print</button>
        <a href="<?= url('teachers/index') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh / clear filters"><i class="fas fa-sync-alt me-1"></i>Refresh</a>
        <a href="<?= url('teachers/repair-links') ?>" class="btn btn-sm btn-outline-warning" title="Link teachers without a login account"><i class="fas fa-link me-1"></i>Repair Links</a>
        <a href="<?= url('teachers/create') ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add Teacher</a>
    </div>
</div>

<form method="GET" action="<?= url('teachers/index') ?>" class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4 col-lg-3">
                <label class="form-label small mb-1">Search Teacher</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Name, department, qualification..." value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small mb-1">Filter by Department</label>
                <select name="department" class="form-select form-select-sm">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= e($d) ?>" <?= $department === $d ? 'selected' : '' ?>><?= e($d) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small mb-1">Filter by Qualification</label>
                <select name="qualification" class="form-select form-select-sm">
                    <option value="">All Qualifications</option>
                    <?php foreach ($qualifications as $q): ?>
                        <option value="<?= e($q) ?>" <?= $qualification === $q ? 'selected' : '' ?>><?= e($q) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small mb-1">Filter by Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <?php foreach (['active', 'inactive', 'left'] as $s): ?>
                        <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 col-lg-1">
                <button class="btn btn-sm btn-primary w-100"><i class="fas fa-filter me-1"></i>Filter</button>
            </div>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Official ID</th>
                    <th><?= $sortHeader('name', 'Teacher Full Name') ?></th>
                    <th><?= $sortHeader('department', 'Department') ?></th>
                    <th><?= $sortHeader('qualification', 'Qualification') ?></th>
                    <th><?= $sortHeader('phone', 'Phone') ?></th>
                    <th><?= $sortHeader('salary', 'Salary') ?></th>
                    <th><?= $sortHeader('status', 'Status') ?></th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No teachers found.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $t): ?>
                    <tr>
                        <td><span class="fw-semibold text-primary"><?= e($t['official_id'] ?: '—') ?></span></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?= avatar($t['photo'], $t['first_name'] . ' ' . $t['last_name'], 36) ?>
                                <strong><?= e(trim($t['first_name'] . ' ' . $t['last_name'])) ?></strong>
                            </div>
                        </td>
                        <td><?= e($t['department'] ?: '—') ?></td>
                        <td class="small"><?= e($t['qualification'] ?: '—') ?></td>
                        <td><?= e($t['phone'] ?: '—') ?></td>
                        <td><?= currency((float) $t['salary']) ?></td>
                        <td><?= statusBadge($t['status']) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="<?= url('teachers/show/' . $t['id']) ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                            <a href="<?= url('teachers/edit/' . $t['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-sm btn-outline-danger" data-delete-url="<?= url('teachers/destroy/' . $t['id']) ?>" data-teacher-name="<?= e(trim(($t['first_name'] ?? '') . ' ' . ($t['last_name'] ?? ''))) ?>" onclick="confirmDeleteTeacher(this)" title="Delete"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pagination->pages() > 1): ?>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small class="text-muted">Showing <?= number_format($total === 0 ? 0 : $pagination->offset() + 1) ?>–<?= number_format(min($pagination->offset() + $pagination->limit(), $total)) ?> of <?= number_format($total) ?></small>
            <?= $pagination->render() ?>
        </div>
    <?php endif; ?>
</div>
