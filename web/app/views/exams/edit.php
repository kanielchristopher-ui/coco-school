<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Edit Exam';
$exam = $exam ?? [];
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <a href="<?= url('exams/index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Exams</a>
</div>

<div class="row g-3 justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="fw-bold mb-0"><i class="fas fa-pen me-2 text-primary"></i>Edit Exam</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('exams/update/' . (int) $exam['id']) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Subject</label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $s): ?>
                                <option value="<?= (int) $s['id'] ?>" <?= (int) ($exam['subject_id'] ?? 0) === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Exam Type</label>
                        <select name="exam_type_id" class="form-select">
                            <option value="">Exam Type</option>
                            <?php foreach ($examTypes as $et): ?>
                                <option value="<?= (int) $et['id'] ?>" <?= (int) ($exam['exam_type_id'] ?? 0) === (int) $et['id'] ? 'selected' : '' ?>><?= e($et['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Class</label>
                        <select name="class_id" id="examClassSelect" class="form-select" onchange="loadSections()">
                            <option value="">Class</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= (int) $c['id'] ?>" <?= (int) ($exam['class_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Section</label>
                        <select name="section_id" id="examSectionSelect" class="form-select">
                            <option value="">Section (All)</option>
                            <?php foreach ($sections as $sec): ?>
                                <option value="<?= (int) $sec['id'] ?>" <?= (int) ($exam['section_id'] ?? 0) === (int) $sec['id'] ? 'selected' : '' ?>><?= e($sec['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Academic Year</label>
                        <select name="session_id" class="form-select">
                            <option value="">Academic Year</option>
                            <?php foreach ($sessions as $ss): ?>
                                <option value="<?= (int) $ss['id'] ?>" <?= (int) ($exam['session_id'] ?? 0) === (int) $ss['id'] ? 'selected' : '' ?>><?= e($ss['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Exam Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?= e($exam['start_date'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (['upcoming' => 'Upcoming', 'active' => 'Active', 'completed' => 'Completed'] as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $exam['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Update Exam</button>
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
                opt.textContent = s.name;
                sel.appendChild(opt);
            });
        });
}
</script>
