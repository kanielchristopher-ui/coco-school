<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Exams';
$canManage = $canManage ?? false;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="d-flex gap-2">
        <form method="GET" class="d-flex gap-2">
            <select name="class_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Classes</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= $classId === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <?php foreach (['upcoming' => 'Upcoming', 'active' => 'Active', 'completed' => 'Completed'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $statusFilter === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <div class="d-flex gap-2">
        <?php if ($canManage): ?>
        <a href="<?= url('grades/index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-clipboard-list me-1"></i>Exam Result</a>
        <a href="<?= url('exams/types') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-tags me-1"></i>Exam Types</a>
        <?php else: ?>
        <a href="<?= url('results/teacher-entry') ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-edit me-1"></i>Marks Entry</a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary bg-gradient text-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="fas fa-file-signature me-2"></i>Examinations</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary"><tr><th>Subject</th><th>Type</th><th>Class</th><th>Session</th><th>Date</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No exams found.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($rows as $ex): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($ex['subject_name'] ?: $ex['name']) ?></td>
                                <td><?= e($ex['type_name'] ?: '—') ?></td>
                                <td>
                                    <?= e($ex['class_name'] ?: '—') ?>
                                    <?php if (!empty($ex['section_name'])): ?>
                                        <span class="badge bg-secondary-subtle text-secondary"><?= e($ex['section_name']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="small"><?= e($ex['session_name'] ?: '—') ?></td>
                                <td class="small"><?= formatDate($ex['start_date']) ?></td>
                                <td><?= statusBadge($ex['status']) ?></td>
                                <td class="text-end text-nowrap">
                                    <?php if ($canManage): ?>
                                    <form method="POST" action="<?= url('exams/update-status/' . $ex['id']) ?>" class="d-inline-flex align-items-center gap-1">
                                        <?= csrf_field() ?>
                                        <select name="status" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                                            <?php foreach (['draft' => 'Draft', 'upcoming' => 'Upcoming', 'active' => 'Active', 'published' => 'Published', 'completed' => 'Completed'] as $val => $label): ?>
                                                <option value="<?= $val ?>" <?= $ex['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                    <a href="<?= url('results/entry/' . $ex['id']) ?>" class="btn btn-sm btn-outline-primary" title="Marks Entry"><i class="fas fa-edit"></i></a>
                                    <a href="<?= url('results/rank/' . $ex['id']) ?>" class="btn btn-sm btn-outline-info" title="Results"><i class="fas fa-trophy"></i></a>
                                    <a href="<?= url('exams/edit/' . $ex['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fas fa-pen"></i></a>
                                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('exams/destroy/' . $ex['id']) ?>')"><i class="fas fa-trash"></i></button>
                                    <?php else: ?>
                                    <form method="POST" action="<?= url('exams/update-status/' . $ex['id']) ?>" class="d-inline-flex align-items-center gap-1">
                                        <?= csrf_field() ?>
                                        <select name="status" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()" title="Publish / change status">
                                            <?php foreach (['draft' => 'Draft', 'upcoming' => 'Upcoming', 'active' => 'Active', 'published' => 'Published', 'completed' => 'Completed'] as $val => $label): ?>
                                                <option value="<?= $val ?>" <?= $ex['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                    <a href="<?= url('exams/edit/' . $ex['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fas fa-pen"></i></a>
                                    <a href="<?= url('results/teacher-entry?exam_id=' . $ex['id'] . '&class_id=' . $ex['class_id'] . '&subject_id=' . $ex['subject_id']) ?>" class="btn btn-sm btn-outline-success" title="Enter Marks"><i class="fas fa-edit"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary bg-gradient text-white"><h6 class="fw-bold mb-0"><i class="fas fa-plus-circle me-2"></i>Create Exam</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('exams/store') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <select name="subject_id" class="form-select" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $s): ?>
                                <option value="<?= (int) $s['id'] ?>"><?= e($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <select name="exam_type_id" class="form-select">
                            <option value="">Exam Type</option>
                            <?php foreach ($examTypes as $et): ?>
                                <option value="<?= (int) $et['id'] ?>"><?= e($et['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <select name="class_id" id="examClassSelect" class="form-select" onchange="loadSections()">
                            <option value="">Class</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <select name="section_id" id="examSectionSelect" class="form-select">
                            <option value="">Section (All)</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <select name="session_id" class="form-select">
                            <option value="">Academic Year</option>
                            <?php foreach ($sessions as $ss): ?>
                                <option value="<?= (int) $ss['id'] ?>" <?= (new Setting())->get('academic_session_id') == $ss['id'] ? 'selected' : '' ?>><?= e($ss['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2"><input type="date" name="start_date" class="form-control" placeholder="Exam Date"></div>
                    <div class="mb-3">
                        <select name="status" class="form-select">
                            <option value="upcoming">Upcoming</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <button class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i>Create Exam</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function loadSections() {
    const classId = document.getElementById('examClassSelect').value;
    const sel = document.getElementById('examSectionSelect');
    sel.innerHTML = '<option value="">Section (All)</option>';
    if (!classId) return;
    fetch('<?= url('exams/sections-json') ?>?class_id=' + encodeURIComponent(classId))
        .then(r => r.json())
        .then(data => {
            data.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.name + (s.room_no ? ' (' + s.room_no + ')' : '');
                sel.appendChild(opt);
            });
        });
}
</script>
