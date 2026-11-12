<?php if (!defined('APP_VERSION')) { exit; } ?>
<div class="text-center py-5">
    <div class="display-1 text-danger"><i class="fas fa-exclamation-triangle"></i></div>
    <h2 class="mt-3">403 - Forbidden</h2>
    <p class="text-muted"><?= e($message ?? 'You do not have permission to access this resource.') ?></p>
    <a href="<?= url('dashboard/index') ?>" class="btn btn-primary mt-2"><i class="fas fa-home me-1"></i> Back to Dashboard</a>
</div>
