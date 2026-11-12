<?php
// Public website footer. Variables available from the controller: $activeNav.
include __DIR__ . '/icons.php';
$siteName  = settings('school_name', APP_NAME);
$shortName = settings('school_short_name', 'WIS');
$motto     = settings('school_motto', 'Knowledge, Integrity, Excellence');
$year      = date('Y');
$addr      = settings('school_address', 'Kinshasa, RDC');
$phone     = settings('school_phone', '');
$email     = settings('school_email', '');
?>
<footer class="ws-footer">
    <div class="ws-container ws-footer-grid">

        <div class="ws-footer-col ws-footer-about">
            <a class="ws-brand ws-brand--footer" href="<?= url('') ?>">
                <img class="ws-logo" src="<?= asset('images/logo.svg') ?>" alt="<?= e($shortName) ?> logo">
                <span class="ws-brand-text">
                    <span class="ws-brand-name"><?= e($siteName) ?></span>
                    <span class="ws-brand-motto"><?= e($motto) ?></span>
                </span>
            </a>
            <p><?= e($siteName) ?> is committed to providing quality education, developing strong character, and preparing students for a successful future.</p>
        </div>

        <nav class="ws-footer-col" aria-label="Explore">
            <h4>Explore</h4>
            <ul>
                <li><a href="<?= url('about') ?>">About Us</a></li>
                <li><a href="<?= url('academics') ?>">Academics</a></li>
                <li><a href="<?= url('admissions') ?>">Admissions</a></li>
                <li><a href="<?= url('teachers') ?>">Our Teachers</a></li>
                <li><a href="<?= url('news') ?>">News &amp; Events</a></li>
            </ul>
        </nav>

        <nav class="ws-footer-col" aria-label="Resources">
            <h4>Resources</h4>
            <ul>
                <li><a href="<?= url('portal') ?>">Student Portal</a></li>
                <li><a href="<?= url('portal') ?>">Teacher Portal</a></li>
                <li><a href="<?= url('portal') ?>">Parent Portal</a></li>
                <li><a href="<?= url('contact') ?>">Contact Us</a></li>
            </ul>
        </nav>

        <div class="ws-footer-col">
            <h4>Contact</h4>
            <ul class="ws-footer-contact">
                <li>
                    <span class="ws-footer-contact-ico"><?php ws_icon('map-pin'); ?></span>
                    <span class="ws-footer-contact-text"><?= e($addr) ?></span>
                </li>
                <?php if ($phone !== ''): ?>
                <li>
                    <span class="ws-footer-contact-ico"><?php ws_icon('phone'); ?></span>
                    <a href="tel:<?= e($phone) ?>"><?= e($phone) ?></a>
                </li>
                <?php endif; ?>
                <?php if ($email !== ''): ?>
                <li>
                    <span class="ws-footer-contact-ico"><?php ws_icon('mail'); ?></span>
                    <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
                </li>
                <?php endif; ?>
            </ul>
        </div>

    </div>

    <div class="ws-footer-bottom">
        <div class="ws-container ws-footer-bottom-inner">
            <p>&copy; <?= e($year) ?> <?= e($siteName) ?>. All Rights Reserved.</p>
            <p class="ws-footer-portal"><a href="<?= url('portal') ?>">School Portal</a> &middot; <a href="<?= url('auth/login') ?>">Staff Login</a></p>
        </div>
    </div>
</footer>

<div class="ws-modal" id="wsNewsModal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="ws-modal-overlay" data-ws-modal-close></div>
    <div class="ws-modal-box">
        <button type="button" class="ws-modal-close" data-ws-modal-close aria-label="Close">
            <svg class="ws-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.3 5.71a1 1 0 0 0-1.42 0L12 10.59 7.12 5.7a1 1 0 0 0-1.41 1.41L10.59 12l-4.89 4.88a1 1 0 1 0 1.42 1.42L12 13.41l4.88 4.89a1 1 0 0 0 1.42-1.42L13.41 12l4.89-4.88a1 1 0 0 0 0-1.41z"/></svg>
        </button>
        <div class="ws-modal-body">
            <p class="ws-eyebrow ws-news-modal-date" id="wsNewsModalDate"></p>
            <h2 id="wsNewsModalTitle"></h2>
            <p class="ws-news-modal-location" id="wsNewsModalLocation"></p>
            <p class="ws-news-modal-text" id="wsNewsModalDescription"></p>
        </div>
    </div>
</div>

<script src="<?= asset('js/website.js') . '?v=' . (@filemtime(ROOT_PATH . '/public/assets/js/website.js') ?: 0) ?>"></script>
</body>
</html>
