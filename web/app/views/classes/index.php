<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Classes';
$canManage = $canManage ?? false;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <span class="text-muted small"><?= count($rows) ?> class(es)</span>
    <?php if ($canManage): ?>
        <a href="<?= url('classes/create') ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add Class</a>
    <?php endif; ?>
</div>

<div class="row g-3">
    <?php if (empty($rows)): ?>
        <div class="col-12"><div class="alert alert-info">No classes yet. Create your first class.</div></div>
    <?php endif; ?>
    <?php foreach ($rows as $c): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="fw-bold mb-1"><?= e($c['name']) ?></h5>
                            <span class="badge bg-secondary-subtle text-secondary"><?= e(['maternel' => 'Nursery', 'primaire' => 'Primary', 'secondaire' => 'Secondary'][$c['cycle'] ?? 'primaire'] ?? 'Primary') ?></span>
                        </div>
                        <?php if ($canManage): ?>
                        <div class="btn-group btn-group-sm">
                            <a href="<?= url('classes/manage/' . $c['id']) ?>" class="btn btn-outline-primary" title="Manage"><i class="fas fa-cog"></i></a>
                            <a href="<?= url('classes/edit/' . $c['id']) ?>" class="btn btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-outline-danger" onclick="confirmDelete('<?= url('classes/destroy/' . $c['id']) ?>')" title="Delete"><i class="fas fa-trash"></i></button>
                        </div>
                        <?php else: ?>
                        <div class="btn-group btn-group-sm">
                            <a href="<?= url('students/index?class_id=' . $c['id']) ?>" class="btn btn-outline-primary" title="View Students"><i class="fas fa-user-graduate"></i></a>
                            <a href="<?= url('attendance/index?class_id=' . $c['id']) ?>" class="btn btn-outline-success" title="Take Attendance"><i class="fas fa-clipboard-check"></i></a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted small mb-3">
                        Form Master / Class Teacher:
                        <strong><?= $c['teacher_first'] ? e($c['teacher_first'] . ' ' . $c['teacher_last']) : 'Not assigned' ?></strong>
                    </p>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-primary-subtle text-primary"><?= $c['section_count'] ?> Sections</span>
                        <span class="badge bg-success-subtle text-success"><?= $c['subject_count'] ?> Subjects</span>
                        <span class="badge bg-info-subtle text-info"><?= $c['student_count'] ?> Students</span>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
