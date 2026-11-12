<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'My Profile';
$u = $user;
?>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm text-center overflow-hidden">
            <div class="card-body bg-soft-indigo">
                <div class="mb-3 mt-2">
                    <?= avatar($u['photo'] ?? null, trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')), 96) ?>
                </div>
                <h5 class="mb-1 fw-bold"><?= e(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''))) ?></h5>
                <div class="small text-body mb-2">@<?= e($u['username'] ?? '') ?></div>
                <div class="mb-2"><?= statusBadge($u['status']) ?></div>
            </div>
            <div class="card-body text-start small border-top">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fas fa-envelope text-primary"></i>
                    <span class="text-body text-truncate"><?= e($u['email'] ?? '') ?></span>
                </div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fas fa-phone text-primary"></i>
                    <span class="text-body"><?= e($u['phone'] ?: '—') ?></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-calendar text-primary"></i>
                    <span class="text-body">Joined <?= formatDate($u['created_at'] ?? null) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <?php if (!empty($teacherProfile)): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary bg-gradient text-white"><h6 class="mb-0 fw-semibold text-white"><i class="fas fa-chalkboard-teacher me-2"></i>Teacher Profile</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="text-muted small">Official ID</div>
                        <div class="fw-semibold text-primary"><?= e($teacherProfile['official_id'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Employee ID</div>
                        <div class="fw-semibold"><?= e($teacherProfile['employee_id'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Department</div>
                        <div class="fw-semibold"><?= e($teacherProfile['department'] ?: '—') ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Qualification</div>
                        <div class="fw-semibold"><?= e($teacherProfile['qualification'] ?: '—') ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Status</div>
                        <div class="fw-semibold"><?= statusBadge($teacherProfile['status']) ?></div>
                    </div>
                </div>
                <hr>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?= url('classes/index') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-school me-1"></i>My Classes</a>
                    <a href="<?= url('subjects/index') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-book-open me-1"></i>My Subjects</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!in_array(Auth::role(), ['teacher', 'student'], true)): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary bg-gradient text-white"><h6 class="mb-0 fw-semibold text-white"><i class="fas fa-user-edit me-2"></i>Edit Profile</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('profile/update') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">First Name</label>
                            <input type="text" name="first_name" class="form-control" value="<?= e($u['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="<?= e($u['last_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= e($u['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Profile Photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Profile</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary bg-gradient text-white"><h6 class="mb-0 fw-semibold text-white"><i class="fas fa-key me-2"></i>Change Password</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('profile/change-password') ?>">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label required">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">New Password <small class="text-muted">(min <?= MIN_PASSWORD_LENGTH ?> characters)</small></label>
                            <input type="password" name="new_password" class="form-control" minlength="<?= MIN_PASSWORD_LENGTH ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" minlength="<?= MIN_PASSWORD_LENGTH ?>" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn btn-warning"><i class="fas fa-key me-1"></i>Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
