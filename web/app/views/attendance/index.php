<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Take Attendance';
$byId = [];
foreach ($rows as $r) { $byId[$r['student_id']] = $r; }
$className = '';
foreach ($classes as $c) { if ($classId === (int) $c['id']) { $className = $c['name']; break; } }
$sectionName = '';
foreach ($sections as $sec) { if ($sectionId === (int) $sec['id']) { $sectionName = $sec['name']; break; } }
?>
<div class="card border-0 shadow-sm mb-4 page-hero bg-soft-indigo">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="stat-icon bg-primary-subtle text-primary"><i class="fas fa-clipboard-check"></i></div>
            <div>
                <h1 class="h4 mb-0 fw-bold">Take Attendance</h1>
                <span class="text-muted small">Mark student attendance by class, section, date and subject.</span>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= url('attendance/report') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-chart-line me-1"></i>Reports</a>
            <a href="<?= url('attendance/teacher') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-user-check me-1"></i>Teacher Attendance</a>
        </div>
    </div>
</div>

<form method="GET" action="<?= url('attendance/index') ?>" class="mb-3">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary bg-gradient text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0 fw-semibold"><i class="fas fa-filter me-2"></i>Attendance Sheet</h6>
            <?php if ($classId): ?>
                <span class="badge bg-white text-primary"><i class="fas fa-users me-1"></i><?= count($students) ?> student(s)</span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card border-0 bg-soft-indigo h-100">
                        <div class="card-body py-3">
                            <label class="form-label small fw-semibold mb-1"><i class="fas fa-school me-1 text-primary"></i>Class Selection</label>
                            <select name="class_id" class="form-select" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= (int) $c['id'] ?>" <?= $classId === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 bg-soft-indigo h-100">
                        <div class="card-body py-3">
                            <label class="form-label small fw-semibold mb-1"><i class="fas fa-layer-group me-1 text-primary"></i>Section Selection</label>
                            <select name="section_id" class="form-select">
                                <option value="">All Sections</option>
                                <?php foreach ($sections as $sec): ?>
                                    <option value="<?= (int) $sec['id'] ?>" <?= $sectionId === (int) $sec['id'] ? 'selected' : '' ?>><?= e($sec['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 bg-soft-indigo h-100">
                        <div class="card-body py-3">
                            <label class="form-label small fw-semibold mb-1"><i class="fas fa-calendar-day me-1 text-primary"></i>Date and Attendance</label>
                            <input type="date" name="date" class="form-control" value="<?= e($date) ?>" required>
                            <div class="small text-dark mt-2"><i class="fas fa-circle-info me-1"></i><?= e(formatDate($date)) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary"><i class="fas fa-search me-1"></i>Load Attendance</button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php if ($classId && !empty($students)): ?>
    <form method="POST" action="<?= url('attendance/store') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="class_id" value="<?= $classId ?>">
        <input type="hidden" name="section_id" value="<?= $sectionId ?>">
        <input type="hidden" name="date" value="<?= e($date) ?>">

        <div class="card border-0 shadow-sm">
            <div class="card-header card-header-soft py-3">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-4">
                        <div class="card border-0 bg-soft-indigo h-100">
                            <div class="card-body py-3">
                                <label class="form-label small fw-semibold mb-2"><i class="fas fa-info-circle me-1 text-primary"></i>Attendance Information</label>
                                <div class="d-flex align-items-center gap-2 small text-body">
                                    <i class="fas fa-calendar-day text-primary"></i><span class="fw-semibold"><?= formatDate($date) ?></span>
                                </div>
                                <div class="d-flex align-items-center gap-2 small text-body mt-1">
                                    <i class="fas fa-school text-primary"></i><span><?= e($className ?: '—') ?><?= $sectionName ? ' / ' . e($sectionName) : '' ?></span>
                                </div>
                                <div class="d-flex align-items-center gap-2 small text-body mt-1">
                                    <i class="fas fa-users text-primary"></i><span><?= count($students) ?> student(s)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card border-0 bg-soft-indigo h-100">
                            <div class="card-body py-3">
                                <label class="form-label small fw-semibold mb-1"><i class="fas fa-book-open me-1 text-primary"></i>Subject Selection</label>
                                <select name="subject_id" id="att-subject" class="form-select" <?= !empty($subjects) ? 'required' : '' ?> onchange="updateSubjectCells(this)">
                                    <option value="">Select Subject</option>
                                    <?php foreach ($subjects as $s): ?>
                                        <option value="<?= (int) $s['id'] ?>" <?= $subjectId === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (empty($subjects)): ?>
                                    <div class="text-muted small mt-2"><i class="fas fa-circle-info me-1"></i>No subjects for this class.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label small fw-semibold text-body mb-1"><i class="fas fa-bolt me-1"></i>Quick Set</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-success" onclick="setAll('present')"><i class="fas fa-check-circle me-1"></i>All Present</button>
                            <button type="button" class="btn btn-sm btn-warning text-dark" onclick="setAll('late')"><i class="fas fa-clock me-1"></i>All Late</button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="setAll('absent')"><i class="fas fa-times-circle me-1"></i>All Absent</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th class="text-center">Photo</th>
                            <th>Official ID</th>
                            <th>Student Name</th>
                            <th>Subject</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $st): ?>
                            <?php $existing = $byId[$st['id']] ?? null; ?>
                            <tr>
                                <td class="text-center"><?= avatar($st['photo'] ?? null, trim(($st['first_name'] ?? '') . ' ' . ($st['last_name'] ?? '')), 40) ?></td>
                                <td><span class="fw-semibold text-primary"><?= e($st['official_id'] ?: '—') ?></span></td>
                                <td class="fw-semibold"><?= e(trim(($st['first_name'] ?? '') . ' ' . ($st['last_name'] ?? ''))) ?></td>
                                <td class="att-subject-cell text-muted"><?= e($subjectName ?: '—') ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm status-group" role="group">
                                        <?php foreach (['present', 'late', 'absent', 'leave'] as $status): ?>
                                            <input type="radio" class="btn-check" name="status[<?= $st['id'] ?>]" id="st-<?= $st['id'] ?>-<?= $status ?>"
                                                   value="<?= $status ?>" <?= ($existing && $existing['status'] === $status) || (!$existing && $status === 'present') ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-<?= match ($status) { 'present' => 'success', 'late' => 'warning', 'absent' => 'danger', 'leave' => 'info' } ?>" for="st-<?= $st['id'] ?>-<?= $status ?>">
                                                <i class="fas fa-<?= match ($status) { 'present' => 'check-circle', 'late' => 'clock', 'absent' => 'times-circle', 'leave' => 'calendar-minus' } ?> me-1"></i><?= ucfirst($status) ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer card-footer-soft d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-muted small"><i class="fas fa-users me-1"></i><?= count($students) ?> student(s) &middot; <?= formatDate($date) ?></span>
                <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Attendance</button>
            </div>
        </div>
    </form>
<?php elseif ($classId): ?>
    <div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-1"></i>No active students found in this class.</div>
<?php else: ?>
    <div class="alert alert-info"><i class="fas fa-circle-info me-1"></i>Select a class and date to mark attendance.</div>
<?php endif; ?>

<script>
function setAll(status) {
    document.querySelectorAll('.status-group input[type=radio]').forEach(function (radio) {
        if (radio.value === status) radio.checked = true;
    });
}
function updateSubjectCells(sel) {
    var label = sel.value ? sel.options[sel.selectedIndex].text : '—';
    document.querySelectorAll('.att-subject-cell').forEach(function (td) {
        td.textContent = label;
    });
}
</script>
