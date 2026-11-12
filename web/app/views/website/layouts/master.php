<?php if (!defined('APP_VERSION')) { exit; }
include __DIR__ . '/header.php';
?>
<main>
<?php flash(); ?>
<?= $content ?? '' ?>
</main>
<?php include __DIR__ . '/footer.php'; ?>
