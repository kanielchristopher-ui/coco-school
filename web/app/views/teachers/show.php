<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Teacher Profile';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <a href="<?= url('teachers/index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>All Teachers</a>
    <div class="d-flex gap-2">
        <a href="<?= url('teachers/edit/' . $teacher['id']) ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit me-1"></i>Edit</a>
        <button class="btn btn-sm btn-outline-danger" data-delete-url="<?= url('teachers/destroy/' . $teacher['id']) ?>" data-teacher-name="<?= e(trim(($teacher['first_name'] ?? '') . ' ' . ($teacher['last_name'] ?? ''))) ?>" onclick="confirmDeleteTeacher(this)"><i class="fas fa-trash me-1"></i>Delete</button>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <?= avatar($teacher['photo'], $teacher['first_name'] . ' ' . $teacher['last_name'], 96) ?>
                <h5 class="mt-3 mb-0"><?= e($teacher['first_name'] . ' ' . $teacher['last_name']) ?></h5>
                <div class="mt-2"><?= statusBadge($teacher['status']) ?></div>
                <hr>
                <div class="text-start small">
                    <p class="mb-1"><i class="fas fa-id-badge me-2 text-primary"></i><strong>Official ID:</strong> <?= e($teacher['official_id'] ?: '—') ?></p>
                    <p class="mb-1"><i class="fas fa-building me-2 text-primary"></i><?= e($teacher['department'] ?: '—') ?></p>
                    <p class="mb-1"><i class="fas fa-graduation-cap me-2 text-primary"></i><?= e($teacher['qualification'] ?: '—') ?></p>
                    <p class="mb-1"><i class="fas fa-tag me-2 text-primary"></i><?= e($teacher['specialization'] ?: '—') ?></p>
                    <p class="mb-1"><i class="fas fa-calendar me-2 text-primary"></i>Joined: <?= formatDate($teacher['joining_date']) ?></p>
                    <p class="mb-1"><i class="fas fa-dollar-sign me-2 text-primary"></i><?= currency((float) $teacher['salary']) ?> / month</p>
                    <?php if ($teacher['cv']): ?>
                        <p class="mb-1"><a href="<?= BASE_URL . '/' . e($teacher['cv']) ?>" target="_blank"><i class="fas fa-file-pdf me-2 text-danger"></i>Download CV</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="row g-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="fw-bold mb-0">Contact</h6></div>
                    <div class="card-body small">
                        <div class="row">
                            <div class="col-md-6 mb-2"><strong>Phone:</strong> <?= e($teacher['phone'] ?: '—') ?></div>
                            <div class="col-md-6 mb-2"><strong>Email:</strong> <?= e($teacher['email'] ?: '—') ?></div>
                            <div class="col-12 mb-2"><strong>Address:</strong> <?= e($teacher['address'] ?: '—') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="fw-bold mb-0">Assigned Classes (Form Master)</h6></div>
                    <div class="card-body">
                        <?php if (empty($teacher['classes'])): ?>
                            <p class="text-muted small mb-0">Not assigned as Form Master to any class.</p>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($teacher['classes'] as $cl): ?>
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span><?= e($cl['name']) ?></span>
                                        <span class="badge bg-light text-dark border">Form Master</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="fw-bold mb-0">Assigned Subjects</h6></div>
                    <div class="card-body">
                        <?php if (empty($teacher['subjects'])): ?>
                            <p class="text-muted small mb-0">No subject assignments.</p>
                        <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($teacher['subjects'] as $sub): ?>
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span><?= e($sub['name']) ?></span>
                                        <span class="badge bg-light text-dark border"><?= e($sub['class_name'] ?: '—') ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="fw-bold mb-0">Attendance Summary</h6></div>
                    <div class="card-body">
                        <?php if (empty($teacher['attendance'])): ?>
                            <p class="text-muted small mb-0">No attendance recorded.</p>
                        <?php else: ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php $colors = ['present' => 'success', 'absent' => 'danger', 'late' => 'warning', 'leave' => 'info']; ?>
                                <?php foreach ($teacher['attendance'] as $a): ?>
                                    <span class="badge bg-<?= $colors[$a['status']] ?? 'secondary' ?>"><?= ucfirst($a['status']) ?>: <?= $a['total'] ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
