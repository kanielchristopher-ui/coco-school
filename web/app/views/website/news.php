<?php
include __DIR__ . '/layouts/icons.php';
$siteName = settings('school_name', APP_NAME);
?>

<section class="ws-page-hero">
    <div class="ws-page-hero-bg" aria-hidden="true"></div>
    <div class="ws-container">
        <p class="ws-eyebrow ws-eyebrow--light">News &amp; Events</p>
        <h1>Latest News &amp; Events</h1>
        <p>Stay up to date with what is happening at <?= e($siteName) ?>.</p>
    </div>
</section>

<section class="ws-section">
    <div class="ws-container">
        <?php if (empty($news)): ?>
            <div class="ws-empty">No news or events have been published yet. Please check back soon.</div>
        <?php else: ?>
            <?php
            $featured = array_shift($news);
            ?>
            <div class="ws-grid ws-grid--2">
                <article class="ws-card ws-news ws-news--featured ws-reveal">
                    <div class="ws-news-date-block">
                        <strong><?= e(date('d', strtotime((string) $featured['event_date']))) ?></strong>
                        <span><?= e(ldate('M', strtotime((string) $featured['event_date']))) ?></span>
                        <span class="ws-news-year"><?= e(date('Y', strtotime((string) $featured['event_date']))) ?></span>
                    </div>
                    <div class="ws-news-body">
                        <p class="ws-news-chip"><?= e(ucfirst((string) ($featured['event_type'] ?? 'event'))) ?></p>
                        <h2><?= e($featured['title']) ?></h2>
                        <?php if (!empty($featured['location'])): ?>
                            <p class="ws-news-location"><?php ws_icon('map-pin'); ?><?= e($featured['location']) ?></p>
                        <?php endif; ?>
                        <p><?= e($featured['description'] ?? '') ?></p>
                        <button type="button" class="ws-link ws-news-more"
                                data-event-title="<?= e($featured['title']) ?>"
                                data-event-date="<?= e(ldate('M d, Y', strtotime((string) $featured['event_date']))) ?>"
                                data-event-location="<?= e($featured['location'] ?? '') ?>"
                                data-event-description="<?= e($featured['description'] ?? '') ?>">Read Full Story <?php ws_icon('arrow-right'); ?></button>
                    </div>
                </article>

                <?php foreach ($news as $n): ?>
                <article class="ws-card ws-news ws-reveal">
                    <div class="ws-news-meta">
                        <span class="ws-news-chip"><?= e(ucfirst((string) ($n['event_type'] ?? 'event'))) ?></span>
                        <span class="ws-news-date"><?php ws_icon('calendar'); ?><?= e(ldate('M d, Y', strtotime((string) $n['event_date']))) ?></span>
                    </div>
                    <h2><?= e($n['title']) ?></h2>
                    <p><?= e(mb_strimwidth((string) ($n['description'] ?? ''), 0, 140, '...')) ?></p>
                    <button type="button" class="ws-link ws-news-more"
                            data-event-title="<?= e($n['title']) ?>"
                            data-event-date="<?= e(ldate('M d, Y', strtotime((string) $n['event_date']))) ?>"
                            data-event-location="<?= e($n['location'] ?? '') ?>"
                            data-event-description="<?= e($n['description'] ?? '') ?>">Read More <?php ws_icon('arrow-right'); ?></button>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
