<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Add Teacher';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold mb-0"><i class="fas fa-user-plus me-2 text-primary"></i>New Teacher</h6>
    <a href="<?= url('teachers/index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php include __DIR__ . '/_form.php'; ?>
    </div>
</div>
