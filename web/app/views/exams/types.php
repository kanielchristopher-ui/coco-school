<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Exam Types';
?>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="fw-bold mb-0">Exam Types</h6></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Name</th><th>Description</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-4">No exam types.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($rows as $t): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($t['name']) ?></td>
                                <td class="small"><?= e($t['description'] ?: '—') ?></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('exams/destroy-type/' . $t['id']) ?>')"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="fw-bold mb-0">Add Exam Type</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('exams/store-type') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-2"><input type="text" name="name" class="form-control" placeholder="e.g. Weekly Test" required></div>
                    <div class="mb-3"><textarea name="description" class="form-control" placeholder="Description (optional)"></textarea></div>
                    <button class="btn btn-primary w-100">Add</button>
                </form>
            </div>
        </div>
    </div>
</div>
