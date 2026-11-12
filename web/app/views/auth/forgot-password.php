<form method="POST" action="<?= url('auth/send-reset-link') ?>">
    <?= csrf_field() ?>

    <p class="text-muted small mb-3">Enter your account email and we will send you a password reset link.</p>

    <div class="form-floating mb-3">
        <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required autofocus>
        <label for="email"><i class="fas fa-envelope me-1"></i> Email Address</label>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="fas fa-paper-plane me-1"></i> Send Reset Link
    </button>

    <div class="text-center mt-3">
        <a href="<?= url('auth/login') ?>" class="small"><i class="fas fa-arrow-left me-1"></i> Back to Login</a>
    </div>
</form>
