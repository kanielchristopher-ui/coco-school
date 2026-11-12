<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Review & Publish Results';
$status = $status ?? '';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h5 class="mb-0">Review & Publish Results</h5>
        <span class="text-muted small">Results submitted by teachers appear here for review and publishing.</span>
    </div>
    <form method="GET" class="d-flex gap-2">
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <?php foreach (['submitted' => 'Submitted (needs review)', 'approved' => 'Approved (ready to publish)', 'returned' => 'Returned for correction', 'draft' => 'Draft (not submitted)', 'published' => 'Published', 'locked' => 'Locked'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= $status === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <a href="<?= url('results/teacher-entry') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit me-1"></i>Marks Entry</a>
    </form>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><h6 class="fw-bold mb-0"><i class="fas fa-clipboard-check me-2 text-primary"></i>Result Review Queue (<?= count($rows) ?>)</h6></div>
    <?php if (empty($rows)): ?>
        <div class="card-body text-center text-muted py-5">
            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
            No results have been entered yet.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Exam</th><th>Type</th><th>Subject</th><th>Class</th><th class="text-center">Students</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($row['exam_name']) ?></td>
                            <td class="small text-muted"><?= e($row['type_name'] ?: '—') ?></td>
                            <td><?= e($row['subject_name'] ?: '—') ?></td>
                            <td class="small"><?= e($row['class_name'] ?: '—') ?></td>
                            <td class="text-center"><?= (int) $row['students'] ?></td>
                            <td>
                                <?= statusBadge($row['status']) ?>
                                <?php if ($row['status'] === 'published' && $row['published_at']): ?>
                                    <div class="small text-muted"><?= e(formatDate($row['published_at'], 'M d, Y H:i')) ?></div>
                                <?php endif; ?>
                                <?php if ($row['status'] === 'returned' && !empty($row['correction_comment'])): ?>
                                    <div class="small text-warning mt-1"><i class="fas fa-comment me-1"></i><?= e($row['correction_comment']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="<?= url('results/review/' . (int) $row['exam_id'] . '/' . (int) $row['subject_id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-clipboard-check me-1"></i>Review</a>
                                <a href="<?= url('results/teacher-entry?class_id=' . (int) ($row['class_id'] ?? 0) . '&subject_id=' . (int) $row['subject_id'] . '&exam_id=' . (int) $row['exam_id']) ?>" class="btn btn-sm btn-outline-secondary" title="Marks Entry"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
