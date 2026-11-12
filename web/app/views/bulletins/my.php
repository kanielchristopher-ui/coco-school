<?php $pageTitle = 'My Report Cards'; ?>
<?php if (!$list): ?>
    <div class="card"><div class="card-body text-center py-5">
        <span class="stat-icon tone-info mx-auto mb-3"><i class="fas fa-file-lines"></i></span>
        <h5>No report cards have been published yet.</h5><p class="text-muted mb-0">Published report cards will appear here automatically.</p>
    </div></div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($list as $r): ?>
        <div class="col-md-6 col-xl-4"><a href="<?= url('bulletins/show/' . (int) $r['id']) ?>" class="card stat-card h-100"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="stat-icon tone-primary"><i class="fas fa-file-lines"></i></span>
                <span class="badge bg-success-subtle text-success"><?= e(formatDate($r['published_at'])) ?></span>
            </div>
            <h6 class="fw-bold mb-0"><?= e($r['period_label']) ?></h6>
            <div class="text-muted small mb-2"><?= e(studentName($r)) ?> · <?= e($r['class_name']) ?> · <?= e($r['session_name']) ?></div>
            <?php if ($r['percent'] !== null): ?>
                <div class="progress mb-1" style="height:8px"><div class="progress-bar <?= $r['percent'] >= 50 ? 'bg-success' : 'bg-danger' ?>" style="width:<?= min(100, (float) $r['percent']) ?>%"></div></div>
                <small><strong><?= nf($r['percent'], 1) ?> %</strong> · <?= (int) $r['rank'] ?>/<?= (int) $r['class_size'] ?></small>
            <?php endif; ?>
        </div></a></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
