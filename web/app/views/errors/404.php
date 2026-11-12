<?php if (!defined('APP_VERSION')) { exit; } ?>
<div class="text-center py-5">
    <div class="display-1 text-warning"><i class="fas fa-compass"></i></div>
    <h2 class="mt-3">404 - Page Not Found</h2>
    <p class="text-muted"><?= e($message ?? 'The page you are looking for does not exist.') ?></p>
    <a href="<?= url('dashboard/index') ?>" class="btn btn-primary mt-2"><i class="fas fa-home me-1"></i> Back to Dashboard</a>
</div>
