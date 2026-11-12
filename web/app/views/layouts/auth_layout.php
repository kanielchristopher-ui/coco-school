<?php if (!defined('APP_VERSION')) { exit; } ?>
<!DOCTYPE html>
<html lang="<?= e(Lang::locale()) ?>" dir="<?= e(Lang::dir()) ?>" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(t(APP_NAME)) ?> - <?= e(settings('school_short_name', 'École')) ?></title>
    <?php
    $favicon = settings('school_favicon');
    $defaultFavicon = "data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%2064%2064'%3E%3Crect%20width='64'%20height='64'%20rx='14'%20fill='%230fa968'/%3E%3C/svg%3E";
    $faviconHref = ($favicon && is_file(ROOT_PATH . '/public/' . ltrim($favicon, '/'))) ? BASE_URL . '/' . e(ltrim($favicon, '/')) : $defaultFavicon;
    $v = fn(string $f) => '?v=' . (@filemtime(ROOT_PATH . '/public/assets/' . $f) ?: 0);
    ?>
    <link rel="icon" href="<?= $faviconHref ?>">
    <script src="<?= asset('js/theme.js') ?>"></script>
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/fontawesome/css/fontawesome.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/fontawesome/css/solid.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/style.css') . $v('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/auth.css') . $v('css/auth.css') ?>">
</head>
<body class="auth-body">
<span class="bubble b1"></span><span class="bubble b2"></span><span class="bubble b3"></span><span class="bubble b4"></span>

<main class="auth-shell">
    <a class="auth-back" href="<?= url('') ?>"><i class="fas fa-arrow-left"></i><span>Back to Home</span></a>

    <section class="auth-left" aria-hidden="true">
        <div class="auth-brand" style="margin-left:150px">
            <?php $logo = settings('school_logo'); ?>
            <span class="logo"><?php if ($logo && is_file(ROOT_PATH . '/public/' . $logo)): ?><img src="<?= BASE_URL . '/' . e($logo) ?>" alt=""><?php else: ?><i class="fas fa-graduation-cap"></i><?php endif; ?></span>
            <span><?= e(settings('school_name', APP_NAME)) ?></span>
        </div>
        <div class="auth-blob"><?php include __DIR__ . '/partials/school_art.php'; ?></div>
        <div class="auth-foot">&copy; <?= date('Y') ?> <?= e(settings('school_name', APP_NAME)) ?><br><?= e(t('One login for the whole school.')) ?></div>
    </section>

    <section class="auth-right">
        <div class="auth-top"><?= lang_switcher('inline') ?><?= theme_switcher() ?></div>
        <div class="auth-box">
            <?php flash(); ?>
            <?= $content ?? '' ?>
        </div>
    </section>
</main>
<script>
(function () {
    var btn = document.getElementById('togglePassword'), input = document.getElementById('password');
    if (btn && input) {
        btn.addEventListener('click', function () {
            var show = input.type === 'password'; input.type = show ? 'text' : 'password';
            btn.innerHTML = '<i class="fas ' + (show ? 'fa-eye-slash' : 'fa-eye') + '"></i>';
        });
    }
})();
</script>
</body>
</html>
