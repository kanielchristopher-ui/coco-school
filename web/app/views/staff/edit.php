<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Edit Staff';
$s = $staff;
?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit Staff Member</h6>
                <a href="<?= url('staff/index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Staff</a>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('staff/update/' . $s['id']) ?>">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Employee ID</label>
                            <input type="text" name="employee_id" class="form-control" value="<?= e($s['employee_id'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">First Name</label>
                            <input type="text" name="first_name" class="form-control" value="<?= e($s['first_name']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="<?= e($s['last_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" class="form-control" value="<?= e($s['department'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Designation</label>
                            <input type="text" name="designation" class="form-control" value="<?= e($s['designation'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= e($s['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= e($s['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control" value="<?= e($s['joining_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Basic Salary</label>
                            <input type="number" name="basic_salary" class="form-control" min="0" step="0.01" value="<?= e($s['basic_salary'] ?? '0') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <?php foreach (['active', 'inactive', 'left'] as $st): ?>
                                    <option value="<?= $st ?>" <?= ($s['status'] ?? 'active') === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Staff</button>
                        <a href="<?= url('staff/index') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
