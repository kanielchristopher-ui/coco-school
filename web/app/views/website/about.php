<?php
include __DIR__ . '/layouts/icons.php';
$siteName = settings('school_name', APP_NAME);
$shortName = settings('school_short_name', 'WIS');
$principalName = settings('principal_name', 'The Principal');
?>

<section class="ws-page-hero">
    <div class="ws-page-hero-bg" aria-hidden="true"></div>
    <div class="ws-container">
        <p class="ws-eyebrow ws-eyebrow--light">About Us</p>
        <h1>Who We Are</h1>
        <p><?= e($siteName) ?> is dedicated to academic excellence, strong character and the success of every student.</p>
    </div>
</section>

<section class="ws-section">
    <div class="ws-container ws-split">
        <div class="ws-split-media ws-reveal">
            <div class="ws-media-frame">
                <img class="ws-media-img" src="<?= asset('images/about-campus.svg') ?>" alt="Illustration of the school campus building" loading="lazy">
            </div>
        </div>
        <div class="ws-split-content ws-reveal">
            <p class="ws-eyebrow">Our School</p>
            <h2>A Community of Learners</h2>
            <p><?= e($siteName) ?> provides a supportive, inspiring environment where students are encouraged to explore, question and grow. Our balanced approach blends a strong academic foundation with the arts, sports, technology and service so that each learner develops as a complete person.</p>
            <p>We believe every child has unique talents, and our job is to help those talents flourish. Parents, teachers and students work together as partners in the learning journey.</p>
            <ul class="ws-check-list">
                <li><?php ws_icon('check-circle'); ?><span>A safe, welcoming and well-managed campus</span></li>
                <li><?php ws_icon('check-circle'); ?><span>Strong parent&ndash;school communication</span></li>
                <li><?php ws_icon('check-circle'); ?><span>Affordable, transparent fees</span></li>
            </ul>
        </div>
    </div>
</section>

<section class="ws-section ws-section--tint">
    <div class="ws-container ws-grid ws-grid--3">
        <article class="ws-card ws-value ws-reveal">
            <div class="ws-icon-badge"><?php ws_icon('book-open'); ?></div>
            <h3>Our Mission</h3>
            <p>To provide a quality, inclusive education that empowers every student with knowledge, skills and character, preparing them to become responsible, confident and successful members of a global society.</p>
        </article>
        <article class="ws-card ws-value ws-reveal">
            <div class="ws-icon-badge ws-icon-badge--gold"><?php ws_icon('star'); ?></div>
            <h3>Our Vision</h3>
            <p>To be a leading international school that inspires a lifelong love of learning and develops future leaders who make a positive difference in their communities and the world.</p>
        </article>
        <article class="ws-card ws-value ws-reveal">
            <div class="ws-icon-badge"><?php ws_icon('shield'); ?></div>
            <h3>Our Motto</h3>
            <p><?= e(settings('school_motto', 'Knowledge, Integrity, Excellence')) ?> &mdash; these three words capture what we stand for and guide everything we do.</p>
        </article>
    </div>
</section>

<section class="ws-section">
    <div class="ws-container">
        <div class="ws-section-head">
            <p class="ws-eyebrow">Core Values</p>
            <h2>What We Stand For</h2>
        </div>
        <div class="ws-grid ws-grid--3">
            <?php
            $values = [
                ['book', 'Knowledge', 'We pursue learning with curiosity, rigor and a commitment to excellence.'],
                ['shield', 'Integrity', 'We act with honesty, respect and responsibility in everything we do.'],
                ['star', 'Excellence', 'We set high standards and support every student to achieve their personal best.'],
                ['users', 'Respect', 'We celebrate diversity and treat everyone with kindness and dignity.'],
                ['trending-up', 'Perseverance', 'We encourage resilience, hard work and a growth mindset.'],
                ['heart', 'Community', 'We work together &mdash; students, parents and staff &mdash; as one family.'],
            ];
            foreach ($values as $v):
            ?>
            <article class="ws-card ws-value ws-value--small ws-reveal">
                <div class="ws-icon-badge ws-icon-badge--ghost"><?php ws_icon($v[0]); ?></div>
                <h3><?= e($v[1]) ?></h3>
                <p><?= $v[2] ?></p>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="ws-section ws-section--dark">
    <div class="ws-container ws-split">
        <div class="ws-split-content ws-reveal">
            <p class="ws-eyebrow ws-eyebrow--light">Principal's Message</p>
            <h2>A Message from Our Principal</h2>
            <blockquote class="ws-quote">
                <span class="ws-quote-mark" aria-hidden="true">&ldquo;</span>
                <p>Welcome to <?= e($siteName) ?>. Choosing a school is one of the most important decisions a family makes, and I am delighted that you are considering ours. Our doors are open &mdash; come and experience the warmth, energy and excellence of our school community.</p>
            </blockquote>
            <p class="ws-quote-author">&mdash; <?= e($principalName) ?>, Principal</p>
        </div>
        <div class="ws-split-media ws-reveal">
            <div class="ws-principal-card">
                <div class="ws-principal-avatar" aria-hidden="true">
                    <?= e(strtoupper(mb_substr((string) $principalName, 0, 1))) ?>
                </div>
                <p><strong><?= e($principalName) ?></strong><br>Principal, <?= e($siteName) ?></p>
            </div>
        </div>
    </div>
</section>

<section class="ws-cta">
    <div class="ws-container ws-cta-inner">
        <h2>Experience <?= e($shortName) ?> for Yourself</h2>
        <p>Arrange a campus tour and see our community in action.</p>
        <div class="ws-cta-actions">
            <a href="<?= url('admissions') ?>" class="ws-btn ws-btn-gold">Apply for Admission <?php ws_icon('arrow-right'); ?></a>
            <a href="<?= url('contact') ?>" class="ws-btn ws-btn-light">Contact Us</a>
        </div>
    </div>
</section>
