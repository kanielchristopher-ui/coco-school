<?php
include __DIR__ . '/layouts/icons.php';
$siteName = settings('school_name', APP_NAME);
?>

<section class="ws-page-hero">
    <div class="ws-page-hero-bg" aria-hidden="true"></div>
    <div class="ws-container">
        <p class="ws-eyebrow ws-eyebrow--light">Academics</p>
        <h1>Our Academic Programs</h1>
        <p>A balanced, modern curriculum that prepares students for examinations, higher education and life.</p>
    </div>
</section>

<section class="ws-section">
    <div class="ws-container">
        <div class="ws-grid ws-grid--3">
            <?php
            $programs = [
                ['book', 'Primary Education', 'Our primary programme builds strong foundations in literacy, numeracy, science and the humanities. Small classes and caring teachers help every child grow in confidence.'],
                ['graduation', 'Secondary Education', 'The secondary phase offers a broad, challenging curriculum that prepares students for national and international examinations and future studies.'],
                ['flask', 'Science', 'Purpose-built laboratories let students explore biology, chemistry and physics through hands-on experiments and inquiry-based learning.'],
                ['hash', 'Mathematics', 'From arithmetic to advanced problem-solving, mathematics is taught with real-world context to develop logic and reasoning skills.'],
                ['globe', 'Languages', 'Strong English language instruction, with additional modern languages to build confident global communicators.'],
                ['monitor', 'ICT & Technology', 'Modern computer labs, digital literacy and coding fundamentals prepare students for a technology-driven world.'],
                ['check-circle', 'Examinations', 'Students are well prepared for internal assessments and external examinations through continuous practice, revision and feedback.'],
                ['star', 'Student Development', 'Beyond the classroom, sports, clubs, leadership opportunities and community service develop character, teamwork and creativity.'],
            ];
            foreach ($programs as $p):
            ?>
            <article class="ws-card ws-program ws-reveal">
                <div class="ws-icon-badge ws-icon-badge--gold"><?php ws_icon($p[0]); ?></div>
                <h3><?= e($p[1]) ?></h3>
                <p><?= e($p[2]) ?></p>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="ws-section ws-section--tint">
    <div class="ws-container ws-split">
        <div class="ws-split-content ws-reveal">
            <p class="ws-eyebrow">Teaching &amp; Learning</p>
            <h2>How Our Students Learn</h2>
            <ul class="ws-check-list">
                <li><?php ws_icon('check-circle'); ?><span>Interactive, student-centered lessons led by qualified teachers</span></li>
                <li><?php ws_icon('check-circle'); ?><span>Regular assessments with clear feedback to guide progress</span></li>
                <li><?php ws_icon('check-circle'); ?><span>Technology integrated across the curriculum</span></li>
                <li><?php ws_icon('check-circle'); ?><span>Differentiated support so every learner can succeed</span></li>
                <li><?php ws_icon('check-circle'); ?><span>Parent&ndash;teacher communication throughout the year</span></li>
            </ul>
            <div class="ws-split-actions">
                <a href="<?= url('admissions') ?>" class="ws-btn ws-btn-primary">Apply for Admission <?php ws_icon('arrow-right'); ?></a>
                <a href="<?= url('teachers') ?>" class="ws-btn ws-btn-outline">Meet Our Teachers</a>
            </div>
        </div>
        <div class="ws-split-media ws-reveal">
            <div class="ws-media-frame">
                <img class="ws-media-img" src="<?= asset('images/life-1.svg') ?>" alt="Illustration: interactive classroom learning" loading="lazy">
            </div>
        </div>
    </div>
</section>

<section class="ws-cta">
    <div class="ws-container ws-cta-inner">
        <h2>Ready to Begin Learning with Us?</h2>
        <p>Start your child's academic journey at <?= e($siteName) ?> today.</p>
        <div class="ws-cta-actions">
            <a href="<?= url('admissions') ?>" class="ws-btn ws-btn-gold">Apply for Admission <?php ws_icon('arrow-right'); ?></a>
            <a href="<?= url('contact') ?>" class="ws-btn ws-btn-light">Contact Us</a>
        </div>
    </div>
</section>
