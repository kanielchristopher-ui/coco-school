<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Inbox';
$unread = count(array_filter($messages, fn($m) => !$m['is_read']));
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <span class="text-muted small"><?= count($messages) ?> message(s)<?= $unread ? ' &bull; ' . $unread . ' unread' : '' ?></span>
    <div class="d-flex gap-2">
        <a href="<?= url('messages/notifications') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-bell me-1"></i>Notifications</a>
        <a href="<?= url('messages/broadcast') ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-bullhorn me-1"></i>Broadcast</a>
        <a href="<?= url('messages/compose') ?>" class="btn btn-sm btn-primary"><i class="fas fa-pen me-1"></i>Compose</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>From</th><th>Subject</th><th>Preview</th><th>Date</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Your inbox is empty.</td></tr>
                <?php endif; ?>
                <?php foreach ($messages as $m): ?>
                    <tr class="<?= !$m['is_read'] ? 'table-primary' : '' ?>">
                        <td class="text-nowrap">
                            <div class="d-flex align-items-center gap-2">
                                <?= avatar(null, $m['sender_first'] ?? 'U', 32) ?>
                                <div>
                                    <strong><?= e(trim(($m['sender_first'] ?? '') . ' ' . ($m['sender_last'] ?? ''))) ?></strong>
                                    <?php if (!$m['is_read']): ?><span class="badge bg-primary ms-1">New</span><?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><a href="<?= url('messages/read/' . $m['id']) ?>" class="text-decoration-none fw-semibold"><?= e($m['subject'] ?: '(no subject)') ?></a></td>
                        <td class="text-muted text-truncate" style="max-width: 260px;"><?= e($m['body']) ?></td>
                        <td class="text-nowrap small text-muted"><?= formatDate($m['created_at']) ?></td>
                        <td class="text-end">
                            <a href="<?= url('messages/read/' . $m['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
