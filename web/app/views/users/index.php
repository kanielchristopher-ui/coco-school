<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Users & Roles';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <span class="text-muted small"><?= count($users) ?> user(s)</span>
    <div class="d-flex gap-2">
        <a href="<?= url('users/roles') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-user-shield me-1"></i>Roles & Permissions</a>
        <a href="<?= url('users/create') ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add User</a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('users/index') ?>" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control" placeholder="Search name, username or email..." value="<?= e($search) ?>">
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
                <a href="<?= url('users/index') ?>" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>User</th><th>Username</th><th>Role</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No users found.</td></tr>
                <?php endif; ?>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?= avatar($u['photo'] ?? null, ($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''), 32) ?>
                                <div>
                                    <strong><?= e(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''))) ?></strong>
                                    <div class="small text-muted"><?= e($u['email'] ?? '') ?></div>
                                </div>
                            </div>
                        </td>
                        <td>@<?= e($u['username'] ?? '') ?></td>
                        <td><?= e($u['role_name'] ?? '—') ?></td>
                        <td><?= statusBadge($u['status']) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="<?= url('users/edit/' . $u['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                            <?php if ((int) $u['id'] !== Auth::id()): ?>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('users/delete/' . $u['id']) ?>')" title="Delete"><i class="fas fa-trash"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
