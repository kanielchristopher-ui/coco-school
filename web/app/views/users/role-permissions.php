<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Permissions - ' . ($role['name'] ?? 'Role');
$assigned = $assigned ?? [];
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <a href="<?= url('users/roles') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Roles</a>
        <h5 class="mt-2 mb-0">Role: <span class="badge bg-primary-subtle text-primary fs-6"><?= e($role['name']) ?></span></h5>
    </div>
    <span class="text-muted small"><?= count($assigned) ?> permission(s) assigned</span>
</div>

<form method="POST" action="<?= url('users/save-role-permissions/' . $role['id']) ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
        <?php foreach ($grouped as $module => $perms): ?>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-th-large me-2"></i><?= e(ucwords(str_replace('_', ' ', $module))) ?></h6>
                    </div>
                    <div class="card-body py-2">
                        <?php foreach ($perms as $p): ?>
                            <?php $checked = in_array((int) $p['id'], $assigned, true); ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= (int) $p['id'] ?>" id="perm-<?= (int) $p['id'] ?>" <?= $checked ? 'checked' : '' ?>>
                                <label class="form-check-label" for="perm-<?= (int) $p['id'] ?>">
                                    <?= e($p['name']) ?>
                                    <code class="text-muted small"><?= e($p['slug']) ?></code>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="<?= url('users/roles') ?>" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Permissions</button>
    </div>
</form>
