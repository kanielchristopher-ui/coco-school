<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Edit Subject';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold mb-0"><i class="fas fa-user-edit me-2 text-primary"></i>Edit Subject</h6>
    <a href="<?= url('subjects/index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<?php include __DIR__ . '/_form.php'; ?>
