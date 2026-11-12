<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Conversation';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <a href="<?= url('messages/inbox') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Inbox</a>
    <span class="text-muted small"><?= count($messages) ?> message(s)</span>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body" style="max-height: 480px; overflow-y: auto;">
        <?php foreach ($messages as $m): ?>
            <?php $mine = (int) $m['sender_id'] === Auth::id(); ?>
            <div class="d-flex <?= $mine ? 'justify-content-end' : 'justify-content-start' ?> mb-3">
                <div class="<?= $mine ? 'bg-primary text-white' : 'bg-light' ?> rounded-3 px-3 py-2" style="max-width: 75%;">
                    <?php if (!$mine): ?>
                        <div class="small fw-bold mb-1"><?= e(trim(($m['sender_first'] ?? '') . ' ' . ($m['sender_last'] ?? ''))) ?></div>
                    <?php endif; ?>
                    <div class="mb-1">
                        <?php if (!empty($m['subject'])): ?><strong><?= e($m['subject']) ?></strong><br><?php endif; ?>
                        <?= nl2br(e($m['body'])) ?>
                    </div>
                    <div class="small <?= $mine ? 'text-white-50' : 'text-muted' ?> text-end"><?= formatDate($m['created_at']) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url('messages/reply/' . $otherId) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="subject" value="Re: <?= e($messages[0]['subject'] ?? '') ?>">
            <div class="mb-2">
                <label class="form-label">Reply</label>
                <textarea name="body" class="form-control" rows="3" placeholder="Type your reply..." required></textarea>
            </div>
            <div class="d-flex justify-content-end">
                <button class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Send Reply</button>
            </div>
        </form>
    </div>
</div>
