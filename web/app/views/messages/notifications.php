<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Notifications';
$unreadCount = count(array_filter($notifications, fn($n) => empty($n['is_read'])));
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <a href="<?= url('messages/inbox') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Inbox</a>
    <span class="text-muted small"><?= count($notifications) ?> notification(s)
        <?php if ($unreadCount > 0): ?>&middot; <span class="badge bg-primary"><?= $unreadCount ?> new</span><?php endif; ?>
    </span>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body bg-soft-indigo rounded p-4">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="stat-icon bg-primary-subtle text-primary"><i class="fas fa-bell"></i></div>
            <div>
                <h5 class="mb-0 fw-bold">Notifications</h5>
                <small class="text-body d-block">Updates about your exams, results, attendance and fees.</small>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-primary bg-gradient text-white">
        <h6 class="mb-0 fw-semibold text-white"><i class="fas fa-bell me-2"></i>Notifications</h6>
    </div>
    <div class="list-group list-group-flush">
        <?php if (empty($notifications)): ?>
            <div class="list-group-item text-center py-5">
                <i class="fas fa-bell-slash fa-3x mb-3 d-block text-primary"></i>
                <h6 class="fw-semibold">No notifications yet.</h6>
                <p class="text-muted mb-0">New notifications will show up here.</p>
            </div>
        <?php endif; ?>
        <?php foreach ($notifications as $n): ?>
            <div class="list-group-item d-flex align-items-start gap-3 py-3 <?= !$n['is_read'] ? 'bg-soft-indigo' : '' ?>">
                <div class="icon-circle <?= $n['type'] === 'warning' ? 'bg-warning-subtle text-warning' : ($n['type'] === 'success' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary') ?>">
                    <i class="fas fa-<?= $n['type'] === 'warning' ? 'exclamation-triangle' : ($n['type'] === 'success' ? 'check-circle' : 'bell') ?>"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold d-flex align-items-center gap-2 flex-wrap">
                        <span><?= e($n['title'] ?? 'Notification') ?></span>
                        <?php if (!$n['is_read']): ?>
                            <span class="badge bg-primary">New</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($n['message'])): ?>
                        <div class="<?= $n['is_read'] ? 'text-muted' : 'text-body' ?> small"><?= e($n['message']) ?></div>
                    <?php endif; ?>
                    <div class="<?= $n['is_read'] ? 'text-muted' : 'text-body' ?> small mt-1"><i class="fas fa-clock me-1"></i><?= formatDate($n['created_at']) ?>
                        <?php if (!empty($n['link'])): ?>
                            &middot; <a href="<?= url($n['link']) ?>"><?= e(str_starts_with($n['link'], 'finance/my-receipt') ? 'View Receipt' : 'View') ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
