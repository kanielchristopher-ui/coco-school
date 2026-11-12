<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Student Dashboard';
$attendanceMap = [];
foreach ($attendance as $a) { $attendanceMap[$a['status']] = (int) $a['total']; }
$present = ($attendanceMap['present'] ?? 0) + ($attendanceMap['late'] ?? 0);
$attendanceTotal = array_sum($attendanceMap);
?>
<div class="row g-3 mb-4">

    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="fas fa-school"></i></div>
                <div>
                    <div class="fs-4 fw-bold"><?= e($class['name'] ?? '—') ?></div>
                    <div class="text-muted small">My Class</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success"><i class="fas fa-book-open"></i></div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format(count($subjects)) ?></div>
                    <div class="text-muted small">My Subjects</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info-subtle text-info"><i class="fas fa-file-alt"></i></div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format(count($results)) ?></div>
                    <div class="text-muted small">Results</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning-subtle text-warning"><i class="fas fa-clipboard-check"></i></div>
                <div>
                    <div class="fs-4 fw-bold"><?= $attendanceTotal > 0 ? round(($present / $attendanceTotal) * 100) : 0 ?>%</div>
                    <div class="text-muted small">Attendance</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body">
                <div class="mb-3"><?= avatar($student['photo'], studentName($student), 88) ?></div>
                <h5 class="mb-1"><?= e(studentName($student)) ?></h5>
                <div class="text-muted small mb-2"><?= e($student['admission_no']) ?></div>
                <div class="mb-2"><?= statusBadge($student['status']) ?></div>
                <div class="text-muted small mb-2"><i class="fas fa-id-badge me-1"></i>Official ID: <strong class="text-primary"><?= e($student['official_id'] ?? '—') ?></strong></div>
                <hr>
                <div class="text-start small">
                    <div class="mb-1"><i class="fas fa-school me-2 text-muted"></i><?= e($class['name'] ?? '—') ?> <?= $class['section_name'] ? '/ ' . e($class['section_name']) : '' ?></div>
                    <div class="mb-1"><i class="fas fa-chalkboard-teacher me-2 text-muted"></i><?= $class['ct_first'] ? e($class['ct_first'] . ' ' . $class['ct_last']) : 'Not assigned' ?></div>
                    <div class="mb-1"><i class="fas fa-user-tag me-2 text-muted"></i>Roll No: <?= e((string) $student['roll_no']) ?></div>
                    <div><i class="fas fa-user me-2 text-muted"></i><?= e(ucfirst($student['gender'] ?: '—')) ?></div>
                </div>
                <hr>
                <div class="d-grid gap-2">
                    <a href="<?= url('results/my-results') ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-file-alt me-1"></i>My Results</a>
                    <a href="<?= url('profile/index') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-user me-1"></i>My Profile</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary bg-gradient text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h6 class="mb-0 fw-semibold text-white"><i class="fas fa-book-open me-2"></i>My Subjects</h6>
                <span class="badge bg-light text-primary"><?= count($subjects) ?> <?= count($subjects) === 1 ? 'subject' : 'subjects' ?></span>
            </div>
            <div class="card-body">
                <?php if ($subjects): ?>
                    <div class="row g-2">
                        <?php foreach ($subjects as $sub): ?>
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100 d-flex gap-3 align-items-start">
                                    <div class="stat-icon bg-primary-subtle text-primary"><i class="fas fa-book"></i></div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold"><?= e($sub['name']) ?></div>
                                        <div class="small text-body mb-1"><i class="fas fa-chalkboard-teacher me-1 text-primary"></i><?= $sub['teacher_first'] ? e($sub['teacher_first'] . ' ' . $sub['teacher_last']) : 'Teacher not assigned' ?></div>
                                        <span class="badge bg-primary-subtle text-primary"><?= e($sub['code'] ?: '') ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No subjects assigned to your class yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary bg-gradient text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold text-white"><i class="fas fa-trophy me-2"></i>My Results</h6>
                <a href="<?= url('results/my-results') ?>" class="btn btn-sm btn-light"><i class="fas fa-eye me-1"></i>View all</a>
            </div>
            <div class="card-body p-0">
                <?php if ($results): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Exam</th>
                                    <th>Type</th>
                                    <th>Total</th>
                                    <th>Percentage</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($results, 0, 6) as $r): ?>
                                    <tr>
                                        <td class="fw-semibold small"><?= e($r['exam_name']) ?></td>
                                        <td class="small"><?= e($r['type_name'] ?: '—') ?></td>
                                        <td class="small"><?= e(number_format((float) $r['total'], 1)) ?> / <?= e(number_format((float) $r['max_total'], 1)) ?></td>
                                        <td><?= statusBadge(gradeFor((float) $r['percentage'])['grade']) ?></td>
                                        <td class="text-end">
                                            <a href="<?= url('results/view/' . $r['exam_id'] . '/' . $student['id']) ?>" class="btn btn-sm btn-outline-primary" title="View Result"><i class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted small p-3 mb-0">No completed results yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary bg-gradient text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h6 class="mb-0 fw-semibold text-white"><i class="fas fa-clipboard-check me-2"></i>My Attendance</h6>
                <span class="badge bg-light text-primary"><?= $attendanceTotal ?> <?= $attendanceTotal === 1 ? 'record' : 'records' ?></span>
            </div>
            <div class="card-body">
                <?php if ($attendanceTotal > 0): ?>
                    <div class="row g-2">
                        <div class="col-3 text-center">
                            <div class="fs-4 fw-bold text-success"><?= $attendanceMap['present'] ?? 0 ?></div>
                            <div class="text-muted small">Present</div>
                        </div>
                        <div class="col-3 text-center">
                            <div class="fs-4 fw-bold text-info"><?= $attendanceMap['late'] ?? 0 ?></div>
                            <div class="text-muted small">Late</div>
                        </div>
                        <div class="col-3 text-center">
                            <div class="fs-4 fw-bold text-danger"><?= $attendanceMap['absent'] ?? 0 ?></div>
                            <div class="text-muted small">Absent</div>
                        </div>
                        <div class="col-3 text-center">
                            <div class="fs-4 fw-bold text-secondary"><?= $attendanceMap['leave'] ?? 0 ?></div>
                            <div class="text-muted small">Leave</div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No attendance records yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
