<?php
include __DIR__ . '/layouts/icons.php';
$siteName = settings('school_name', APP_NAME);
$shortName = settings('school_short_name', 'WIS');
?>

<section class="ws-page-hero">
    <div class="ws-page-hero-bg" aria-hidden="true"></div>
    <div class="ws-container">
        <p class="ws-eyebrow ws-eyebrow--light">Our Teachers</p>
        <h1>Meet Our Teachers</h1>
        <p>Qualified, caring and committed &mdash; the heart of <?= e($siteName) ?>.</p>
    </div>
</section>

<section class="ws-section">
    <div class="ws-container">
        <?php if (empty($teachers)): ?>
            <div class="ws-empty">Our teacher profiles are being updated. Please check back soon.</div>
        <?php else: ?>
            <div class="ws-grid ws-grid--3">
                <?php foreach ($teachers as $t):
                    $fullName = trim(($t['first_name'] ?? '') . ' ' . ($t['last_name'] ?? ''));
                    $initials = strtoupper(mb_substr((string) ($t['first_name'] ?? ''), 0, 1) . mb_substr((string) ($t['last_name'] ?? ''), 0, 1));
                    $role = $t['specialization'] ?? ($t['department'] ?? '');
                    $tPhoto = $t['photo'] ?? '';
                ?>
                <article class="ws-card ws-teacher ws-reveal">
                    <div class="ws-teacher-avatar" aria-hidden="true">
                        <?php if ($tPhoto && is_file(ROOT_PATH . '/public/' . ltrim($tPhoto, '/'))): ?>
                            <img src="<?= BASE_URL . '/' . e(ltrim($tPhoto, '/')) ?>" alt="">
                        <?php else: ?>
                            <?= e($initials !== '' ? $initials : '?') ?>
                        <?php endif; ?>
                    </div>
                    <h3><?= e($fullName !== '' ? $fullName : 'Staff Member') ?></h3>
                    <?php if ($role !== ''): ?>
                        <p class="ws-teacher-role"><?= e($role) ?></p>
                    <?php endif; ?>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="ws-note ws-reveal">
            <div class="ws-icon-badge ws-icon-badge--gold"><?php ws_icon('users'); ?></div>
            <div>
                <h3>Interested in Joining Our Team?</h3>
                <p>We are always glad to hear from passionate educators. Get in touch with our school office to learn about current opportunities.</p>
            </div>
            <a href="<?= url('contact') ?>" class="ws-btn ws-btn-outline">Contact the School Office <?php ws_icon('arrow-right'); ?></a>
        </div>
    </div>
</section>
