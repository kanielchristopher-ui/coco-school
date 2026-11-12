<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Review & Publish Results';
$status = $status ?? 'draft';
$publishedAt = $publishedAt ?? null;
$correctionComment = $correctionComment ?? null;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <a href="<?= url('results/teacher-entry?class_id=' . (int) $exam['class_id'] . '&subject_id=' . (int) $subject['id'] . '&exam_id=' . (int) $exam['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Marks Entry</a>
    <div class="d-flex align-items-center gap-2">
        <span class="text-muted small">Status:</span>
        <?= statusBadge($status) ?>
        <?php if ($publishedAt): ?>
            <span class="small text-muted">Published <?= e(formatDate($publishedAt, 'M d, Y H:i')) ?></span>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-8">
                <h5 class="mb-1"><?= e($exam['name']) ?></h5>
                <div class="text-muted small">
                    Subject: <strong><?= e($subject['name']) ?></strong> &bull;
                    Class: <?= e($exam['class_name'] ?: '—') ?><?= !empty($exam['section_name']) ? ' (' . e($exam['section_name']) . ')' : '' ?> &bull;
                    Total: <?= e($exam['type_name'] ?? '—') ?>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <?php if ($status === 'draft' && $canSubmit && !empty($rows)): ?>
                <form method="POST" action="<?= url('results/submit/' . (int) $exam['id'] . '/' . (int) $subject['id']) ?>" class="d-inline" onsubmit="return confirm('Submit these results to the admin for review?');">
                    <?= csrf_field() ?>
                    <button class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Submit for Review</button>
                </form>
                <?php elseif ($status === 'submitted'): ?>
                    <?php if ($canManage): ?>
                    <form method="POST" action="<?= url('results/publish/' . (int) $exam['id'] . '/' . (int) $subject['id']) ?>" class="d-inline">
                        <?= csrf_field() ?>
                        <button class="btn btn-success"><i class="fas fa-bullhorn me-1"></i>Publish Results</button>
                    </form>
                    <form method="POST" action="<?= url('results/approve/' . (int) $exam['id'] . '/' . (int) $subject['id']) ?>" class="d-inline ms-1">
                        <?= csrf_field() ?>
                        <button class="btn btn-outline-info"><i class="fas fa-check-double me-1"></i>Approve</button>
                    </form>
                    <div class="mt-2 text-start">
                        <form method="POST" action="<?= url('results/return-results/' . (int) $exam['id'] . '/' . (int) $subject['id']) ?>" onsubmit="return confirm('Return these results to the teacher for correction?');">
                            <?= csrf_field() ?>
                            <textarea name="comment" class="form-control form-control-sm mb-1" rows="2" placeholder="Reason for returning (required)" required></textarea>
                            <button class="btn btn-warning btn-sm w-100"><i class="fas fa-undo me-1"></i>Return for Correction</button>
                        </form>
                    </div>
                    <?php else: ?>
                    <span class="badge bg-primary-subtle text-primary"><i class="fas fa-paper-plane me-1"></i>Submitted (awaiting review)</span>
                    <?php endif; ?>
                <?php elseif ($status === 'approved'): ?>
                    <?php if ($canPublish): ?>
                    <form method="POST" action="<?= url('results/publish/' . (int) $exam['id'] . '/' . (int) $subject['id']) ?>" class="d-inline">
                        <?= csrf_field() ?>
                        <button class="btn btn-success"><i class="fas fa-bullhorn me-1"></i>Publish Results</button>
                    </form>
                    <?php endif; ?>
                    <?php if ($canManage): ?>
                    <div class="mt-2 text-start">
                        <form method="POST" action="<?= url('results/return-results/' . (int) $exam['id'] . '/' . (int) $subject['id']) ?>" onsubmit="return confirm('Return these results to the teacher for correction?');">
                            <?= csrf_field() ?>
                            <textarea name="comment" class="form-control form-control-sm mb-1" rows="2" placeholder="Reason for returning (required)" required></textarea>
                            <button class="btn btn-warning btn-sm w-100"><i class="fas fa-undo me-1"></i>Return for Correction</button>
                        </form>
                    </div>
                    <?php endif; ?>
                <?php elseif ($status === 'returned'): ?>
                    <span class="badge bg-warning-subtle text-warning"><i class="fas fa-undo me-1"></i>Returned for correction</span>
                    <?php if ($canManage): ?>
                    <form method="POST" action="<?= url('results/approve/' . (int) $exam['id'] . '/' . (int) $subject['id']) ?>" class="d-inline ms-1">
                        <?= csrf_field() ?>
                        <button class="btn btn-success btn-sm"><i class="fas fa-check-double me-1"></i>Approve</button>
                    </form>
                    <?php endif; ?>
                <?php elseif ($status === 'published'): ?>
                <span class="badge bg-success-subtle text-success"><i class="fas fa-check me-1"></i>Visible to students</span>
                <?php elseif ($status === 'locked'): ?>
                <span class="badge bg-danger-subtle text-danger"><i class="fas fa-lock me-1"></i>Locked (final)</span>
                <?php endif; ?>

                <?php if ($canManage): ?>
                <a href="<?= url('results/teacher-entry?class_id=' . (int) $exam['class_id'] . '&subject_id=' . (int) $subject['id'] . '&exam_id=' . (int) $exam['id']) ?>" class="btn btn-sm btn-outline-secondary ms-1" title="Edit Marks"><i class="fas fa-edit me-1"></i>Edit Marks</a>
                <?php endif; ?>

                <?php if ($canManage && $status === 'published'): ?>
                <div class="mt-2 text-start">
                    <form method="POST" action="<?= url('results/reopen/' . (int) $exam['id'] . '/' . (int) $subject['id']) ?>" onsubmit="return confirm('Reopen these results for editing? Students will no longer see them until republished.');">
                        <?= csrf_field() ?>
                        <textarea name="comment" class="form-control form-control-sm mb-1" rows="2" placeholder="Reason for reopening (required)" required></textarea>
                        <button class="btn btn-sm btn-outline-warning w-100"><i class="fas fa-undo me-1"></i>Reopen</button>
                    </form>
                </div>
                <?php endif; ?>
                <?php if ($canManage && $status !== 'locked'): ?>
                <form method="POST" action="<?= url('results/lock/' . (int) $exam['id'] . '/' . (int) $subject['id']) ?>" class="d-inline ms-1" onsubmit="return confirm('Lock these results? They cannot be modified afterwards.');">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-lock me-1"></i>Lock</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($correctionComment)): ?>
