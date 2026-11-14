<?php
// Public website header / navbar. Variables available from the controller:
// $pageTitle, $pageDescription, $activeNav.
$siteName    = settings('school_name', APP_NAME);
$shortName   = settings('school_short_name', 'EP');
$motto       = settings('school_motto', 'Savoir, Intégrité, Excellence');
$active      = $activeNav ?? '';
$isHome      = $active === 'home';
$metaTitle   = t($pageTitle ?? ($siteName . ' | Quality Education'));
$metaDesc    = t($pageDescription ?? ($siteName . ' provides quality education and prepares students for a successful future.'));
?>
<!DOCTYPE html>
<html lang="<?= e(Lang::locale()) ?>" dir="<?= e(Lang::dir()) ?>" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($metaTitle) ?></title>
    <meta name="description" content="<?= e($metaDesc) ?>">
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/logo.svg') ?>">
    <script src="<?= asset('js/theme.js') ?>"></script>
    <link rel="stylesheet" href="<?= asset('css/website.css') . '?v=' . (@filemtime(ROOT_PATH . '/public/assets/css/website.css') ?: 0) ?>">
    <link rel="stylesheet" href="<?= asset('vendor/fontawesome/css/fontawesome.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/fontawesome/css/solid.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/landing.css') . '?v=' . (@filemtime(ROOT_PATH . '/public/assets/css/landing.css') ?: 0) ?>">
</head>
<body class="<?= $isHome ? 'ws-page--home' : '' ?>">

<header class="ws-header" id="wsHeader">
    <div class="ws-container ws-header-inner">
        <a class="ws-brand" href="<?= url('') ?>" aria-label="<?= e($siteName) ?> home">
            <img class="ws-logo" src="<?= asset('images/logo.svg') ?>" alt="<?= e($shortName) ?> logo">
            <span class="ws-brand-text">
                <span class="ws-brand-name"><?= e($siteName) ?></span>
                <span class="ws-brand-motto"><?= e($motto) ?></span>
            </span>
        </a>

        <button class="ws-nav-toggle" id="wsNavToggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="wsNav">
            <span></span><span></span><span></span>
        </button>

        <nav class="ws-nav" id="wsNav" aria-label="Main navigation">
            <ul>
                <li><a href="<?= url('') ?>" class="<?= $isHome ? 'is-active' : '' ?>">Home</a></li>
                <li><a href="<?= url('about') ?>" class="<?= $active === 'about' ? 'is-active' : '' ?>">About</a></li>
                <li><a href="<?= url('academics') ?>" class="<?= $active === 'academics' ? 'is-active' : '' ?>">Academics</a></li>
                <li><a href="<?= url('admissions') ?>" class="<?= $active === 'admissions' ? 'is-active' : '' ?>">Admissions</a></li>
                <li><a href="<?= url('teachers') ?>" class="<?= $active === 'teachers' ? 'is-active' : '' ?>">Teachers</a></li>
                <li><a href="<?= url('news') ?>" class="<?= $active === 'news' ? 'is-active' : '' ?>">News</a></li>
                <li><a href="<?= url('contact') ?>" class="<?= $active === 'contact' ? 'is-active' : '' ?>">Contact</a></li>
                <li class="ws-lang"><?= lang_switcher('inline') ?></li>
                <li class="ws-theme"><?= theme_switcher() ?></li>
                <li class="ws-nav-cta"><a href="<?= url('auth/login') ?>" class="ws-btn ws-btn-gold ws-btn-sm"><i class="fas fa-right-to-bracket"></i> <?= e(t('Sign In')) ?></a></li>
            </ul>
        </nav>
    </div>
</header>
