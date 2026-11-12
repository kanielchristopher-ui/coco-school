<?php
include __DIR__ . '/layouts/icons.php';
$siteName = settings('school_name', APP_NAME);
$phone = settings('school_phone', '');
$email = settings('school_email', '');
?>

<section class="ws-page-hero">
    <div class="ws-page-hero-bg" aria-hidden="true"></div>
    <div class="ws-container">
        <p class="ws-eyebrow ws-eyebrow--light">Contact Us</p>
        <h1>Get in Touch</h1>
        <p>We would love to hear from you. Send us a message and we will respond as soon as we can.</p>
    </div>
</section>

<section class="ws-section">
    <div class="ws-container ws-contact-grid">
        <div class="ws-reveal">
            <div class="ws-contact-panel">
                <p class="ws-eyebrow">Contact Information</p>
                <h2>We're Here to Help</h2>
                <p class="ws-contact-intro">Whether you have a question about admissions, academics or school life, our office is happy to assist.</p>
                <ul class="ws-contact-list">
                    <li>
                        <span class="ws-contact-icon"><?php ws_icon('map-pin'); ?></span>
                        <div><strong>Address</strong><span><?= e(settings('school_address', 'Kinshasa, RDC')) ?></span></div>
                    </li>
                    <?php if ($phone !== ''): ?>
                    <li>
                        <span class="ws-contact-icon"><?php ws_icon('phone'); ?></span>
                        <div><strong>Phone</strong><a href="tel:<?= e($phone) ?>"><?= e($phone) ?></a></div>
                    </li>
                    <?php endif; ?>
                    <?php if ($email !== ''): ?>
                    <li>
                        <span class="ws-contact-icon"><?php ws_icon('mail'); ?></span>
                        <div><strong>Email</strong><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></div>
                    </li>
                    <?php endif; ?>
                    <li>
                        <span class="ws-contact-icon"><?php ws_icon('clock'); ?></span>
                        <div><strong>Office Hours</strong><span>Monday &ndash; Friday, 8:00 AM &ndash; 4:00 PM</span></div>
                    </li>
                </ul>
            </div>
            <div class="ws-map" role="img" aria-label="Map placeholder">
                <span class="ws-map-pin"><?php ws_icon('map-pin'); ?></span>
                <p>Interactive map coming soon</p>
            </div>
        </div>

        <div class="ws-card ws-form-card ws-reveal">
            <p class="ws-eyebrow">Send a Message</p>
            <h2>Send Us a Message</h2>
            <p class="ws-form-intro">Fields marked <span class="ws-required">*</span> are required.</p>
            <form class="ws-form" method="post" action="<?= url('contactSubmit') ?>" id="wsContactForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(CSRF::token()) ?>">
                <div class="ws-field">
                    <label for="name">Full Name <span class="ws-required">*</span></label>
                    <input type="text" id="name" name="name" maxlength="120" required value="<?= e(old('name')) ?>" placeholder="Your full name">
                </div>
                <div class="ws-field">
                    <label for="email">Email Address <span class="ws-required">*</span></label>
                    <input type="email" id="email" name="email" maxlength="150" required value="<?= e(old('email')) ?>" placeholder="you@example.com">
                </div>
                <div class="ws-field">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" maxlength="30" value="<?= e(old('phone')) ?>" placeholder="Optional">
                </div>
                <div class="ws-field">
                    <label for="subject">Subject <span class="ws-required">*</span></label>
                    <input type="text" id="subject" name="subject" maxlength="150" required value="<?= e(old('subject')) ?>" placeholder="What is this about?">
                </div>
                <div class="ws-field">
                    <label for="message">Message <span class="ws-required">*</span></label>
                    <textarea id="message" name="message" rows="6" maxlength="3000" required placeholder="Write your message here..."><?= e(old('message')) ?></textarea>
                </div>
                <button type="submit" class="ws-btn ws-btn-gold ws-btn-block">Send Message <?php ws_icon('send'); ?></button>
            </form>
        </div>
    </div>
</section>
