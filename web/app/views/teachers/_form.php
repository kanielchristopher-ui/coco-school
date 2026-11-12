<?php if (!defined('APP_VERSION')) { exit; }
$teacher = $teacher ?? null;
$isEdit = $teacher !== null;
$v = fn(string $f, string $d = '') => e($teacher[$f] ?? $d);
?>
<form method="POST" action="<?= $isEdit ? url('teachers/update/' . $teacher['id']) : url('teachers/store') ?>" enctype="multipart/form-data" class="needs-validation" novalidate>
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label required">Teacher Full Name</label>
            <div class="row g-2">
                <div class="col-6">
                    <input type="text" name="first_name" class="form-control" placeholder="First name" value="<?= $v('first_name') ?>" required>
                </div>
                <div class="col-6">
                    <input type="text" name="last_name" class="form-control" placeholder="Last name" value="<?= $v('last_name') ?>">
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label">Teaching Level</label>
            <?php $lvl = $teacher['teaching_level'] ?? 'secondaire'; ?>
            <select name="teaching_level" class="form-select">
                <option value="maternel" <?= $lvl === 'maternel' ? 'selected' : '' ?>>Nursery teacher (Maternelle)</option>
                <option value="primaire" <?= $lvl === 'primaire' ? 'selected' : '' ?>>Primary teacher (Primaire)</option>
                <option value="secondaire" <?= $lvl === 'secondaire' ? 'selected' : '' ?>>Secondary teacher (EB / Humanités)</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Department</label>
            <input type="text" name="department" class="form-control" list="departmentList" placeholder="e.g. Mathematics" value="<?= $v('department') ?>">
            <datalist id="departmentList">
                <?php foreach ((new Teacher())->departments() as $d): ?>
                    <option value="<?= e($d) ?>">
                <?php endforeach; ?>
            </datalist>
        </div>

        <div class="col-md-3">
            <label class="form-label">Qualification</label>
            <input type="text" name="qualification" class="form-control" placeholder="e.g. M.Sc. Mathematics, B.Ed." value="<?= $v('qualification') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" placeholder="+1-555-0100" value="<?= $v('phone') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="teacher@school.com" value="<?= $v('email') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Salary</label>
            <div class="input-group">
                <span class="input-group-text"><?= e(settings('currency_symbol', '$')) ?></span>
                <input type="number" step="0.01" min="0" name="salary" class="form-control" value="<?= $v('salary', '0') ?>">
            </div>
        </div>

        <div class="col-12"><hr class="my-1"></div>

        <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <?php foreach (['active', 'inactive', 'left'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($teacher['status'] ?? 'active') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Teacher Photo</label>
            <input type="file" name="photo" class="form-control" accept="image/*">
            <?php if (!empty($teacher['photo'])): ?>
                <div class="mt-2"><?= avatar($teacher['photo'], $teacher['first_name'], 50) ?></div>
            <?php endif; ?>
        </div>

        <?php if (!$isEdit): ?>
        <div class="col-12"><hr class="my-1"></div>
        <div class="col-12">
            <label class="form-label fw-semibold"><i class="fas fa-user-lock me-1"></i>Login Account <span class="text-muted fw-normal">(optional)</span></label>
            <div class="text-muted small mb-2">Creates the teacher's login account. Leave Email blank to use the teacher email above; leave Password blank to use the default.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label">Username</label>
            <input type="text" name="account_username" class="form-control" placeholder="Auto-generated from email">
        </div>
        <div class="col-md-4">
            <label class="form-label">Email</label>
            <input type="email" name="account_email" class="form-control" value="<?= e($teacher['email'] ?? '') ?>" placeholder="Login email (defaults to teacher email)">
        </div>
        <div class="col-md-4">
            <label class="form-label">Password</label>
            <input type="password" name="account_password" class="form-control" placeholder="Default: <?= e(DEFAULT_TEACHER_PASSWORD) ?>">
        </div>
        <?php endif; ?>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i><?= $isEdit ? 'Update Teacher' : 'Save Teacher' ?></button>
        <a href="<?= url('teachers/index') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
