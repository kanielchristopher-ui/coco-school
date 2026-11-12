<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'My Exams';
$studentId = $student['id'] ?? 0;
?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body bg-soft-indigo rounded p-4">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="stat-icon bg-primary-subtle text-primary"><i class="fas fa-file-signature"></i></div>
            <div>
                <h5 class="mb-0 fw-bold">My Exams</h5>
                <small class="text-body d-block"><i class="fas fa-user me-1"></i><?= e(studentName($student)) ?> &bull; <?= e($student['admission_no'] ?? '') ?></small>
            </div>
        </div>
    </div>
</div>

<?php if (empty($rows)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-file-signature fa-3x mb-3 d-block text-primary"></i>
            <h6 class="fw-semibold">No exams have been published for your class yet.</h6>
            <p class="text-muted mb-0">Upcoming and published exams for your class will appear here.</p>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($rows as $ex): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary bg-gradient text-white d-flex align-items-center gap-2">
                        <div class="stat-icon bg-white text-primary"><i class="fas fa-file-signature"></i></div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-semibold text-white"><?= e($ex['subject_name'] ?: $ex['name']) ?></h6>
                            <small class="text-white"><?= e($ex['type_name'] ?: 'Examination') ?></small>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small mb-0">
                            <li class="d-flex align-items-center gap-2 mb-2">
                                <i class="fas fa-school text-primary"></i>
                                <span class="text-body fw-semibold"><?= e($ex['class_name'] ?: '—') ?></span>
                                <?php if (!empty($ex['section_name'])): ?>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= e($ex['section_name']) ?></span>
                                <?php endif; ?>
                            </li>
                            <li class="d-flex align-items-center gap-2 mb-2">
                                <i class="fas fa-calendar-day text-primary"></i>
                                <span class="text-body"><?= formatDate($ex['start_date']) ?></span>
                            </li>
                            <li class="d-flex align-items-center gap-2 mb-2">
                                <i class="fas fa-calendar-alt text-primary"></i>
                                <span class="text-body"><?= e($ex['session_name'] ?: '—') ?></span>
                            </li>
                            <li class="d-flex align-items-center gap-2">
                                <i class="fas fa-tag text-primary"></i>
                                <span class="text-body"><?= e($ex['type_name'] ?: '—') ?></span>
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer card-footer-soft d-flex justify-content-between align-items-center">
                        <span class="small text-body">Status</span>
                        <?= statusBadge($ex['status']) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
