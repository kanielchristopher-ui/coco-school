<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Teacher Attendance';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold mb-0"><i class="fas fa-user-check me-2 text-primary"></i>Teacher Attendance</h6>
    <a href="<?= url('attendance/index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Student Attendance</a>
</div>

<form method="GET" action="<?= url('attendance/teacher') ?>" class="row g-2 mb-3">
    <div class="col-md-3">
        <input type="date" name="date" class="form-control" value="<?= e($date) ?>" required>
    </div>
    <div class="col-md-1"><button class="btn btn-outline-primary w-100"><i class="fas fa-search"></i></button></div>
</form>

<?php if (!empty($teachers)): ?>
    <form method="POST" action="<?= url('attendance/store-teacher') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="date" value="<?= e($date) ?>">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="fw-bold mb-0"><?= count($teachers) ?> teacher(s) - <?= formatDate($date) ?></h6></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Official ID</th><th>Teacher</th><th>Department</th><th>Subject</th><th class="text-center">Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teachers as $t): ?>
                            <?php $ex = $existing[$t['id']] ?? null; ?>
                            <tr>
                                <td class="text-center text-muted"><?= e($t['official_id'] ?: '—') ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?= avatar($t['photo'] ?? null, $t['first_name'] . ' ' . $t['last_name'], 32) ?>
                                        <strong><?= e($t['first_name'] . ' ' . $t['last_name']) ?></strong>
                                    </div>
                                </td>
                                <td><?= e($t['department'] ?: '—') ?></td>
                                <td><?= e($t['subjects'] ?: '—') ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php foreach (['present', 'absent', 'late', 'leave'] as $status): ?>
                                            <input type="radio" class="btn-check" name="status[<?= $t['id'] ?>]" id="t-<?= $t['id'] ?>-<?= $status ?>"
                                                   value="<?= $status ?>" <?= ($ex && $ex['status'] === $status) || (!$ex && $status === 'present') ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-<?= match ($status) { 'present' => 'success', 'absent' => 'danger', 'late' => 'warning', 'leave' => 'info' } ?>" for="t-<?= $t['id'] ?>-<?= $status ?>">
                                                <?= ucfirst($status) ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white"><button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button></div>
        </div>
    </form>
<?php else: ?>
    <div class="alert alert-warning">No active teachers found.</div>
<?php endif; ?>
