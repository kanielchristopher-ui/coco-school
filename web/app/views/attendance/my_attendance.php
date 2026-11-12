<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'My Attendance';
$summary = ['present' => 0, 'late' => 0, 'absent' => 0, 'leave' => 0];
foreach ($rows as $r) { $summary[$r['status']] = ($summary[$r['status']] ?? 0) + 1; }
$totalRecords = array_sum($summary);
$presentCount = ($summary['present'] ?? 0) + ($summary['late'] ?? 0);
$attendanceRate = $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100) : 0;
?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body bg-soft-indigo rounded p-4">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="stat-icon bg-primary-subtle text-primary"><i class="fas fa-calendar-check"></i></div>
            <div>
                <h5 class="mb-0 fw-bold">My Attendance</h5>
                <small class="text-body d-block"><i class="fas fa-user me-1"></i><?= e(studentName($student)) ?> &bull; <?= e($student['admission_no'] ?? '') ?></small>
            </div>
        </div>
    </div>
</div>

<?php if (empty($rows)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-clipboard-check fa-3x mb-3 d-block text-primary"></i>
            <h6 class="fw-semibold">No attendance records found for you yet.</h6>
            <p class="text-muted mb-0">Your attendance will appear here once your teachers mark it.</p>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success-subtle text-success"><i class="fas fa-check"></i></div>
                    <div>
                        <div class="fs-4 fw-bold"><?= $summary['present'] ?? 0 ?></div>
                        <div class="text-muted small">Present</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning-subtle text-warning"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="fs-4 fw-bold"><?= $summary['late'] ?? 0 ?></div>
                        <div class="text-muted small">Late</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-danger-subtle text-danger"><i class="fas fa-times"></i></div>
                    <div>
                        <div class="fs-4 fw-bold"><?= $summary['absent'] ?? 0 ?></div>
                        <div class="text-muted small">Absent</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info-subtle text-info"><i class="fas fa-plane"></i></div>
                    <div>
                        <div class="fs-4 fw-bold"><?= $summary['leave'] ?? 0 ?></div>
                        <div class="text-muted small">Leave</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary bg-gradient text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="mb-0 fw-semibold text-white"><i class="fas fa-clipboard-list me-2"></i>Attendance Records</h6>
            <span class="badge bg-light text-primary"><?= $totalRecords ?> <?= $totalRecords === 1 ? 'record' : 'records' ?></span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-primary">
                    <tr><th>Date</th><th>Class</th><th>Subject</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td class="small"><?= formatDate($r['date'] ?? null) ?></td>
                            <td>
                                <?= e($r['class_name'] ?: '—') ?>
                                <?php if (!empty($r['section_name'])): ?>
                                    <small class="text-muted d-block"><?= e($r['section_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= e($r['subject_name'] ?? '—') ?></td>
                            <td><?= statusBadge($r['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer card-footer-soft small text-body">
            <i class="fas fa-percentage me-1 text-primary"></i>Your overall attendance rate is <strong><?= $attendanceRate ?>%</strong> (present + late counted as attended).
        </div>
    </div>
<?php endif; ?>
