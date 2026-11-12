<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Compose Message';
?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-pen me-2"></i>New Message</h6>
                <a href="<?= url('messages/inbox') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Inbox</a>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('messages/send') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label required">Recipient</label>
                        <select name="receiver_id" class="form-select" required>
                            <option value="">Select User</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= (int) $u['id'] ?>"><?= e(trim($u['first_name'] . ' ' . $u['last_name'])) ?> (@<?= e($u['username']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="Subject (optional)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Message</label>
                        <textarea name="body" class="form-control" rows="6" placeholder="Write your message..." required></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Send Message</button>
                        <a href="<?= url('messages/inbox') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
