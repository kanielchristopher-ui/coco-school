<?php if (!defined('APP_VERSION')) { exit; }
$student = $student ?? null;
$isEdit = $student !== null;
$v = fn(string $f, string $d = '') => e($student[$f] ?? $d);
?>
<form method="POST" action="<?= $isEdit ? url('students/update/' . $student['id']) : url('students/store') ?>" enctype="multipart/form-data" class="needs-validation" novalidate>
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-12"><hr class="my-1"></div>

        <div class="col-md-4">
            <label class="form-label required">Surname</label>
            <input type="text" name="first_name" class="form-control" value="<?= $v('first_name') ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Post-name</label>
            <input type="text" name="middle_name" class="form-control" value="<?= $v('middle_name') ?>" placeholder="e.g. Tshibola">
        </div>
        <div class="col-md-4">
            <label class="form-label">Given name</label>
            <input type="text" name="last_name" class="form-control" value="<?= $v('last_name') ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-select">
                <?php foreach (['male', 'female', 'other'] as $g): ?>
                    <option value="<?= $g ?>" <?= $v('gender', 'male') === $g ? 'selected' : '' ?>><?= ucfirst($g) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Date of Birth</label>
            <input type="date" name="dob" class="form-control" value="<?= $v('dob') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Place of Birth</label>
            <input type="text" name="birth_place" class="form-control" value="<?= $v('birth_place') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Nationality</label>
            <input type="text" name="nationality" class="form-control" value="<?= $v('nationality') !== '' ? $v('nationality') : 'Congolaise' ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">PERM Number</label>
            <input type="text" name="perm_number" class="form-control" value="<?= $v('perm_number') ?>">
        </div>

        <div class="col-md-4">
            <label class="form-label">Class</label>
            <select name="class_id" class="form-select class-select" required>
                <option value="">Select Class</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= (int) ($student['class_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Section</label>
            <select name="section_id" class="form-select section-select">
                <option value="">Select Section</option>
                <?php if ($isEdit && !empty($student['class_id'])): ?>
                    <?php foreach ((new ClassRoom())->sections((int) $student['class_id']) as $sec): ?>
                        <option value="<?= (int) $sec['id'] ?>" <?= (int) ($student['section_id'] ?? 0) === (int) $sec['id'] ? 'selected' : '' ?>><?= e($sec['name']) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Blood Group</label>
            <select name="blood_group" class="form-select">
                <option value="">Select</option>
                <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                    <option value="<?= $bg ?>" <?= $v('blood_group') === $bg ? 'selected' : '' ?>><?= $bg ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <?php foreach (['active', 'inactive', 'alumni', 'transferred'] as $st): ?>
                    <option value="<?= $st ?>" <?= ($student['status'] ?? 'active') === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12"><hr class="my-1"></div>

        <div class="col-md-4">
            <label class="form-label">Guardian Name</label>
            <input type="text" name="guardian_name" class="form-control" value="<?= $v('guardian_name') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Relation</label>
            <input type="text" name="guardian_relation" class="form-control" placeholder="Father/Mother" value="<?= $v('guardian_relation') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Guardian Phone</label>
            <input type="text" name="guardian_phone" class="form-control" value="<?= $v('guardian_phone') ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Guardian Email</label>
            <input type="email" name="guardian_email" class="form-control" value="<?= $v('guardian_email') ?>">
        </div>

        <div class="col-12">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" value="<?= $v('address') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">City</label>
            <input type="text" name="city" class="form-control" value="<?= $v('city') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">State</label>
            <input type="text" name="state" class="form-control" value="<?= $v('state') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Commune / District</label>
            <input type="text" name="zip" class="form-control" value="<?= $v('zip') ?>">
        </div>

        <div class="col-12"><hr class="my-1"></div>

        <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= $v('phone') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= $v('email') ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label">Medical Information</label>
            <textarea name="medical_info" class="form-control" rows="2"><?= $v('medical_info') ?></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Student Photo</label>
            <input type="file" name="photo" class="form-control" accept="image/*">
            <?php if (!empty($student['photo'])): ?>
                <div class="mt-2"><?= avatar($student['photo'], $student['first_name'], 50) ?></div>
            <?php endif; ?>
        </div>

        <?php if (!$isEdit): ?>
        <div class="col-12"><hr class="my-1"></div>
        <div class="col-12">
            <label class="form-label fw-semibold"><i class="fas fa-user-lock me-1"></i>Login Account <span class="text-muted fw-normal">(optional)</span></label>
            <div class="text-muted small mb-2">Creates the student's login account. Leave Email blank to use the student email above; leave Password blank to use the default.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label">Username</label>
            <input type="text" name="account_username" class="form-control" placeholder="Auto-generated from email">
        </div>
        <div class="col-md-4">
            <label class="form-label">Email</label>
            <input type="email" name="account_email" class="form-control" value="<?= e($student['email'] ?? '') ?>" placeholder="Login email (defaults to student email)">
        </div>
        <div class="col-md-4">
            <label class="form-label">Password</label>
            <input type="password" name="account_password" class="form-control" placeholder="Default: <?= e(DEFAULT_STUDENT_PASSWORD) ?>">
        </div>
        <?php endif; ?>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i><?= $isEdit ? 'Update Student' : 'Save Student' ?></button>
        <a href="<?= url('students/index') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Load sections when class changes.
    document.querySelectorAll('.class-select').forEach(function (sel) {
        sel.addEventListener('change', function () {
            const sectionSelect = this.closest('form').querySelector('.section-select');
            sectionSelect.innerHTML = '<option value="">Select Section</option>';
            if (!this.value) return;
            fetch('<?= url('classes/sections-json') ?>?class_id=' + this.value)
                .then(r => r.json())
                .then(data => {
                    data.forEach(s => {
                        const o = document.createElement('option');
                        o.value = s.id; o.textContent = s.name;
                        sectionSelect.appendChild(o);
                    });
                });
        });
    });
});
</script>
