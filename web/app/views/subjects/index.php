<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Subjects';
$canManage = $canManage ?? false;
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small"><?= count($rows) ?> subject(s)</span>
    <?php if ($canManage): ?>
        <a href="<?= url('subjects/create') ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add Subject</a>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Subject</th><th>Code</th><th>Class</th><th>Teacher</th><th>Weekly Hours</th><?php if ($canManage): ?><th class="text-end">Actions</th><?php endif; ?></tr></thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No subjects found.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $sub): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($sub['name']) ?></td>
                        <td><?= e($sub['code'] ?: '—') ?></td>
                        <td><?= e($sub['class_name'] ?: '—') ?></td>
                        <td><?= $sub['teacher_first'] ? e($sub['teacher_first'] . ' ' . $sub['teacher_last']) : '—' ?></td>
                        <td><?= $sub['credits'] !== null ? e((string) $sub['credits']) : '—' ?></td>
                        <?php if ($canManage): ?>
                        <td class="text-end text-nowrap">
                            <a href="<?= url('subjects/edit/' . $sub['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('subjects/destroy/' . $sub['id']) ?>')"><i class="fas fa-trash"></i></button>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
