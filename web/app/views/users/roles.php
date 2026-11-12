<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Roles & Permissions';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <a href="<?= url('users/index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Users</a>
    <span class="text-muted small"><?= count($roles) ?> role(s)</span>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Role</th><th>Description</th><th>Users</th><th>Permissions</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($roles)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No roles defined.</td></tr>
                <?php endif; ?>
                <?php foreach ($roles as $r): ?>
                    <tr>
                        <td>
                            <span class="badge bg-primary-subtle text-primary fs-6"><?= e($r['name']) ?></span>
                        </td>
                        <td class="text-muted"><?= e($r['description'] ?: '—') ?></td>
                        <td><span class="badge bg-light text-dark"><?= (int) $r['user_count'] ?></span></td>
                        <td><span class="badge bg-light text-dark"><?= (int) $r['permission_count'] ?></span></td>
                        <td class="text-end">
                            <a href="<?= url('users/role-permissions/' . $r['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-user-shield me-1"></i>Manage Permissions</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
