<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'My Results';
$studentId = $student['id'] ?? 0;
?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body bg-soft-indigo rounded p-4">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="stat-icon bg-primary-subtle text-primary"><i class="fas fa-file-alt"></i></div>
            <div>
                <h5 class="mb-0 fw-bold">My Results</h5>
                <small class="text-body d-block"><i class="fas fa-user me-1"></i><?= e(studentName($student)) ?> &bull; <?= e($student['admission_no'] ?? '') ?></small>
            </div>
        </div>
    </div>
</div>

<?php if (empty($exams)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-file-alt fa-3x mb-3 d-block text-primary"></i>
            <h6 class="fw-semibold">No exam results published for you yet.</h6>
            <p class="text-muted mb-0">Published results will appear here automatically.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary bg-gradient text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="mb-0 fw-semibold text-white"><i class="fas fa-trophy me-2"></i>Published Results</h6>
            <span class="badge bg-light text-primary"><?= count($exams) ?> <?= count($exams) === 1 ? 'exam' : 'exams' ?></span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-primary">
                    <tr><th>Exam</th><th>Type</th><th>Date</th><th>Total</th><th>Max</th><th>Percentage</th><th>Grade</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($exams as $e): ?>
                        <?php $g = gradeFor((float) $e['percentage']); ?>
                        <tr>
                            <td><strong><?= e($e['name']) ?></strong></td>
                            <td><span class="badge bg-secondary-subtle text-secondary"><?= e($e['type_name'] ?: '—') ?></span></td>
                            <td class="small"><?= formatDate($e['start_date'] ?? null) ?></td>
                            <td><strong><?= e($e['total']) ?></strong></td>
                            <td><?= e($e['max_total']) ?></td>
                            <td>
                                <span class="badge <?= (float) $e['percentage'] >= 60 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>"><?= e($e['percentage']) ?>%</span>
                            </td>
                            <td><span class="badge bg-primary-subtle text-primary"><?= e($g['grade']) ?></span></td>
                            <td class="text-end text-nowrap">
                                <a href="<?= url('results/view/' . $e['id'] . '/' . $studentId) ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye me-1"></i>View Result</a>
                                <a href="<?= url('results/report-card/' . $e['id'] . '/' . $studentId) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-pdf me-1"></i>Report Card</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer card-footer-soft small text-body">
            <i class="fas fa-info-circle me-1 text-primary"></i>Percentage shown is your overall score across all subjects. Use <strong>View Result</strong> for subject-wise marks and <strong>Report Card</strong> for a printable copy.
        </div>
    </div>
<?php endif; ?>
