<?php
/**
 * Accueil public — structure inspirée de la vidéo « landing page » :
 * héros + téléphone, bandeau de chiffres, atouts, caisse (étapes), cycles,
 * communauté, témoignages (carrousel), FAQ, appel à l'action, actualités.
 */
$siteName = settings('school_name', APP_NAME);
$stats = $stats ?? ['students' => 0, 'teachers' => 0, 'classes' => 0, 'years' => 10];
$news = $news ?? [];
$spark = '<svg class="spark" viewBox="0 0 30 30" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M4 12 12 8M10 4l3 8M20 18l8 2M18 24l6 5"/></svg>';
?>

<!-- ============ Héros ============ -->
<section class="lp-hero">
    <div class="lp-wrap lp-hero-grid">
        <div>
            <span class="lp-eyebrow lp-reveal"><?= e($siteName) ?></span>
            <h1 class="lp-reveal" style="--d:.08s"><?= e(t('A school where every child')) ?> <span class="scribble"><?= e(t('grows and shines')) ?></span></h1>
            <p class="lead lp-reveal" style="--d:.16s"><?= e(t('From nursery to secondary, follow attendance, results, report cards and fees in one simple place, on your phone or computer.')) ?></p>
            <div class="lp-hero-actions lp-reveal" style="--d:.24s">
                <a class="lp-btn lp-btn--dark" href="<?= url('auth/login') ?>"><?= e(t('Get Started')) ?> <i class="fas fa-arrow-right"></i></a>
                <a class="lp-btn lp-btn--line" href="<?= url('admissions') ?>"><?= e(t('Admissions')) ?></a>
            </div>
        </div>

        <div class="lp-phone-wrap lp-reveal from-right">
            <svg class="lp-hand-doodle" viewBox="0 0 70 50" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M6 14 20 8M30 4l4 12M44 20l18-4M46 34l14 8"/></svg>
            <div class="lp-float f1"><i class="fas fa-circle-check"></i><?= e(t('Results published')) ?></div>
            <div class="lp-float f2"><i class="fas fa-user-check"></i><?= e(t('Attendance')) ?> 96 %</div>
            <div class="lp-float f3"><i class="fas fa-receipt"></i><?= e(t('Fee receipt sent')) ?></div>
            <div class="lp-phone" aria-hidden="true">
                <div class="lp-screen">
                    <div class="top"><b><?= e(settings('school_short_name', 'EP')) ?></b><span><i class="fas fa-bell"></i></span></div>
                    <div class="hi"><b><?= e(t('Hello, :name', ['name' => 'Esther'])) ?></b><?= e(t('Your results are ready')) ?></div>
                    <div class="tiles"><div class="tile"><b>78 %</b><?= e(t('Average')) ?></div><div class="tile"><b>1<sup>er</sup></b><?= e(t('Rank')) ?></div></div>
                    <div class="bars"><i style="height:45%"></i><i style="height:70%"></i><i style="height:55%"></i><i style="height:88%"></i><i style="height:65%"></i><i style="height:95%"></i></div>
                    <div class="row"><span class="dot"><i class="fas fa-book"></i></span><span><?= e(t('Mathematics')) ?> · 16/20</span></div>
                    <div class="row"><span class="dot"><i class="fas fa-language"></i></span><span><?= e(t('French')) ?> · 14/20</span></div>
                    <div class="row"><span class="dot"><i class="fas fa-coins"></i></span><span><?= e(t('Fees paid')) ?> ✓</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ Chiffres ============ -->
