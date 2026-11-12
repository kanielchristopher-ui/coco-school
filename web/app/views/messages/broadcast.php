<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Broadcast Message';
?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Broadcast Announcement</h6>
                <a href="<?= url('messages/inbox') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Inbox</a>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('messages/broadcast-send') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label required">Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="e.g. Mid-term Exam Notice" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Message</label>
                        <textarea name="body" class="form-control" rows="6" placeholder="Write your announcement..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Audience</label>
                        <?php if ($isTeacher): ?>
                            <select name="class_id" class="form-select" required>
                                <option value="">Select a class (all students in it)</option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">As a teacher you can only announce to students in your assigned classes.</div>
                        <?php else: ?>
                            <div class="border rounded p-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="target" id="tgtAll" value="all" checked>
                                    <label class="form-check-label" for="tgtAll">All active users</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="target" id="tgtRole" value="role">
                                    <label class="form-check-label" for="tgtRole">Everyone with a role</label>
                                    <select name="role" class="form-select form-select-sm mt-1 ms-3 w-auto d-none" id="roleSelect">
                                        <option value="">-- Role --</option>
                                        <option value="teacher">Teachers</option>
                                        <option value="student">Students</option>
                                        <option value="parent">Parents</option>
                                        <option value="admin">Admins</option>
                                    </select>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="target" id="tgtClass" value="class">
                                    <label class="form-check-label" for="tgtClass">All students in a class</label>
                                    <select name="class_id" class="form-select form-select-sm mt-1 ms-3 w-auto d-none" id="classSelect">
                                        <option value="">-- Class --</option>
                                        <?php foreach ($classes as $c): ?>
                                            <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-bullhorn me-1"></i>Send Broadcast</button>
                        <a href="<?= url('messages/inbox') ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const all = document.getElementById('tgtAll');
    const role = document.getElementById('tgtRole');
    const cls = document.getElementById('tgtClass');
    const roleSel = document.getElementById('roleSelect');
    const classSel = document.getElementById('classSelect');
    if (!all) return;
    function toggle() {
        roleSel.classList.toggle('d-none', !role.checked);
        classSel.classList.toggle('d-none', !cls.checked);
    }
    all.addEventListener('change', toggle);
    role.addEventListener('change', toggle);
    cls.addEventListener('change', toggle);
    toggle();
});
</script>
