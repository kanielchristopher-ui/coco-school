<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Edit Class';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold mb-0"><i class="fas fa-user-edit me-2 text-primary"></i>Edit Class</h6>
    <a href="<?= url('classes/manage/' . $class['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-cog me-1"></i>Manage</a>
</div>
<?php include __DIR__ . '/_form.php'; ?>