<section class="lp-stats" aria-label="<?= e(t('Key figures')) ?>">
    <div class="lp-wrap lp-stats-grid">
        <div class="lp-stat lp-reveal"><b data-count="<?= (int) ($stats['years'] ?? 10) ?>" data-suffix="+">0</b><span><?= e(t('Years of experience')) ?></span></div>
        <div class="lp-stat lp-reveal" style="--d:.1s"><b data-count="<?= (int) ($stats['students'] ?? 0) ?>">0</b><span><?= e(t('Students')) ?></span></div>
        <div class="lp-stat lp-reveal" style="--d:.2s"><b data-count="<?= (int) ($stats['teachers'] ?? 0) ?>">0</b><span><?= e(t('Teachers')) ?></span></div>
        <div class="lp-stat lp-reveal" style="--d:.3s"><b data-count="<?= (int) ($stats['classes'] ?? 0) ?>">0</b><span><?= e(t('Classes')) ?></span></div>
    </div>
</section>

<!-- ============ Atouts ============ -->
<section class="lp-section">
    <div class="lp-wrap">
        <div class="lp-head lp-reveal">
            <span class="lp-eyebrow"><?= e(t('Why us')) ?></span><br>
            <h2 class="lp-title"><?= $spark ?><?= e(t('The way to a better school')) ?></h2>
            <p><?= e(t('A variety of benefits when you use our school and its online services.')) ?></p>
        </div>
        <div class="lp-features">
            <article class="lp-feature lp-reveal"><span class="ico"><i class="fas fa-school"></i></span><h3><?= e(t('All cycles, one place')) ?></h3><p><?= e(t('Nursery, primary and secondary (basic education and humanities) managed together.')) ?></p></article>
            <article class="lp-feature lp-reveal" style="--d:.1s"><span class="ico c2"><i class="fas fa-file-lines"></i></span><h3><?= e(t('Results and report cards online')) ?></h3><p><?= e(t('Parents and students see published results, rankings and report cards at any time.')) ?></p></article>
            <article class="lp-feature lp-reveal" style="--d:.2s"><span class="ico c3"><i class="fas fa-mobile-screen-button"></i></span><h3><?= e(t('Simple fees, clear receipts')) ?></h3><p><?= e(t('Pay in cash or by Mobile Money and always get a receipt and your remaining balance.')) ?></p></article>
        </div>
    </div>
</section>

<!-- ============ Caisse ============ -->
<section class="lp-section" style="padding-top:0">
    <div class="lp-wrap">
        <div class="lp-map lp-reveal">
            <div class="lp-map-grid">
                <div>
                    <span class="lp-eyebrow"><?= e(t('Cashier')) ?></span><br>
                    <h2 class="lp-title"><?= $spark ?><?= e(t('Pay school fees without queues or stress')) ?></h2>
                    <p style="color:var(--muted)"><?= e(t('Every payment is recorded by the cashier, a receipt is issued, and parents can follow the balance online.')) ?></p>
                    <div class="lp-pay"><span>💵 <?= e(t('Cash')) ?></span><span>📱 M-Pesa</span><span>📱 Airtel Money</span><span>📱 Orange Money</span><span>🏦 <?= e(t('Bank Transfer')) ?></span></div>
                    <div class="lp-search"><span><?= e(t('Check my child\'s balance')) ?></span><a href="<?= url('auth/login') ?>" aria-label="<?= e(t('Sign In')) ?>"><i class="fas fa-magnifying-glass"></i></a></div>
                </div>
                <div class="lp-steps">
                    <div class="lp-step"><span class="pin"><b>1</b></span><h4><?= e(t('Registration')) ?></h4><p><?= e(t('The student is registered and receives an official ID.')) ?></p></div>
                    <div class="lp-step"><span class="pin"><b>2</b></span><h4><?= e(t('Payment')) ?></h4><p><?= e(t('Pay the fees at the cashier or by Mobile Money.')) ?></p></div>
                    <div class="lp-step"><span class="pin"><b>3</b></span><h4><?= e(t('Receipt')) ?></h4><p><?= e(t('A numbered receipt is issued and saved automatically.')) ?></p></div>
                    <div class="lp-step"><span class="pin"><b>4</b></span><h4><?= e(t('Follow-up')) ?></h4><p><?= e(t('Parents check balances and receipts in their space.')) ?></p></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ Cycles ============ -->
