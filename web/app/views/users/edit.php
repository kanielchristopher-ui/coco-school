<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Edit User';
$u = $user;
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit User</h6>
                <a href="<?= url('users/index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Users</a>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('users/update/' . $u['id']) ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label required">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?= e(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''))) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= e($u['email']) ?>" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= e($u['phone'] ?? '') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label required">Role</label>
                            <select name="role_id" class="form-select" required>
                                <option value="">Select Role</option>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= (int) $r['id'] ?>" <?= (int) $u['role_id'] === (int) $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 d-none" id="linkStudentGroup">
                        <label class="form-label">Link to Student Profile</label>
                        <select name="linked_student_id" class="form-select">
                            <option value="">— Not linked —</option>
                            <?php foreach ($students as $s): ?>
                                <option value="<?= (int) $s['id'] ?>" <?= (int) $linked['student_id'] === (int) $s['id'] ? 'selected' : '' ?>><?= e(trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''))) ?> (<?= e($s['admission_no'] ?? '') ?><?= $s['class_name'] ? ' - ' . e($s['class_name']) : '' ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Optional. Leave unlinked and a Student profile is created automatically from this account.</div>
                    </div>
                    <div class="mb-3 d-none" id="linkTeacherGroup">
                        <label class="form-label">Link to Teacher Profile</label>
                        <select name="linked_teacher_id" class="form-select">
                            <option value="">— Not linked —</option>
                            <?php foreach ($teachers as $t): ?>
                                <option value="<?= (int) $t['id'] ?>" <?= (int) $linked['teacher_id'] === (int) $t['id'] ? 'selected' : '' ?>><?= e(trim(($t['first_name'] ?? '') . ' ' . ($t['last_name'] ?? ''))) ?> (<?= e($t['employee_id'] ?? '') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Links the account to a teacher so they can manage their assigned classes and subjects.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password <small class="text-muted">(leave blank to keep current, min <?= MIN_PASSWORD_LENGTH ?> characters)</small></label>
                        <input type="password" name="password" class="form-control" minlength="<?= MIN_PASSWORD_LENGTH ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (['active', 'inactive', 'suspended', 'pending'] as $st): ?>
                                <option value="<?= $st ?>" <?= $u['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update User</button>
                        <a href="<?= url('users/index') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.querySelector('select[name="role_id"]');
    const slugs = <?= json_encode(array_column($roles, 'slug', 'id')) ?>;
    const studentGroup = document.getElementById('linkStudentGroup');
    const teacherGroup = document.getElementById('linkTeacherGroup');
    function toggleLink() {
        const slug = slugs[roleSelect.value] || '';
        studentGroup.classList.toggle('d-none', slug !== 'student');
        teacherGroup.classList.toggle('d-none', slug !== 'teacher');
    }
    roleSelect.addEventListener('change', toggleLink);
    toggleLink();
});
</script>
