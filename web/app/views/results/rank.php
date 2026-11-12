<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Rankings - ' . ($exam['name'] ?? 'Exam');
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <a href="<?= url('exams/index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Exams</a>
    <span class="text-muted small"><?= count($ranks) ?> ranked student(s)</span>
</div>

<?php if (empty($ranks)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center text-muted py-5">
            <i class="fas fa-trophy fa-3x mb-3 d-block"></i>
            No marks entered for this exam yet.
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Rank</th><th>Student</th><th>Roll</th><th>Total</th><th>Max</th><th>Percentage</th><th>Grade</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($ranks as $i => $r): ?>
                        <?php $grade = gradeFor((float) $r['percentage']); ?>
                        <tr>
                            <td>
                                <?php if ($i === 0): ?>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-crown"></i> <?= $i + 1 ?></span>
                                <?php elseif ($i < 3): ?>
                                    <span class="badge bg-secondary"><i class="fas fa-medal"></i> <?= $i + 1 ?></span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark"><?= $i + 1 ?></span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= e(trim($r['first_name'] . ' ' . $r['last_name'])) ?></strong></td>
                            <td><?= e($r['roll_no'] ?: '—') ?></td>
                            <td><?= e($r['total']) ?></td>
                            <td><?= e($r['max_total']) ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px;width:80px;">
                                        <div class="progress-bar" style="width: <?= min(100, (float) $r['percentage']) ?>%"></div>
                                    </div>
                                    <span class="fw-bold"><?= e($r['percentage']) ?>%</span>
                                </div>
                            </td>
                            <td><?= statusBadge($grade['grade']) ?></td>
                            <td class="text-end">
                                <a href="<?= url('results/report-card/' . $exam['id'] . '/' . $r['student_id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-alt me-1"></i>Report Card</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