<section class="lp-section" style="padding-top:0">
    <div class="lp-wrap">
        <div class="lp-head lp-reveal"><span class="lp-eyebrow"><?= e(t('Academic Programs')) ?></span><br><h2 class="lp-title"><?= $spark ?><?= e(t('Nursery, primary, secondary: what else do you need?')) ?></h2></div>
        <div class="lp-cycles">
            <article class="lp-cycle c1 lp-reveal"><div class="big">🧸</div><h3><?= e(t('Nursery')) ?></h3><p><?= e(t('Three years of play, language, early maths and arts to prepare for primary school.')) ?></p><span class="tag"><?= e(t('3 years')) ?></span></article>
            <article class="lp-cycle c2 lp-reveal" style="--d:.1s"><div class="big">📚</div><h3><?= e(t('Primary')) ?></h3><p><?= e(t('Six years of solid foundations in French, maths and sciences, up to the TENAFEP.')) ?></p><span class="tag"><?= e(t('6 years')) ?> · TENAFEP</span></article>
            <article class="lp-cycle c3 lp-reveal" style="--d:.2s"><div class="big">🎓</div><h3><?= e(t('Secondary')) ?></h3><p><?= e(t('Basic education (7th–8th) then four years of humanities, up to the State Exam.')) ?></p><span class="tag"><?= e(t('6 years')) ?> · <?= e(t('State Exam')) ?></span></article>
        </div>
    </div>
</section>

<!-- ============ Communauté ============ -->
<section class="lp-section" style="padding-top:0">
    <div class="lp-wrap lp-community">
        <div class="lp-reveal from-left">
            <span class="lp-eyebrow"><?= e(t('Community')) ?></span><br>
            <h2 class="lp-title"><?= $spark ?><?= e(t('Parents, teachers and students working together')) ?></h2>
            <p style="color:var(--muted)"><?= e(t('Messages, announcements and events keep the whole school community connected.')) ?></p>
            <a class="lp-btn lp-btn--dark" href="<?= url('contact') ?>"><?= e(t('Contact Us')) ?></a>
        </div>
        <div class="lp-bubbles lp-reveal from-right" aria-hidden="true">
            <span class="lp-av big">👩🏿‍🏫</span><span class="lp-av a1">👦🏿</span><span class="lp-av a2">👩🏾</span><span class="lp-av a3">👧🏿</span><span class="lp-av a4">👨🏿‍💼</span>
            <span class="lp-chat m1"><?= e(t('New results!')) ?></span><span class="lp-chat m2"><?= e(t('Welcome!')) ?></span><span class="lp-chat m3"><?= e(t('Meeting on Friday')) ?></span>
        </div>
    </div>
</section>

<!-- ============ Témoignages ============ -->
<section class="lp-section" style="padding-top:0">
    <div class="lp-wrap">
        <div class="lp-slider-head lp-reveal">
            <div><span class="lp-eyebrow"><?= e(t('Testimonials')) ?></span><br><h2 class="lp-title"><?= $spark ?><?= e(t('Satisfied families are our best ads')) ?></h2></div>
            <div class="lp-arrows"><button type="button" id="lpPrev" aria-label="←"><i class="fas fa-arrow-left"></i></button><button type="button" id="lpNext" aria-label="→"><i class="fas fa-arrow-right"></i></button></div>
        </div>
        <div class="lp-track lp-reveal" id="lpTrack">
            <?php foreach ([
                ['M', 'Mama Berthe', 'Parent', 'Thanks to the online space, I see my daughter\'s results and fees without leaving my office.'],
                ['J', 'Papa Joseph', 'Parent', 'The receipts are clear and I always know my remaining balance. Very reassuring.'],
                ['G', 'Mme Grâce', 'Teacher', 'Taking attendance and entering marks is quick, and results are published after review.'],
                ['P', 'Patience', 'Student', 'I check my results and my report card from my phone, even at home.'],
                ['L', 'M. Léon', 'Director of Studies', 'Reviewing and publishing results has never been so organised.'],
            ] as $q): ?>
                <figure class="lp-quote" style="margin:0"><span class="qm">❝</span><p><?= e(t($q[3])) ?></p>
                    <div class="who"><span class="av"><?= e($q[0]) ?></span><div><strong><?= e($q[1]) ?></strong><small><?= e(t($q[2])) ?></small><span class="lp-stars">★★★★★</span></div></div></figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ Actualités ============ -->
