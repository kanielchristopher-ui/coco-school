<?php
include __DIR__ . '/layouts/icons.php';
$siteName = settings('school_name', APP_NAME);
$loggedIn = $loggedIn ?? false;
$isSuperAdmin = $loggedIn && Auth::role() === 'super_admin';
$portals = [
    ['users', 'One Login for Everyone', 'Teachers, students, parents, school management, discipline and cashier sign in here and land on their own space.', 'Sign In', ''],
];
?>

<section class="ws-page-hero ws-page-hero--portal">
    <div class="ws-page-hero-bg" aria-hidden="true"></div>
    <div class="ws-container">
        <p class="ws-eyebrow ws-eyebrow--light">School Portal</p>
        <h1>Welcome to the <?= e($siteName) ?> Portal</h1>
        <p><?= $loggedIn ? 'You are signed in. Continue to your dashboard below.' : 'Sign in with the account you received from the school office.' ?></p>
    </div>
</section>

<section class="ws-section">
    <div class="ws-container">
        <?php if ($isSuperAdmin): ?>
        <article class="ws-card ws-portal-card ws-reveal">
            <div class="ws-icon-badge ws-icon-badge--portal"><?php ws_icon('monitor'); ?></div>
            <h3>Dashboard</h3>
            <p>Manage the school system from the administrator dashboard.</p>
            <div class="ws-portal-actions">
                <a href="<?= url(Auth::home()) ?>" class="ws-btn ws-btn-primary ws-btn-sm ws-btn-block">Dashboard <?php ws_icon('arrow-right'); ?></a>
            </div>
        </article>
        <?php else: ?>
        <div class="ws-grid ws-portal-grid" style="max-width:460px;margin:0 auto">
            <?php foreach ($portals as $p): ?>
            <article class="ws-card ws-portal-card ws-reveal">
                <div class="ws-icon-badge ws-icon-badge--portal"><?php ws_icon($p[0]); ?></div>
                <h3><?= e($p[1]) ?></h3>
                <p><?= e($p[2]) ?></p>
                <div class="ws-portal-actions">
                    <?php if ($loggedIn): ?>
                        <a href="<?= url(Auth::home()) ?>" class="ws-btn ws-btn-primary ws-btn-sm ws-btn-block">Go to Dashboard <?php ws_icon('arrow-right'); ?></a>
                    <?php else: ?>
                        <a href="<?= url('auth/login') ?>" class="ws-btn ws-btn-primary ws-btn-sm ws-btn-block"><?= e($p[3]) ?> <?php ws_icon('arrow-right'); ?></a>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if (!$loggedIn): ?>
        <p class="ws-center ws-muted">
            Having trouble signing in? <a href="<?= url('contact') ?>">Contact the school office</a> for assistance.
        </p>
        <?php endif; ?>
    </div>
</section>
