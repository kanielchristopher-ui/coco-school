<?php $pageTitle = 'Report Card Extensions'; ?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h6 class="fw-bold mb-0"><i class="fas fa-puzzle-piece me-2 text-primary"></i>Install an extension (.ccplg)</h6></div>
            <div class="card-body">
                <?php if (!$sodium || !$zip): ?><div class="alert alert-warning">PHP extensions « sodium » and « zip » are required to install packages.</div><?php endif; ?>
                <form method="POST" action="<?= url('bulletins/install-extension') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="file" name="package" accept=".ccplg" class="form-control mb-3" required>
                    <button class="btn btn-primary w-100" <?= (!$sodium || !$zip) ? 'disabled' : '' ?>><i class="fas fa-upload me-1"></i>Verify and install</button>
                </form>
                <ul class="small text-muted mt-3 mb-0">
                    <li>The package signature is checked with the server's trusted key.</li>
                    <li>Only declarative HTML/CSS templates are accepted — no PHP, JavaScript or SQL is ever run.</li>
                    <li>Parents and students cannot install or create extensions.</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header"><h6 class="fw-bold mb-0">Installed extensions</h6></div>
            <div class="table-responsive"><table class="table align-middle">
                <thead><tr><th>Extension</th><th>Version</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php if (!$installed): ?><tr><td colspan="4" class="text-muted text-center py-4">No extensions installed.</td></tr><?php endif; ?>
                <?php foreach ($installed as $x): ?>
                    <tr>
                        <td><strong><?= e($x['name']) ?></strong><div class="small text-muted"><?= e($x['plugin_id']) ?></div></td>
                        <td>v<?= e($x['version']) ?></td>
                        <td><?= statusBadge($x['status'] === 'active' ? 'active' : 'inactive') ?></td>
                        <td class="text-end text-nowrap">
                            <form method="POST" action="<?= url('bulletins/extension-status/' . (int) $x['id'] . '/' . ($x['status'] === 'active' ? 'disabled' : 'active')) ?>" class="d-inline"><?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-secondary"><?= e(t($x['status'] === 'active' ? 'Disable' : 'Enable')) ?></button></form>
                            <form method="POST" action="<?= url('bulletins/remove-extension/' . (int) $x['id']) ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this record? This action cannot be undone.')"><?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        </div>
        <div class="card"><div class="card-header"><h6 class="fw-bold mb-0">Available templates</h6></div><div class="card-body">
            <?php foreach ($catalog as $t): ?><span class="badge bg-primary-subtle text-primary me-2 mb-2"><?= e($t['label']) ?></span><?php endforeach; ?>
        </div></div>
    </div>
</div>
