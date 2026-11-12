<div class="auth-welcome">
    <h3 class="auth-welcome-title">Welcome Back</h3>
    <p class="auth-welcome-sub">Sign in to your account to continue</p>
</div>

<form method="POST" action="<?= url('auth/authenticate') ?>" class="needs-validation auth-form" novalidate>
    <?= csrf_field() ?>

    <div class="mb-3">
        <label class="form-label" for="username"><i class="fas fa-user me-1"></i> Username or Email</label>
        <div class="auth-input-wrap">
            <span class="auth-input-icon"><i class="fas fa-user"></i></span>
            <input type="text" class="form-control auth-input" id="username" name="username"
                   placeholder="Enter your username or email" value="<?= e(old('username')) ?>"
                   required autofocus autocomplete="username">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label" for="password"><i class="fas fa-lock me-1"></i> Password</label>
        <div class="auth-input-wrap">
            <span class="auth-input-icon"><i class="fas fa-lock"></i></span>
            <input type="password" class="form-control auth-input" id="password" name="password"
                   placeholder="Enter your password" required autocomplete="current-password">
            <button type="button" class="auth-password-toggle" id="togglePassword" aria-label="Show password"><i class="fas fa-eye"></i></button>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
            <label class="form-check-label small" for="remember">Remember Me</label>
        </div>
        <a href="<?= url('auth/forgot-password') ?>" class="auth-forgot">Forgot password?</a>
    </div>

    <button type="submit" class="btn btn-primary auth-submit">
        <span>Sign In</span>
        <i class="fas fa-arrow-right"></i>
    </button>
</form>

<script>
(function () {
    const toggle = document.getElementById('togglePassword');
    const pwd = document.getElementById('password');
    if (toggle && pwd) {
        toggle.addEventListener('click', function () {
            const show = pwd.type === 'password';
            pwd.type = show ? 'text' : 'password';
            toggle.innerHTML = show ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
            toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    }
})();
</script>
