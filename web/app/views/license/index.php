<?php
$pageTitle = 'License';
$levels = ['maternel' => 'Nursery', 'primaire' => 'Primary', 'secondaire' => 'Secondary'];
$st = $check?->status;
$lic = $check?->license;
$tone = ['ok' => 'success', 'warning' => 'warning', 'danger' => 'danger'][$st?->severity() ?? 'warning'];
?>
<?php if ($technicalError): ?>
    <div class="alert alert-warning"><i class="fas fa-triangle-exclamation me-2"></i><?= e(t('License module unavailable: apply the database migrations (0004).')) ?> <small class="d-block text-muted"><?= e($technicalError) ?></small></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="stat-icon tone-<?= $tone ?>"><i class="fas fa-<?= $tone === 'success' ? 'shield-halved' : ($tone === 'warning' ? 'hourglass-half' : 'lock') ?>"></i></span>
                    <div>
                        <h5 class="mb-0"><?= $st ? e(t($st->label())) : e(t('Unknown')) ?></h5>
                        <?php if ($check && $check->detail !== ''): ?><small class="text-muted"><?= e($check->detail) ?></small><?php endif; ?>
                    </div>
                </div>
                <?php if ($lic): ?>
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4"><?= e(t('License holder')) ?></dt><dd class="col-sm-8"><?= e($lic->holder) ?></dd>
                        <dt class="col-sm-4"><?= e(t('License ID')) ?></dt><dd class="col-sm-8"><code><?= e($lic->id) ?></code></dd>
                        <dt class="col-sm-4"><?= e(t('Valid from')) ?></dt><dd class="col-sm-8"><?= e(formatDate($lic->validFrom->format('Y-m-d'))) ?></dd>
                        <dt class="col-sm-4"><?= e(t('Valid until')) ?></dt><dd class="col-sm-8"><?= e(formatDate($lic->validUntil->format('Y-m-d'))) ?>
                            <?php if ($check->daysLeft !== null): ?> <span class="badge bg-<?= $tone ?>-subtle text-<?= $tone ?>"><?= e(t(':n day(s) left', ['n' => max(0, $check->daysLeft)])) ?></span><?php endif; ?></dd>
                        <dt class="col-sm-4"><?= e(t('Grace period')) ?></dt><dd class="col-sm-8"><?= e(t(':n day(s)', ['n' => $lic->graceDays])) ?></dd>
                        <dt class="col-sm-4"><?= e(t('Edition')) ?></dt><dd class="col-sm-8"><?= e($lic->edition) ?></dd>
                    </dl>
                <?php elseif ($st && in_array($st->value, ['trial', 'trial_expired'], true)): ?>
                    <p class="mb-0 small text-muted"><?= e(t('No license key installed: trial period of :n days, limited to :m students per level.', ['n' => \CocoCore\Licensing\LicenseManager::TRIAL_DAYS, 'm' => \CocoCore\Licensing\LicenseManager::TRIAL_MAX_STUDENTS])) ?></p>
                    <?php if ($check->daysLeft !== null): ?><p class="mt-2 mb-0"><span class="badge bg-<?= $tone ?>-subtle text-<?= $tone ?>"><?= e(t(':n day(s) left', ['n' => max(0, $check->daysLeft)])) ?></span></p><?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="fw-bold mb-0"><?= e(t('Covered levels and student limits')) ?></h6></div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th><?= e(t('Level')) ?></th><th><?= e(t('Covered')) ?></th><th><?= e(t('Active students')) ?></th><th><?= e(t('Limit')) ?></th></tr></thead>
                    <tbody>
                    <?php foreach ($levels as $code => $label):
                        $covered = $lic ? $lic->allowsLevel($code) : true;
                        $limit = $lic ? $lic->studentLimit($code) : \CocoCore\Licensing\LicenseManager::TRIAL_MAX_STUDENTS;
                        $used = $usage[$code] ?? 0; ?>
                        <tr>
                            <td><strong><?= e(t($label)) ?></strong></td>
                            <td><?= $covered ? '<span class="badge bg-success-subtle text-success">' . e(t('Yes')) . '</span>' : '<span class="badge bg-danger-subtle text-danger">' . e(t('No')) . '</span>' ?></td>
                            <td><?= nf($used) ?></td>
                            <td><?= $limit > 0 ? nf($limit) : e(t('Unlimited')) ?>
                                <?php if ($limit > 0): ?><div class="progress mt-1" style="height:6px"><div class="progress-bar bg-<?= $used >= $limit ? 'danger' : 'primary' ?>" style="width:<?= min(100, (int) round($used / $limit * 100)) ?>%"></div></div><?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card mb-4">
            <div class="card-header"><h6 class="fw-bold mb-0"><?= e(t('Install a license key')) ?></h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('license/install') ?>">
                    <?= csrf_field() ?>
                    <label class="form-label"><?= e(t('License key')) ?></label>
                    <textarea name="token" rows="5" class="form-control font-monospace small" placeholder="CCL1.…" required></textarea>
                    <div class="form-text"><?= e(t('The key is verified offline with the provider\'s public key. Never share it.')) ?></div>
                    <button class="btn btn-primary mt-3 w-100"><i class="fas fa-key me-1"></i><?= e(t('Verify and install')) ?></button>
                </form>
            </div>
        </div>
        <?php if ($history): ?>
            <div class="card">
                <div class="card-header"><h6 class="fw-bold mb-0"><?= e(t('Installed licenses')) ?></h6></div>
                <div class="card-body">
                    <?php foreach ($history as $h): ?>
                        <div class="list-row">
                            <div class="flex-grow-1"><strong><?= e($h['holder']) ?></strong><div class="small text-muted"><code><?= e($h['license_id']) ?></code> · <?= e(formatDate($h['valid_from'])) ?> → <?= e(formatDate($h['valid_until'])) ?></div></div>
                            <?= statusBadge($h['status']) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
