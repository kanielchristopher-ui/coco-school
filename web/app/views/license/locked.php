<?php /** Page affichée quand la licence est invalide, révoquée ou falsifiée. */ ?>
<div class="text-center">
    <span class="stat-icon tone-danger mx-auto mb-3" style="width:72px;height:72px;font-size:1.8rem"><i class="fas fa-lock"></i></span>
    <h3 class="fw-bold"><?= e(t($check ? $check->status->label() : 'License required')) ?></h3>
    <p class="text-muted"><?= e($check && $check->detail !== '' ? $check->detail : $message) ?></p>
    <p class="small text-muted"><?= e(t('Contact your provider to obtain a valid license, then sign in as administrator and install the key.')) ?></p>
    <?php if (Auth::check() && in_array(Auth::role(), ['super_admin', 'admin'], true)): ?>
        <a href="<?= url('license/index') ?>" class="btn btn-primary"><i class="fas fa-key me-1"></i><?= e(t('Install a license key')) ?></a>
    <?php else: ?>
        <a href="<?= url('auth/login') ?>" class="btn btn-primary"><?= e(t('Sign In')) ?></a>
    <?php endif; ?>
</div>