<div class="alert alert-warning d-flex gap-2 align-items-start">
    <i class="fas fa-undo mt-1"></i>
    <div>
        <strong>Correction requested by the admin:</strong>
        <div class="mb-0"><?= e($correctionComment) ?></div>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><h6 class="fw-bold mb-0"><i class="fas fa-clipboard-check me-2 text-primary"></i>Entered Results (<?= count($rows) ?> students)</h6></div>
    <?php if (empty($rows)): ?>
        <div class="card-body text-center text-muted py-5">
            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
            No results have been entered for this exam & subject yet. Enter marks first, then publish.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>#</th><th>Student</th><th class="text-center">Marks</th><th class="text-center">Max</th><th class="text-center">%</th><th class="text-center">Grade</th><th>Remarks</th><th>Teacher Comments</th></tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach ($rows as $r): ?>
                        <?php $pct = ((float) $r['max_marks'] > 0) ? round(((float) $r['marks_obtained'] / (float) $r['max_marks']) * 100, 2) : 0; ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td class="fw-semibold"><?= e(studentName($r)) ?> <span class="text-muted small">(Roll <?= e($r['roll_no'] ?: '—') ?>)</span></td>
                            <td class="text-center"><strong><?= e($r['marks_obtained']) ?></strong></td>
                            <td class="text-center"><?= e($r['max_marks']) ?></td>
                            <td class="text-center"><?= e($pct) ?>%</td>
                            <td class="text-center"><?= statusBadge($r['grade']) ?></td>
                            <td class="small"><?= e($r['remarks'] ?: '—') ?></td>
                            <td class="small"><?= e($r['teacher_comments'] ?: '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
