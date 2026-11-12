<?php
$pageTitle = 'Report Card';
$b = $bulletin;
$stLabel = ['draft' => ['Draft', 'secondary'], 'validated' => ['Validated', 'info'], 'published' => ['Published', 'success'], 'superseded' => ['Superseded', 'secondary']];
[$lab, $tone] = $stLabel[$b['status']] ?? ['—', 'secondary'];
?>
<style><?= $rendered['css'] ?></style>
<div class="d-flex flex-wrap gap-2 align-items-center mb-3 no-print">
    <span class="badge bg-<?= $tone ?>-subtle text-<?= $tone ?> fs-6"><?= e(t($lab)) ?> · v<?= (int) $b['version'] ?></span>
    <div class="ms-auto d-flex flex-wrap gap-2">
        <a href="<?= url('bulletins/print-view/' . (int) $b['id']) ?>" target="_blank" class="btn btn-outline-primary"><i class="fas fa-print me-1"></i>Print / Save PDF</a>
        <?php if ($canValidate && $b['status'] === 'draft'): ?>
            <form method="POST" action="<?= url('bulletins/validate-bulletin') ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $b['id'] ?>"><input type="hidden" name="back" value="bulletins/show/<?= (int) $b['id'] ?>"><button class="btn btn-info text-white"><i class="fas fa-check me-1"></i>Validate</button></form>
        <?php endif; ?>
        <?php if ($canPublish && $b['status'] === 'validated'): ?>
            <form method="POST" action="<?= url('bulletins/publish') ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $b['id'] ?>"><input type="hidden" name="back" value="bulletins/show/<?= (int) $b['id'] ?>"><button class="btn btn-success"><i class="fas fa-paper-plane me-1"></i>Publish</button></form>
        <?php endif; ?>
        <?php if ($isTeacher && $b['status'] === 'published'): ?>
            <button class="btn btn-outline-warning" data-bs-toggle="collapse" data-bs-target="#regen"><i class="fas fa-rotate me-1"></i>Request a correction</button>
        <?php endif; ?>
    </div>
</div>
<?php if ($isTeacher && $b['status'] === 'published'): ?>
<div class="collapse mb-3 no-print" id="regen"><div class="card card-body">
    <form method="POST" action="<?= url('bulletins/request-regeneration') ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
        <label class="form-label">Reason</label><textarea name="reason" class="form-control mb-2" rows="2" required></textarea>
        <button class="btn btn-warning">Send request</button></form>
</div></div>
<?php endif; ?>

<div class="mb-4"><?= $rendered['html'] ?></div>

<?php if ($audit): ?>
<div class="card no-print"><div class="card-header"><h6 class="fw-bold mb-0">Audit trail</h6></div><div class="card-body">
    <?php foreach ($audit as $a): ?>
        <div class="list-row"><span class="stat-icon tone-primary" style="width:36px;height:36px;font-size:.85rem"><i class="fas fa-clock-rotate-left"></i></span>
            <div><strong><?= e(t(ucfirst(str_replace('_', ' ', $a['action'])))) ?></strong> <span class="text-muted small">· <?= e(trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''))) ?> · <?= e(formatDate($a['created_at'], 'M d, Y H:i')) ?></span>
            <?php if (!empty($a['details_json'])): ?><div class="small text-muted"><?= e(implode(' · ', array_map(fn($k, $v) => $k . ': ' . (is_scalar($v) ? $v : json_encode($v)), array_keys(json_decode($a['details_json'], true) ?: []), array_values(json_decode($a['details_json'], true) ?: [])))) ?></div><?php endif; ?></div></div>
    <?php endforeach; ?>
</div></div>
<?php endif; ?>
