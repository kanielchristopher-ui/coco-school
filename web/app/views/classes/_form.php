<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = $class ?? null ? 'Edit Class' : 'Add Class';
$class = $class ?? null;
$isEdit = $class !== null;
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold mb-0"><i class="fas fa-school me-2 text-primary"></i><?= $isEdit ? 'Edit Class' : 'Add Class' ?></h6>
    <a href="<?= url('classes/index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? url('classes/update/' . $class['id']) : url('classes/store') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label required">Class Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. 5th Primary" value="<?= e($class['name'] ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label required">Cycle</label>
                    <?php $cycleVal = $class['cycle'] ?? 'primaire'; ?>
                    <select name="cycle" class="form-select" required>
                        <option value="maternel" <?= $cycleVal === 'maternel' ? 'selected' : '' ?>>Nursery (Maternelle)</option>
                        <option value="primaire" <?= $cycleVal === 'primaire' ? 'selected' : '' ?>>Primary (Primaire)</option>
                        <option value="secondaire" <?= $cycleVal === 'secondaire' ? 'selected' : '' ?>>Secondary (Secondaire)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Level Rank</label>
                    <input type="text" name="numeric_name" class="form-control" placeholder="e.g. 5" value="<?= e($class['numeric_name'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Form Master / Class Teacher</label>
                    <select name="class_teacher_id" class="form-select">
                        <option value="">Not assigned</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= (int) $t['id'] ?>" <?= (int) ($class['class_teacher_id'] ?? 0) === (int) $t['id'] ? 'selected' : '' ?>>
                                <?= e($t['first_name'] . ' ' . $t['last_name']) ?> (<?= e($t['employee_id']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i><?= $isEdit ? 'Update Class' : 'Save Class' ?></button>
                <a href="<?= url('classes/index') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
