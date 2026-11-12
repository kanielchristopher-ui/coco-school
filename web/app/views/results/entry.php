<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Marks Entry - ' . ($exam['name'] ?? 'Exam');
$genderLabel = static fn($g) => match (strtolower((string) $g)) {
    'male'   => 'Male',
    'female' => 'Female',
    'other'  => 'Other',
    default  => '—',
};
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h5 class="mb-0"><?= e($exam['name']) ?></h5>
        <span class="text-muted small"><?= count($students) ?> student(s) &bull; <?= count($subjects) ?> subject(s) &bull; Date: <?= e(formatDate($exam['start_date'])) ?></span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('results/rank/' . $exam['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-trophy me-1"></i>Rankings</a>
        <a href="<?= url('exams/index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Exams</a>
    </div>
</div>

<?php
    // Per-subject publish status (from any saved result row).
    $subjectStatus = [];
    foreach ($existing as $studentRows) {
        foreach ($studentRows as $subId => $row) {
            if (!isset($subjectStatus[$subId])) {
                $subjectStatus[$subId] = $row['status'] ?? 'draft';
            }
        }
    }
?>
<?php if (empty($students)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center text-muted py-5">
            <i class="fas fa-users fa-3x mb-3 d-block"></i>
            No active students in this class.
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <h6 class="fw-bold mb-2"><i class="fas fa-clipboard-check me-2 text-primary"></i>Publish Status by Subject</h6>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($subjects as $sub): ?>
                    <?php $st = $subjectStatus[$sub['id']] ?? 'draft'; ?>
                    <a href="<?= url('results/review/' . (int) $exam['id'] . '/' . (int) $sub['id']) ?>" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2">
                        <?= e($sub['name']) ?>
                        <?= statusBadge($st) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <form method="POST" action="<?= url('results/store/' . $exam['id']) ?>">
        <?= csrf_field() ?>

        <?php foreach ($students as $st): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <span class="badge bg-primary rounded-pill px-3 py-2">ID: <?= e($st['official_id'] ?? '—') ?></span>
                        <div>
                            <div class="fw-semibold fs-6"><?= e(studentName($st)) ?></div>
                            <div class="small text-muted">Gender: <?= e($genderLabel($st['gender'] ?? null)) ?></div>
                        </div>
                        <div class="ms-md-auto small text-muted">
                            <i class="far fa-calendar me-1"></i>Date: <?= e(formatDate($exam['start_date'])) ?>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width:180px;">Subject</th>
                                <th class="text-center">Full Mark</th>
                                <th class="text-center" style="min-width:170px;">Marks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subjects as $sub): ?>
                                <?php $row = $existing[$st['id']][$sub['id']] ?? null; ?>
                                <tr>
                                    <td><?= e($sub['name']) ?></td>
                                    <td class="text-center text-muted"><?= (int) ($row['max_marks'] ?? 100) ?></td>
                                    <td class="text-center">
                                        <input type="number" name="marks[<?= (int) $st['id'] ?>][<?= (int) $sub['id'] ?>]"
                                               class="form-control form-control-sm text-center"
                                               step="0.01" min="0" max="100" placeholder="—"
                                               value="<?= e($row['marks_obtained'] ?? '') ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="position-sticky bottom-0 py-3" style="z-index:5;">
            <div class="card border-0 shadow">
                <div class="card-body py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <span class="text-muted small">Enter marks as numbers. Leave blank to skip a subject.</span>
                    <button class="btn btn-primary px-4"><i class="fas fa-save me-1"></i>Save All Marks</button>
                </div>
            </div>
        </div>
    </form>
<?php endif; ?>
