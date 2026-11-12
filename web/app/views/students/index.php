<?php if (!defined('APP_VERSION')) { exit; }
$isTeacher = Auth::role() === 'teacher';
$pageTitle = $isTeacher ? 'My Students' : 'Students';
$canManage = $canManage ?? false;

// Summary counts in the current scope (teacher: assigned classes only).
$summaryScope = $isTeacher ? Auth::assignedClassIds() : [];
$countInScope = static function (?string $status) use ($summaryScope): int {
    return count((new Student())->listAll(null, null, null, null, $status, 'id', 'asc', $summaryScope));
};
$summaryTotal   = $countInScope(null);
$summaryActive  = $countInScope('active');
$summaryClasses = count($classes);

// Build a query string preserving active filters (optionally overridden).
$buildQuery = function (array $overrides = []) use ($search, $classId, $sectionId, $gender, $status, $sort, $dir): string {
    $params = array_merge([
        'q'          => $search,
        'class_id'   => $classId ?: '',
        'section_id' => $sectionId ?: '',
        'gender'     => $gender,
        'status'     => $status,
        'sort'       => $sort,
        'dir'        => $dir,
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
<div class="card border-0 shadow-sm mb-4 page-hero bg-soft-indigo">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="stat-icon bg-primary-subtle text-primary"><i class="fas fa-user-graduate"></i></div>
            <div>
                <h1 class="h4 mb-0 fw-bold"><?= $isTeacher ? 'My Students' : 'Students' ?></h1>
                <span class="text-muted small"><?= $isTeacher ? 'View the students in your assigned classes.' : 'View and manage all student records.' ?></span>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?php if ($canManage): ?>
            <a href="<?= url('students/export-pdf' . $buildQuery([])) ?>" class="btn btn-sm btn-outline-danger"><i class="fas fa-file-pdf me-1"></i>Export PDF</a>
            <a href="<?= url('students/export-excel' . $buildQuery([])) ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-file-excel me-1"></i>Export Excel</a>
            <a href="<?= url('students/id-cards' . $buildQuery([])) ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-id-card me-1"></i>ID Cards</a>
            <?php endif; ?>
            <button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fas fa-print me-1"></i>Print</button>
            <a href="<?= url('students/index') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh / clear filters"><i class="fas fa-sync-alt me-1"></i>Refresh</a>
            <?php if ($canManage): ?>
            <a href="<?= url('students/repair-links') ?>" class="btn btn-sm btn-outline-warning" title="Link students without a login account"><i class="fas fa-link me-1"></i>Repair Links</a>
            <a href="<?= url('students/create') ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add Student</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="fas fa-user-graduate"></i></div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format($summaryTotal) ?></div>
                    <div class="text-muted small">Total Students</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success"><i class="fas fa-user-check"></i></div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format($summaryActive) ?></div>
                    <div class="text-muted small">Active Students</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info-subtle text-info"><i class="fas fa-school"></i></div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format($summaryClasses) ?></div>
                    <div class="text-muted small"><?= $isTeacher ? 'Assigned Classes' : 'Classes' ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<form method="GET" action="<?= url('students/index') ?>" class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-primary bg-gradient text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-filter me-2"></i>Filters</h6>
        <a href="<?= url('students/index') ?>" class="btn btn-sm btn-light"><i class="fas fa-undo me-1"></i>Clear all</a>
    </div>
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4 col-lg-3">
                <label class="form-label small fw-medium mb-1">Search Student</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Search by student name..." value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small fw-medium mb-1">Class</label>
                <select name="class_id" class="form-select form-select-sm class-select">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= $classId === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small fw-medium mb-1">Section</label>
                <select name="section_id" class="form-select form-select-sm section-select">
                    <option value="">All Sections</option>
                    <?php foreach ($sections as $sec): ?>
                        <option value="<?= (int) $sec['id'] ?>" <?= $sectionId === (int) $sec['id'] ? 'selected' : '' ?>><?= e($sec['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small fw-medium mb-1">Gender</label>
                <select name="gender" class="form-select form-select-sm">
                    <option value="">All Genders</option>
                    <?php foreach (['male', 'female', 'other'] as $g): ?>
                        <option value="<?= $g ?>" <?= $gender === $g ? 'selected' : '' ?>><?= ucfirst($g) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 col-lg-2">
                <label class="form-label small fw-medium mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <?php foreach (['active', 'inactive', 'alumni', 'transferred'] as $s): ?>
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
            <thead class="table-primary">
                <tr>
                    <th class="text-center">Photo</th>
                    <th>Official ID</th>
                    <th><?= $sortHeader('name', 'Student Name') ?></th>
                    <th><?= $sortHeader('class', 'Class / Section') ?></th>
                    <th><?= $sortHeader('gender', 'Gender') ?></th>
                    <th><?= $sortHeader('status', 'Status') ?></th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No students found.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $s): ?>
                    <tr>
                        <td class="text-center"><?= avatar($s['photo'], studentName($s), 40) ?></td>
                        <td><span class="fw-semibold text-primary"><?= e($s['official_id'] ?: '—') ?></span></td>
                        <td class="fw-semibold"><?= e(studentName($s)) ?></td>
                        <td><?= e($s['class_name'] ?: '—') ?><?= $s['section_name'] ? ' / ' . e($s['section_name']) : '' ?></td>
                        <td><?= ucfirst(e($s['gender'] ?: '—')) ?></td>
                        <td><?= statusBadge($s['status']) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="<?= url('students/show/' . $s['id']) ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                            <?php if ($canManage): ?>
                            <a href="<?= url('students/edit/' . $s['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-sm btn-outline-danger" data-delete-url="<?= url('students/destroy/' . $s['id']) ?>" data-student-name="<?= e(trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''))) ?>" onclick="confirmDeleteStudent(this)" title="Delete"><i class="fas fa-trash"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pagination->pages() > 1): ?>
        <div class="card-footer card-footer-soft d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small class="text-muted">Showing <?= number_format($total === 0 ? 0 : $pagination->offset() + 1) ?>–<?= number_format(min($pagination->offset() + $pagination->limit(), $total)) ?> of <?= number_format($total) ?></small>
            <?= $pagination->render() ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Reload sections when the class filter changes.
    document.querySelectorAll('.class-select').forEach(function (sel) {
        sel.addEventListener('change', function () {
            const sectionSelect = document.querySelector('.section-select');
            const params = new URLSearchParams(window.location.search);
            if (this.value) params.set('class_id', this.value); else params.delete('class_id');
            params.delete('section_id');
            window.location.href = window.location.pathname + '?' + params.toString();
        });
    });
});
</script>
