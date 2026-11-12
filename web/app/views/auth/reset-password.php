<form method="POST" action="<?= url('auth/process-reset') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">

    <p class="text-muted small mb-3">Choose a new password for your account.</p>

    <div class="form-floating mb-3">
        <input type="password" class="form-control" id="password" name="password" placeholder="New Password"
               minlength="<?= (int) MIN_PASSWORD_LENGTH ?>" required autofocus>
        <label for="password"><i class="fas fa-key me-1"></i> New Password</label>
        <div class="form-text">Minimum <?= (int) MIN_PASSWORD_LENGTH ?> characters.</div>
    </div>

    <div class="form-floating mb-3">
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm Password" required>
        <label for="password_confirmation"><i class="fas fa-key me-1"></i> Confirm Password</label>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="fas fa-check-circle me-1"></i> Reset Password
    </button>

    <div class="text-center mt-3">
        <a href="<?= url('auth/login') ?>" class="small"><i class="fas fa-arrow-left me-1"></i> Back to Login</a>
    </div>
</form>
