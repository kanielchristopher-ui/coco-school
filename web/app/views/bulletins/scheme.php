<?php $pageTitle = 'Evaluation Scheme'; ?>
<div class="d-flex flex-wrap gap-2 mb-4">
    <?php foreach ($levels as $l): ?>
        <a href="<?= url('bulletins/scheme?level=' . $l) ?>" class="btn <?= $l === $level ? 'btn-primary' : 'btn-outline-primary' ?>"><?= e(t(BulletinEngine::levelLabel($l))) ?></a>
    <?php endforeach; ?>
    <a href="<?= url('bulletins/index') ?>" class="btn btn-outline-secondary ms-auto"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<?php if (!$scheme): ?>
    <div class="card"><div class="card-body text-center py-5">
        <span class="stat-icon tone-primary mx-auto mb-3"><i class="fas fa-sliders"></i></span>
        <h5>No evaluation scheme for this level and academic year.</h5>
        <p class="text-muted">The RDC model: nursery/primary = 3 terms (2 periods + exam), secondary = 2 semesters (2 periods + exam).</p>
        <form method="POST" action="<?= url('bulletins/install-scheme') ?>"><?= csrf_field() ?><input type="hidden" name="level" value="<?= e($level) ?>">
            <button class="btn btn-primary">Install the RDC scheme</button></form>
    </div></div>
<?php else: $cfg = $scheme['config']; ?>
<form method="POST" action="<?= url('bulletins/save-scheme') ?>">
    <?= csrf_field() ?><input type="hidden" name="level" value="<?= e($level) ?>">
    <div class="alert alert-info">Each change is saved as a <strong>new version</strong> (current: v<?= (int) $scheme['version'] ?>). Report cards already generated keep their own version. Totals use the maximum and the weight of each step — no equal-period assumption.</div>
    <?php foreach ($cfg['terms'] as $term): ?>
        <div class="card mb-3">
            <div class="card-header"><h6 class="fw-bold mb-0"><?= e($term['label']) ?></h6></div>
            <div class="table-responsive"><table class="table align-middle">
                <thead><tr><th>Step</th><th>Label</th><th style="width:130px">Max points</th><th style="width:110px">Weight</th></tr></thead>
                <tbody>
                <?php foreach ($term['steps'] as $st): $input = in_array($st['type'], ['period', 'exam'], true); ?>
                    <tr>
                        <td><span class="badge bg-<?= $st['type'] === 'exam' ? 'warning' : ($st['type'] === 'total' ? 'primary' : 'info') ?>-subtle text-<?= $st['type'] === 'exam' ? 'warning' : ($st['type'] === 'total' ? 'primary' : 'info') ?>"><?= e(t(ucfirst($st['type']))) ?></span></td>
                        <td><input type="text" name="label[<?= e($st['id']) ?>]" class="form-control form-control-sm" value="<?= e($st['label']) ?>"></td>
                        <td><?php if ($input): ?><input type="text" inputmode="decimal" name="max[<?= e($st['id']) ?>]" class="form-control form-control-sm" value="<?= e((string) ($st['max_points'] ?? 10)) ?>"><?php else: ?><span class="text-muted small">Σ</span><?php endif; ?></td>
                        <td><?php if ($input): ?><input type="text" inputmode="decimal" name="weight[<?= e($st['id']) ?>]" class="form-control form-control-sm" value="<?= e((string) ($st['weight'] ?? 1)) ?>"><?php else: ?><span class="text-muted small">Σ</span><?php endif; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
    <?php endforeach; ?>
    <div class="card mb-3"><div class="card-body row g-3 align-items-end">
        <div class="col-md-6">
            <label class="form-label">Report card template</label>
            <select name="template" class="form-select">
                <?php foreach ($templates as $tid => $tpl): ?><option value="<?= e($tid) ?>" <?= $tid === $template ? 'selected' : '' ?>><?= e($tpl['label']) ?></option><?php endforeach; ?>
            </select>
            <div class="form-text"><a href="<?= url('bulletins/extensions') ?>">Install more templates (.ccplg)</a></div>
        </div>
        <div class="col-md-6 text-md-end"><button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save as new version</button></div>
    </div></div>
</form>
<div class="card"><div class="card-header"><h6 class="fw-bold mb-0">Version history</h6></div><div class="card-body">
    <?php foreach ($versions as $v): ?><span class="badge bg-secondary-subtle text-secondary me-2">v<?= (int) $v['version'] ?> · <?= e(formatDate($v['created_at'])) ?></span><?php endforeach; ?>
</div></div>
<?php endif; ?>
