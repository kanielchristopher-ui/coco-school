<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Teacher Dashboard';
$teacherName = trim(($teacher['first_name'] ?? '') . ' ' . ($teacher['last_name'] ?? ''));
$teacherName = $teacherName !== '' ? $teacherName : 'Teacher';
$pendingSubmissions = count($exams ?? []);
?>
<div class="card border-0 shadow-sm mb-4 welcome-card bg-soft-indigo">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <?= avatar($teacher['photo'] ?? null, $teacherName, 60) ?>
            <div>
                <div class="text-uppercase small text-muted fw-semibold">Welcome Back</div>
                <h4 class="fw-bold mb-0"><?= e($teacherName) ?></h4>
                <div class="text-muted small mt-1">
                    <span class="badge bg-primary-subtle text-primary me-2"><i class="fas fa-building me-1"></i><?= e($teacher['department'] ?: 'Teacher') ?></span>
                    <i class="fas fa-calendar-day me-1"></i><?= e(ldate('l, F j, Y')) ?>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('attendance/index') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-clipboard-check me-1"></i>Take Attendance</a>
            <a href="<?= url('profile/index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-user me-1"></i>My Profile</a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="fas fa-school"></i></div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format(count($classes ?? [])) ?></div>
                    <div class="text-muted small">My Classes</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success"><i class="fas fa-user-graduate"></i></div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format($studentCount ?? 0) ?></div>
                    <div class="text-muted small">Total Students</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info-subtle text-info"><i class="fas fa-book-open"></i></div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format(count($subjects ?? [])) ?></div>
                    <div class="text-muted small">My Subjects</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning-subtle text-warning"><i class="fas fa-clipboard-list"></i></div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format($pendingSubmissions) ?></div>
                    <div class="text-muted small">Pending Academic Submissions</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <a href="<?= url('attendance/index') ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 quick-action-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="fas fa-clipboard-check"></i></div>
                    <div>
                        <div class="fw-semibold">Take Attendance</div>
                        <div class="text-muted small">Mark student attendance</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="<?= url('results/teacher-entry') ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 quick-action-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success-subtle text-success"><i class="fas fa-edit"></i></div>
                    <div>
                        <div class="fw-semibold">Marks Entry</div>
                        <div class="text-muted small">Enter student marks</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="<?= url('exams/index') ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 quick-action-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info-subtle text-info"><i class="fas fa-file-signature"></i></div>
                    <div>
                        <div class="fw-semibold">My Exams</div>
                        <div class="text-muted small">Scheduled exams</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="<?= url('messages/inbox') ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 quick-action-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning-subtle text-warning"><i class="fas fa-envelope"></i></div>
                    <div>
                        <div class="fw-semibold">Messages</div>
                        <div class="text-muted small">Inbox and notifications</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary bg-gradient text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-school me-2"></i>My Classes</h6>
                <a href="<?= url('classes/index') ?>" class="btn btn-sm btn-light">View all</a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($classes)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th>Class</th>
                                    <th>Numeric</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($classes as $c): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= e($c['name']) ?></td>
                                        <td><?= e($c['numeric_name'] ?: '—') ?></td>
                                        <td class="text-end">
                                            <a href="<?= url('students/index?class_id=' . $c['id']) ?>" class="btn btn-sm btn-outline-primary" title="Students"><i class="fas fa-user-graduate"></i></a>
                                            <a href="<?= url('attendance/index?class_id=' . $c['id']) ?>" class="btn btn-sm btn-outline-success" title="Take Attendance"><i class="fas fa-clipboard-check"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted p-3 mb-0">No classes assigned to you yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary bg-gradient text-white">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-history me-2"></i>Recent Activity</h6>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($recentActivities)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($recentActivities as $act): ?>
                            <li class="list-group-item small d-flex align-items-start gap-2">
                                <i class="fas fa-circle text-primary mt-1" style="font-size:.4rem"></i>
                                <div>
                                    <div><?= e($act['description']) ?></div>
                                    <div class="text-muted"><?= e(ldate('M d, H:i', strtotime($act['created_at'] ?? 'now'))) ?></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted small p-3 mb-0">No recent activity.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary bg-gradient text-white">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-book-open me-2"></i>My Subjects</h6>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($subjects)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($subjects as $su): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-book text-muted"></i>
                                    <span class="fw-semibold"><?= e($su['name']) ?></span>
                                    <?php if (!empty($su['code'])): ?>
                                        <span class="badge bg-light text-dark border"><?= e($su['code']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="badge bg-primary-subtle text-primary"><?= e($su['class_name'] ?: '—') ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted p-3 mb-0">No subjects assigned to you yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary bg-gradient text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-bell me-2"></i>Notifications</h6>
                <a href="<?= url('messages/notifications') ?>" class="btn btn-sm btn-light">View all</a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($notifications)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($notifications as $n): ?>
                            <li class="list-group-item d-flex align-items-start gap-2 px-3">
                                <div class="icon-circle <?= $n['type'] === 'warning' ? 'bg-warning-subtle text-warning' : ($n['type'] === 'success' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary') ?>">
                                    <i class="fas fa-<?= $n['type'] === 'warning' ? 'exclamation-triangle' : ($n['type'] === 'success' ? 'check-circle' : 'bell') ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold small"><?= e($n['title'] ?? 'Notification') ?></div>
                                    <?php if (!empty($n['message'])): ?>
                                        <div class="text-muted small"><?= e($n['message']) ?></div>
                                    <?php endif; ?>
                                    <div class="text-muted small mt-1"><?= formatDate($n['created_at'] ?? '') ?></div>
                                </div>
                                <?php if (!$n['is_read']): ?>
                                    <span class="badge bg-primary">New</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted small p-3 mb-0">No notifications.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
