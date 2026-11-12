<?php
include __DIR__ . '/layouts/icons.php';
$siteName = settings('school_name', APP_NAME);
?>

<section class="ws-page-hero">
    <div class="ws-page-hero-bg" aria-hidden="true"></div>
    <div class="ws-container">
        <p class="ws-eyebrow ws-eyebrow--light">Admissions</p>
        <h1>Join Our School Family</h1>
        <p>We welcome applications from families who value quality education and strong character.</p>
    </div>
</section>

<section class="ws-section">
    <div class="ws-container ws-split">
        <div class="ws-split-content ws-reveal">
            <p class="ws-eyebrow">Welcome</p>
            <h2>Admissions Overview</h2>
            <p>Admission to <?= e($siteName) ?> is open to students from primary through secondary levels. We aim to make the process simple, transparent and welcoming for every family.</p>
            <p>Our admission team is happy to answer your questions, arrange a campus tour, and guide you through each step. Spaces are limited, so we encourage early applications.</p>
            <ul class="ws-check-list">
                <li><?php ws_icon('check-circle'); ?><span>Primary and secondary levels welcome</span></li>
                <li><?php ws_icon('check-circle'); ?><span>Campus tours available on request</span></li>
                <li><?php ws_icon('check-circle'); ?><span>Simple, transparent and supportive process</span></li>
            </ul>
            <div class="ws-split-actions">
                <a href="<?= url('contact') ?>" class="ws-btn ws-btn-primary">Talk to Our Admissions Office <?php ws_icon('arrow-right'); ?></a>
            </div>
        </div>
        <div class="ws-split-media ws-reveal">
            <div class="ws-media-frame">
                <img class="ws-media-img" src="<?= asset('images/life-3.svg') ?>" alt="Illustration: welcome to learning at our school" loading="lazy">
            </div>
        </div>
    </div>
</section>

<section class="ws-section ws-section--tint">
    <div class="ws-container">
        <div class="ws-section-head">
            <p class="ws-eyebrow">How to Apply</p>
            <h2>Admission Process</h2>
        </div>
        <ol class="ws-steps">
            <li class="ws-reveal"><span class="ws-step-num">1</span><div><h3>Submit Application</h3><p>Complete the application form and provide the required documents.</p></div></li>
            <li class="ws-reveal"><span class="ws-step-num">2</span><div><h3>Document Review</h3><p>Our admissions team reviews your application and supporting documents.</p></div></li>
            <li class="ws-reveal"><span class="ws-step-num">3</span><div><h3>Assessment / Interview</h3><p>The student attends an age-appropriate assessment and a short interview.</p></div></li>
            <li class="ws-reveal"><span class="ws-step-num">4</span><div><h3>Admission Decision</h3><p>You will be notified of the admission decision within a few days.</p></div></li>
            <li class="ws-reveal"><span class="ws-step-num">5</span><div><h3>Enrollment</h3><p>Complete the enrollment and fee payment to secure your place.</p></div></li>
        </ol>
    </div>
</section>

<section class="ws-section">
    <div class="ws-container">
        <div class="ws-section-head">
            <p class="ws-eyebrow">Documents</p>
            <h2>Requirements</h2>
        </div>
        <div class="ws-grid ws-grid--2">
            <div class="ws-card ws-reveal">
                <div class="ws-card-head">
                    <div class="ws-icon-badge"><?php ws_icon('book-open'); ?></div>
                    <h3>For All Applicants</h3>
                </div>
                <ul class="ws-list">
                    <li><?php ws_icon('check'); ?><span>Completed application form</span></li>
                    <li><?php ws_icon('check'); ?><span>Birth certificate or equivalent</span></li>
                    <li><?php ws_icon('check'); ?><span>Recent passport-size photographs</span></li>
                    <li><?php ws_icon('check'); ?><span>School reports / transcripts from the previous school</span></li>
                    <li><?php ws_icon('check'); ?><span>Transfer / leaving certificate (where applicable)</span></li>
                </ul>
            </div>
            <div class="ws-card ws-reveal">
                <div class="ws-card-head">
                    <div class="ws-icon-badge ws-icon-badge--gold"><?php ws_icon('clock'); ?></div>
                    <h3>Important Information</h3>
                </div>
                <ul class="ws-list">
                    <li><?php ws_icon('check'); ?><span>Admissions are offered on a first-come, first-served basis</span></li>
                    <li><?php ws_icon('check'); ?><span>A non-refundable application fee applies</span></li>
                    <li><?php ws_icon('check'); ?><span>Placement is subject to an assessment</span></li>
                    <li><?php ws_icon('check'); ?><span>Fee structures are shared during the interview</span></li>
                    <li><?php ws_icon('check'); ?><span>For questions, contact our office during opening hours</span></li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="ws-section ws-section--tint">
    <div class="ws-container">
        <div class="ws-section-head">
            <p class="ws-eyebrow">Good to Know</p>
            <h2>Frequently Asked Questions</h2>
        </div>
        <div class="ws-faq">
            <details class="ws-faq-item">
                <summary>What ages can apply?</summary>
                <p>We welcome applications for our primary and secondary programmes. Contact our office for the exact age groups available in the current academic year.</p>
            </details>
            <details class="ws-faq-item">
                <summary>Is an entrance assessment required?</summary>
                <p>Yes. An age-appropriate assessment and a short interview help us understand how to best support your child.</p>
            </details>
            <details class="ws-faq-item">
                <summary>Can I visit the school before applying?</summary>
                <p>Absolutely. We would love to show you around. Please contact us to arrange a campus tour.</p>
            </details>
            <details class="ws-faq-item">
                <summary>How will I know if my child is admitted?</summary>
                <p>You will receive the admission decision through your preferred contact details shortly after the assessment.</p>
            </details>
        </div>
    </div>
</section>

<section class="ws-cta">
    <div class="ws-container ws-cta-inner">
        <h2>Begin Your Application</h2>
        <p>Contact our admissions office to start your child's journey at <?= e($siteName) ?>.</p>
        <div class="ws-cta-actions">
            <a href="<?= url('contact') ?>" class="ws-btn ws-btn-gold">Contact the Admissions Office <?php ws_icon('arrow-right'); ?></a>
        </div>
    </div>
</section>
