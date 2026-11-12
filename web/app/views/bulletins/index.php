<?php
$pageTitle = 'Report Cards';
$stLabel = ['draft' => ['Draft', 'secondary'], 'validated' => ['Validated', 'info'], 'published' => ['Published', 'success'], 'superseded' => ['Superseded', 'light']];
?>
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('bulletins/index') ?>" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Class</label>
                <select name="class_id" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= $class && (int) $class['id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Period</label>
                <select name="period" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($periods as $pid => $plabel): ?>
                        <option value="<?= e($pid) ?>" <?= $pid === $periodId ? 'selected' : '' ?>><?= e($plabel) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?= url('bulletins/marks') ?>" class="btn btn-outline-primary"><i class="fas fa-pen-to-square me-1"></i>Marks Entry</a>
                <?php if ($canManage): ?><a href="<?= url('bulletins/scheme?level=' . $level) ?>" class="btn btn-outline-secondary"><i class="fas fa-sliders me-1"></i>Evaluation Scheme</a><?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if (!$scheme): ?>
    <div class="card"><div class="card-body text-center py-5">
        <span class="stat-icon tone-warning mx-auto mb-3"><i class="fas fa-triangle-exclamation"></i></span>
        <h5>No evaluation scheme for this level and academic year.</h5>
        <?php if ($canManage): ?><a href="<?= url('bulletins/scheme?level=' . $level) ?>" class="btn btn-primary mt-2">Install the RDC scheme</a><?php endif; ?>
    </div></div>
<?php elseif (!$class): ?>
    <div class="card"><div class="card-body text-muted">No class available for your role.</div></div>
<?php else: ?>
<div class="card">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <h6 class="fw-bold mb-0"><?= e($class['name']) ?> — <?= e($periods[$periodId] ?? '') ?></h6>
        <div class="d-flex flex-wrap gap-2">
            <form method="POST" action="<?= url('bulletins/generate') ?>" class="d-flex gap-2">
                <?= csrf_field() ?>
                <input type="hidden" name="class_id" value="<?= (int) $class['id'] ?>"><input type="hidden" name="period" value="<?= e($periodId) ?>">
                <select name="template" class="form-select form-select-sm" style="width:auto">
                    <?php foreach ($templates as $tid => $tpl): ?><option value="<?= e($tid) ?>" <?= $tid === $template ? 'selected' : '' ?>><?= e($tpl['label']) ?></option><?php endforeach; ?>
                </select>
                <button class="btn btn-sm btn-primary"><i class="fas fa-wand-magic-sparkles me-1"></i>Generate for the class</button>
            </form>
            <form method="POST" action="<?= url('bulletins/validate-bulletin') ?>" id="bulkValidate">
                <?= csrf_field() ?><input type="hidden" name="back" value="<?= e('bulletins/index?class_id=' . $class['id'] . '&period=' . $periodId) ?>">
                <?php foreach ($rows as $r): if ($r['bulletin'] && $r['bulletin']['status'] === 'draft'): ?><input type="hidden" name="ids[]" value="<?= (int) $r['bulletin']['id'] ?>"><?php endif; endforeach; ?>
                <button class="btn btn-sm btn-outline-info"><i class="fas fa-check me-1"></i>Validate all drafts</button>
            </form>
            <?php if ($canPublish): ?>
            <form method="POST" action="<?= url('bulletins/publish') ?>" onsubmit="return confirm('Publish all validated report cards of this class?')">
                <?= csrf_field() ?><input type="hidden" name="back" value="<?= e('bulletins/index?class_id=' . $class['id'] . '&period=' . $periodId) ?>">
                <?php foreach ($rows as $r): if ($r['bulletin'] && $r['bulletin']['status'] === 'validated'): ?><input type="hidden" name="ids[]" value="<?= (int) $r['bulletin']['id'] ?>"><?php endif; endforeach; ?>
                <button class="btn btn-sm btn-success"><i class="fas fa-paper-plane me-1"></i>Publish all validated</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Student</th><th>Marks</th><th>%</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php if (!$rows): ?><tr><td colspan="5" class="text-center text-muted py-4">No active students in this class.</td></tr><?php endif; ?>
            <?php foreach ($rows as $r): $b = $r['bulletin']; $s = $r['student']; ?>
                <tr>
                    <td><strong><?= e(studentName($s)) ?></strong><div class="small text-muted"><?= e($s['perm_number'] ?? '') ?></div></td>
                    <td>
                        <?php if ($r['complete']): ?><span class="badge bg-success-subtle text-success">Complete</span>
                        <?php elseif ($r['has_marks']): ?><span class="badge bg-warning-subtle text-warning">Incomplete</span>
                        <?php else: ?><span class="badge bg-secondary-subtle text-secondary">No marks</span><?php endif; ?>
                    </td>
                    <td style="min-width:130px">
                        <div class="progress" style="height:8px"><div class="progress-bar <?= $r['percent'] >= 50 ? 'bg-success' : 'bg-danger' ?>" style="width:<?= min(100, (float) $r['percent']) ?>%"></div></div>
                        <small class="text-muted"><?= nf($r['percent'], 1) ?> %</small>
                    </td>
                    <td>
                        <?php if ($b): [$lab, $tone] = $stLabel[$b['status']] ?? ['—', 'secondary']; ?>
                            <span class="badge bg-<?= $tone ?>-subtle text-<?= $tone === 'light' ? 'muted' : $tone ?>"><?= e($lab) ?></span> <small class="text-muted">v<?= (int) $b['version'] ?></small>
                        <?php else: ?><span class="text-muted small">Not generated</span><?php endif; ?>
                    </td>
                    <td class="text-end text-nowrap">
                        <form method="POST" action="<?= url('bulletins/generate') ?>" class="d-inline">
                            <?= csrf_field() ?><input type="hidden" name="class_id" value="<?= (int) $class['id'] ?>"><input type="hidden" name="period" value="<?= e($periodId) ?>">
                            <input type="hidden" name="student_id" value="<?= (int) $s['id'] ?>"><input type="hidden" name="template" value="<?= e($template) ?>">
                            <button class="btn btn-sm btn-outline-primary" title="<?= e(t($b && $b['status'] !== 'draft' ? 'New version' : 'Generate')) ?>"><i class="fas fa-rotate"></i></button>
                        </form>
                        <?php if ($b): ?>
                            <a href="<?= url('bulletins/show/' . (int) $b['id']) ?>" class="btn btn-sm btn-outline-secondary" title="<?= e(t('View')) ?>"><i class="fas fa-eye"></i></a>
                            <a href="<?= url('bulletins/print-view/' . (int) $b['id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="<?= e(t('Print')) ?>"><i class="fas fa-print"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
