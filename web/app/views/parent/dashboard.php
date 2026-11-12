<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Parent Dashboard';
?>
<?php if (!empty($parent['official_id'])): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex align-items-center gap-3">
            <i class="fas fa-id-badge fa-2x text-primary"></i>
            <div>
                <div class="text-muted small">Parent Official ID</div>
                <div class="fw-bold fs-5"><?= e($parent['official_id']) ?></div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if (empty($children)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center text-muted py-5">
            <i class="fas fa-child fa-3x mb-3 d-block"></i>
            No children are linked to your account yet. Please contact the school office.
        </div>
    </div>
<?php endif; ?>

<?php foreach ($children as $child): ?>
    <?php $s = $child['student']; ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex flex-wrap align-items-center gap-3">
            <?= avatar($s['photo'] ?? null, $s['first_name'] ?? 'S', 40) ?>
            <div>
                <h6 class="mb-0"><?= e(studentName($s)) ?></h6>
                <span class="text-muted small"><?= e($s['official_id'] ?? '—') ?><?= !empty($s['admission_no']) ? ' &bull; ' . e($s['admission_no']) : '' ?><?= !empty($s['class_name']) ? ' &bull; ' . e($s['class_name']) : '' ?></span>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Attendance -->
                <div class="col-md-4">
                    <h6 class="text-muted small text-uppercase mb-2"><i class="fas fa-calendar-check me-1"></i>Attendance</h6>
                    <div class="d-flex gap-2 flex-wrap mb-2">
                        <?php
                            $totalAtt = 0;
                            $attMap = [];
                            foreach ($child['attendance'] as $a) { $attMap[$a['status']] = (int) $a['total']; $totalAtt += (int) $a['total']; }
                            $badges = ['present' => 'success', 'late' => 'warning', 'absent' => 'danger', 'leave' => 'info'];
                        ?>
                        <?php foreach ($badges as $st => $color): ?>
                            <span class="badge bg-<?= $color ?>-subtle text-<?= $color ?>"><?= ucfirst($st) ?>: <?= $attMap[$st] ?? 0 ?></span>
                        <?php endforeach; ?>
                        <?php if ($totalAtt === 0): ?>
                            <span class="text-muted small">No attendance records.</span>
                        <?php endif; ?>
                    </div>
                    <div class="small text-muted">Total records: <?= $totalAtt ?></div>
                </div>

                <!-- Results -->
                <div class="col-md-4">
                    <h6 class="text-muted small text-uppercase mb-2"><i class="fas fa-file-alt me-1"></i>Results</h6>
                    <?php if (empty($child['results'])): ?>
                        <span class="text-muted small">No exam results yet.</span>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-1">
                            <?php foreach ($child['results'] as $r): ?>
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <span class="badge bg-primary-subtle text-primary" title="<?= e($r['type_name'] ?? '') ?><?= $r['term'] ? ' - ' . e($r['term']) : '' ?>">
                                        <?= e($r['exam_name']) ?><?= $r['term'] ? ' (' . e($r['term']) . ')' : '' ?>: <?= e($r['percentage']) ?>%
                                    </span>
                                    <span class="d-flex gap-1">
                                        <a href="<?= url('results/view/' . (int) $r['exam_id'] . '/' . (int) $s['id']) ?>" class="btn btn-sm btn-outline-info" title="View Result"><i class="fas fa-eye"></i></a>
                                        <a href="<?= url('results/report-card/' . (int) $r['exam_id'] . '/' . (int) $s['id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Report Card"><i class="fas fa-file-pdf"></i></a>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Fees -->
                <div class="col-md-4">
                    <h6 class="text-muted small text-uppercase mb-2"><i class="fas fa-money-bill me-1"></i>Fees</h6>
                    <?php if (empty($child['fees'])): ?>
                        <span class="text-muted small">No fee invoices.</span>
                    <?php else: ?>
                        <div class="small">
                            <?php foreach (array_slice($child['fees'], 0, 3) as $f): ?>
                                <div class="d-flex justify-content-between mb-1">
                                    <span><?= e($f['fee_type_name'] ?: $f['invoice_no']) ?></span>
                                    <span><?= currency($f['amount']) ?> <?= statusBadge($f['status']) ?></span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($child['fees']) > 3): ?>
                                <span class="text-muted">+<?= count($child['fees']) - 3 ?> more</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php if (!empty($notifications)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-bell me-2"></i>Notifications</h6>
            <form method="POST" action="<?= url('parent/read-notifications') ?>" class="d-inline">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-check-double me-1"></i>Mark all read</button>
            </form>
        </div>
        <div class="list-group list-group-flush">
            <?php foreach ($notifications as $n): ?>
                <div class="list-group-item d-flex align-items-center gap-3">
                    <div class="icon-circle bg-primary-subtle text-primary"><i class="fas fa-bell"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold"><?= e($n['title'] ?? '') ?></div>
                        <?php if (!empty($n['message'])): ?><div class="text-muted small"><?= e($n['message']) ?></div><?php endif; ?>
                    </div>
                    <span class="text-muted small text-nowrap"><?= formatDate($n['created_at']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
