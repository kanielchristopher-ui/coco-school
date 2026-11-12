<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Marks Entry';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 card card-body border-0 shadow-sm bg-soft-indigo">
    <h5 class="mb-0 fw-bold"><i class="fas fa-edit me-2 text-primary"></i>Subject-wise Marks Entry</h5>
    <span class="small text-dark">You can only enter marks for subjects assigned to you.</span>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="<?= url('results/teacher-entry') ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Class</label>
                <select name="class_id" id="tClass" class="form-select" onchange="filterSubjects(this.value)">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= $classId === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Subject</label>
                <select name="subject_id" id="tSubject" class="form-select" onchange="this.form.submit()">
                    <option value="">Select Subject</option>
                    <?php foreach ($subjects as $su): ?>
                        <option value="<?= (int) $su['id'] ?>"
                            data-class="<?= (int) $su['class_id'] ?>"
                            <?= $subjectId === (int) $su['id'] ? 'selected' : '' ?>>
                            <?= e($su['name']) ?><?= $su['class_name'] ? ' (' . e($su['class_name']) . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Exam</label>
                <select name="exam_id" id="tExam" class="form-select" onchange="this.form.submit()">
                    <option value="">Select Exam</option>
                    <?php foreach ($exams as $ex): ?>
                        <?php
                            $exLabel = $ex['type_name'] ? e($ex['type_name']) : e($ex['name']);
                            if ($ex['name'] && $ex['type_name']) { $exLabel .= ' - ' . e($ex['name']); }
                            if ($ex['class_name']) { $exLabel .= ' - ' . e($ex['class_name']); }
                            if ($ex['section_name']) { $exLabel .= ' (' . e($ex['section_name']) . ')'; }
                        ?>
                        <option value="<?= (int) $ex['id'] ?>" <?= $examId === (int) $ex['id'] ? 'selected' : '' ?>>
                            <?= $exLabel ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<?php if ($classId && $subjectId && $examId): ?>
    <?php if ($publishStatus === 'published' || $publishStatus === 'locked'): ?>
        <div class="alert alert-<?= $publishStatus === 'locked' ? 'danger' : 'success' ?> d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="mb-0">
                <strong><i class="fas fa-<?= $publishStatus === 'locked' ? 'lock' : 'check-circle' ?> me-1"></i> Results are <?= e($publishStatus) ?>.</strong>
                These results are locked from editing and are now visible to students.
                <?php if ($publishStatus === 'published'): ?>Ask the admin to reopen them if changes are needed.<?php endif; ?>
            </div>
            <a href="<?= url('results/review/' . (int) $examId . '/' . (int) $subjectId) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-clipboard-check me-1"></i>Review Results</a>
        </div>
    <?php elseif ($publishStatus === 'submitted'): ?>
        <div class="alert alert-primary d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="mb-0">
                <strong><i class="fas fa-paper-plane me-1"></i> Results submitted for review.</strong>
                These results are waiting for the admin to approve them. You cannot edit them until the admin returns them for correction.
            </div>
            <a href="<?= url('results/review/' . (int) $examId . '/' . (int) $subjectId) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-clipboard-check me-1"></i>Review Results</a>
        </div>
    <?php elseif ($publishStatus === 'approved'): ?>
        <div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="mb-0">
                <strong><i class="fas fa-check-circle me-1"></i> Results approved.</strong>
                The admin has approved these results. They will be published to students shortly.
            </div>
            <a href="<?= url('results/review/' . (int) $examId . '/' . (int) $subjectId) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-clipboard-check me-1"></i>Review Results</a>
        </div>
    <?php elseif (empty($students)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-users fa-3x mb-3 d-block"></i>
                No active students in this class for the selected subject.
            </div>
        </div>
    <?php else: ?>
        <?php if ($publishStatus === 'returned'): ?>
            <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="mb-0">
                    <strong><i class="fas fa-undo me-1"></i> Results returned for correction.</strong>
                    The admin has returned these results. Correct the marks below, then submit them again for review.
                    <?php if (!empty($correctionComment)): ?>
                        <div class="mt-2 mb-0 alert alert-warning-subtle border-0 py-2">
                            <strong>Admin comment:</strong> <?= e($correctionComment) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <form method="POST" action="<?= url('results/teacher-store') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="class_id" value="<?= (int) $classId ?>">
            <input type="hidden" name="subject_id" value="<?= (int) $subjectId ?>">
            <input type="hidden" name="exam_id" value="<?= (int) $examId ?>">
<?php
    $selName = 'Subject';
    foreach ($subjects as $su) { if ((int) $su['id'] === $subjectId) { $selName = $su['name']; break; } }
?>
<div class="card border-0 shadow-sm">
                <div class="card-header bg-primary bg-gradient text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h6 class="mb-0"><i class="fas fa-file-signature me-2"></i><?= e($selName) ?></h6>
                    <span class="d-flex align-items-center gap-2">
                        <?php if (!empty($existing)): ?><?= statusBadge($publishStatus) ?><?php endif; ?>
                        <span class="small opacity-75">Full Mark 100</span>
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-primary">
                            <tr>
                                <th style="min-width:190px;">Student</th>
                                <th class="text-center" style="width:120px;">Marks Obtained</th>
                                <th class="text-center" style="width:90px;">Grade</th>
                                <th style="min-width:180px;">Remarks</th>
                                <th style="min-width:180px;">Teacher Comments</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $st): ?>
                                <?php $row = $existing[$st['id']] ?? null; ?>
                                <tr>
                                    <td class="text-nowrap">
                                        <strong><?= e(studentName($st)) ?></strong>
                                        <div class="small text-muted">Roll: <?= e($st['roll_no'] ?: '—') ?></div>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" name="marks[<?= (int) $st['id'] ?>]"
                                               class="form-control form-control-sm text-center"
                                               step="0.01" min="0" max="100" placeholder="—"
                                               value="<?= e($row['marks_obtained'] ?? '') ?>">
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row): ?>
                                            <span class="badge bg-primary-subtle text-primary"><?= e($row['grade']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">auto</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <input type="text" name="remarks[<?= (int) $st['id'] ?>]" class="form-control form-control-sm" placeholder="Remarks" value="<?= e($row['remarks'] ?? '') ?>">
                                    </td>
                                    <td>
                                        <input type="text" name="teacher_comments[<?= (int) $st['id'] ?>]" class="form-control form-control-sm" placeholder="Comments" value="<?= e($row['teacher_comments'] ?? '') ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer card-footer-soft">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <span class="text-muted small">Grades are calculated automatically from the saved grade scale.</span>
                        <div class="d-flex gap-2">
                            <a href="<?= url('results/review/' . (int) $examId . '/' . (int) $subjectId) ?>" class="btn btn-success"><i class="fas fa-clipboard-check me-1"></i>Review Results</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Marks</button>
                            <button type="submit" class="btn btn-secondary" formaction="<?= url('results/submit/' . (int) $examId . '/' . (int) $subjectId) ?>" title="Submit these marks to the admin for review and publishing."><i class="fas fa-paper-plane me-1"></i>Submit Results</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center text-muted py-5">
            <i class="fas fa-chalkboard-teacher fa-3x mb-3 d-block"></i>
            Select a class, subject and exam to enter marks.
        </div>
    </div>
<?php endif; ?>

<script>
function filterSubjects(className) {
    // Client-side hint only; the full filter applies on submit.
    const sel = document.getElementById('tSubject');
    const opts = sel.querySelectorAll('option[data-class]');
    let visible = false;
    opts.forEach(o => {
        const match = !className || o.dataset.class === className;
        o.style.display = match ? '' : 'none';
        if (match && o.value === sel.value) visible = true;
    });
    if (!visible) sel.value = '';
}
if (document.getElementById('tClass')) {
    filterSubjects(document.getElementById('tClass').value);
}
</script>
