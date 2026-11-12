<?php $pageTitle = 'Report Card Marks'; ?>
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('bulletins/marks') ?>" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Select Subject</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= (int) $s['id'] ?>" <?= $subject && (int) $subject['id'] === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['class_name']) ?> — <?= e($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($subject && $scheme): ?>
            <div class="col-md-5">
                <label class="form-label">Period / Exam</label>
                <select name="step_id" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($steps as $sid => $st): ?>
                        <option value="<?= e($sid) ?>" <?= $sid === $stepId ? 'selected' : '' ?>><?= e($st['term_label']) ?> — <?= e($st['label']) ?> (max <?= nf($st['max'], 1) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if (!$subjects): ?>
    <div class="card"><div class="card-body text-muted">No subjects assigned to you yet.</div></div>
<?php elseif ($subject && !$scheme): ?>
    <div class="alert alert-warning">No evaluation scheme for this level and academic year. Ask the school management to install it.</div>
<?php elseif ($subject && $stepId !== ''): $max = $steps[$stepId]['max']; ?>
<form method="POST" action="<?= url('bulletins/save-marks') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="subject_id" value="<?= (int) $subject['id'] ?>"><input type="hidden" name="step_id" value="<?= e($stepId) ?>">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><?= e($subject['class_name']) ?> — <?= e($subject['name']) ?> — <?= e($steps[$stepId]['label']) ?></h6>
            <span class="badge bg-primary-subtle text-primary">Maximum: <?= nf($max, 1) ?></span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>#</th><th>Student</th><th style="width:170px">Points / <?= nf($max, 1) ?></th></tr></thead>
                <tbody>
                <?php foreach ($students as $i => $r): $st = $r['student']; ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= e(studentName($st)) ?></strong><div class="small text-muted"><?= e($st['perm_number'] ?? '') ?></div></td>
                        <td>
                            <?php if ($r['locked']): ?>
                                <span class="badge bg-secondary-subtle text-secondary"><i class="fas fa-lock me-1"></i><?= $r['points'] !== null ? e(nf($r['points'], 2)) : '—' ?></span>
                            <?php else: ?>
                                <input type="text" inputmode="decimal" name="points[<?= (int) $st['id'] ?>]" class="form-control" value="<?= $r['points'] !== null ? e(rtrim(rtrim(number_format((float) $r['points'], 2, '.', ''), '0'), '.')) : '' ?>" placeholder="—">
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer text-end"><button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Marks</button></div>
    </div>
</form>
<?php else: ?>
    <div class="card"><div class="card-body text-muted">Select a class, subject and exam to enter marks.</div></div>
<?php endif; ?>
