<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = $subject ?? null ? 'Edit Subject' : 'Add Subject';
$subject = $subject ?? null;
$isEdit = $subject !== null;
$v = fn(string $f, string $d = '') => e($subject[$f] ?? $d);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold mb-0"><i class="fas fa-book-open me-2 text-primary"></i><?= $isEdit ? 'Edit Subject' : 'Add Subject' ?></h6>
    <a href="<?= url('subjects/index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? url('subjects/update/' . $subject['id']) : url('subjects/store') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label required">Subject Name</label>
                    <input type="text" name="name" class="form-control" value="<?= $v('name') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" class="form-control" placeholder="e.g. MATH-01" value="<?= $v('code') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Weekly Hours</label>
                    <input type="number" name="credits" class="form-control" min="1" placeholder="Optional" value="<?= $v('credits') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Class</label>
                    <select name="class_id" class="form-select">
                        <option value="">General / All classes</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= (int) $c['id'] ?>" <?= (int) ($subject['class_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Teacher</label>
                    <select name="teacher_id" class="form-select">
                        <option value="">Not assigned</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= (int) $t['id'] ?>" <?= (int) ($subject['teacher_id'] ?? 0) === (int) $t['id'] ? 'selected' : '' ?>><?= e($t['first_name'] . ' ' . $t['last_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= $v('description') ?></textarea>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i><?= $isEdit ? 'Update Subject' : 'Save Subject' ?></button>
                <a href="<?= url('subjects/index') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