<?php if (!empty($news)): ?>
<section class="lp-section" style="padding-top:0">
    <div class="lp-wrap">
        <div class="lp-head lp-reveal"><span class="lp-eyebrow"><?= e(t('Stay Informed')) ?></span><br><h2 class="lp-title"><?= $spark ?><?= e(t('Latest News & Events')) ?></h2></div>
        <div class="lp-news">
            <?php foreach ($news as $i => $n): ?>
                <article class="lp-new lp-reveal" style="--d:<?= $i * .1 ?>s"><span class="d"><i class="fas fa-calendar-day me-1"></i><?= e(ldate('M d, Y', strtotime((string) $n['event_date']))) ?></span>
                    <h3><?= e($n['title']) ?></h3><p><?= e(mb_strimwidth((string) ($n['description'] ?? ''), 0, 120, '...')) ?></p></article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============ FAQ ============ -->
<section class="lp-section" style="padding-top:0">
    <div class="lp-wrap lp-faq">
        <div class="lp-reveal from-left">
            <span class="lp-eyebrow">FAQ</span><br>
            <h2 class="lp-title"><?= $spark ?><?= e(t('Got a question for us?')) ?></h2>
            <p style="color:var(--muted)"><?= e(t('If there are questions you want to ask, we will answer all your questions.')) ?></p>
            <form class="lp-ask" method="GET" action="<?= url('contact') ?>">
                <input type="email" name="email" placeholder="<?= e(t('Enter your email')) ?>" aria-label="<?= e(t('Email Address')) ?>">
                <button class="lp-btn lp-btn--dark" type="submit"><?= e(t('Submit')) ?></button>
            </form>
        </div>
        <div class="lp-qa lp-reveal from-right">
            <details open><summary><?= e(t('How do I enrol my child?')) ?></summary><p><?= e(t('Visit the Admissions page, bring the required documents to the school office and complete the registration.')) ?></p></details>
            <details><summary><?= e(t('How do I see my child\'s results?')) ?></summary><p><?= e(t('Sign in with the account given by the school: published results and report cards appear in your space.')) ?></p></details>
            <details><summary><?= e(t('How can I pay the school fees?')) ?></summary><p><?= e(t('At the cashier in cash, or by Mobile Money. A receipt is always issued.')) ?></p></details>
            <details><summary><?= e(t('Who can sign in?')) ?></summary><p><?= e(t('Teachers, students, parents, the prefect, the director of studies, the discipline director and the cashier all use the same login page.')) ?></p></details>
        </div>
    </div>
</section>

<!-- ============ Appel à l'action ============ -->
<section class="lp-section" style="padding-top:0">
    <div class="lp-wrap">
        <div class="lp-cta lp-reveal">
            <div><h2><?= e(t('Ready to begin learning with us?')) ?></h2><p><?= e(t('Sign in to your space or contact the school office.')) ?></p></div>
            <div class="d-flex flex-wrap gap-2"><a class="lp-btn lp-btn--white" href="<?= url('auth/login') ?>"><?= e(t('Sign In')) ?></a><a class="lp-btn lp-btn--white" href="<?= url('admissions') ?>"><?= e(t('Admissions')) ?></a></div>
        </div>
    </div>
</section>
<script src="<?= asset('js/landing.js') ?>"></script>
