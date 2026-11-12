<?php
$pageTitle = 'Discipline';
$sev = ['minor' => ['Minor', 'info'], 'moderate' => ['Moderate', 'warning'], 'serious' => ['Serious', 'danger']];
?>
<div class="row g-4">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h6 class="fw-bold mb-0"><i class="fas fa-gavel me-2 text-primary"></i>Record Incident</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('discipline/store') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label required">Student</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">Select Student</option>
                            <?php foreach ($students as $s): ?>
                                <option value="<?= (int) $s['id'] ?>"><?= e(studentName($s)) ?> — <?= e($s['class_name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label required">Incident Type</label>
                            <select name="type" class="form-select" required>
                                <?php foreach (DisciplineRecord::TYPES as $t): ?><option value="<?= e($t) ?>"><?= e($t) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label required">Date</label>
                            <input type="date" name="incident_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Severity</label>
                            <select name="severity" class="form-select">
                                <?php foreach ($sev as $k => $v): ?><option value="<?= $k ?>"><?= e($v[0]) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Sanction</label>
                            <select name="sanction" class="form-select">
                                <?php foreach (DisciplineRecord::SANCTIONS as $t): ?><option value="<?= $t === 'None' ? '' : e($t) ?>"><?= e($t) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="What happened?"></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="parent_notified" id="pn" value="1">
                        <label class="form-check-label" for="pn">Parents notified</label>
                    </div>
                    <button class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Save</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Discipline Records</h6>
                <form class="d-flex flex-wrap gap-2" method="GET" action="<?= url('discipline/index') ?>">
                    <input type="text" name="q" class="form-control form-control-sm" style="width:150px" placeholder="Search" value="<?= e($filters['q']) ?>">
                    <select name="class_id" class="form-select form-select-sm" style="width:150px">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $c): ?><option value="<?= (int) $c['id'] ?>" <?= $filters['class_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
                    </select>
                    <select name="status" class="form-select form-select-sm" style="width:120px">
                        <option value="">All Status</option>
                        <option value="open" <?= $filters['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="resolved" <?= $filters['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                    </select>
                    <button class="btn btn-sm btn-outline-primary">Filter</button>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Date</th><th>Student</th><th>Incident Type</th><th>Severity</th><th>Sanction</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php if (!$records): ?><tr><td colspan="7" class="text-center text-muted py-4">No incidents recorded.</td></tr><?php endif; ?>
                    <?php foreach ($records as $r): $sv = $sev[$r['severity']] ?? $sev['minor']; ?>
                        <tr>
                            <td class="text-nowrap"><?= formatDate($r['incident_date']) ?></td>
                            <td><strong><?= e(studentName($r)) ?></strong><div class="small text-muted"><?= e($r['class_name'] ?? '') ?></div></td>
                            <td><?= e($r['type']) ?><?php if (!empty($r['description'])): ?><div class="small text-muted"><?= e($r['description']) ?></div><?php endif; ?></td>
                            <td><span class="badge bg-<?= $sv[1] ?>-subtle text-<?= $sv[1] ?>"><?= e($sv[0]) ?></span></td>
                            <td><?= e($r['sanction'] ?: '—') ?><?php if ($r['parent_notified']): ?><div class="small text-success"><i class="fas fa-check"></i> Parents notified</div><?php endif; ?></td>
                            <td><?= statusBadge($r['status']) ?></td>
                            <td class="text-end text-nowrap">
                                <?php if ($r['status'] === 'open'): ?>
                                    <form method="POST" action="<?= url('discipline/resolve/' . (int) $r['id']) ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-outline-success" title="Resolve"><i class="fas fa-check"></i></button></form>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('discipline/destroy/' . (int) $r['id']) ?>')"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
