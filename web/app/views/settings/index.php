<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Settings';
?>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0"><i class="fas fa-school me-2 text-primary"></i>School Information</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('settings/save') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">School Name</label>
                            <input type="text" name="school_name" class="form-control" value="<?= e($values['school_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Short Name</label>
                            <input type="text" name="school_short_name" class="form-control" value="<?= e($values['school_short_name'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Address</label>
                            <textarea name="school_address" class="form-control" rows="2"><?= e($values['school_address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Phone</label>
                            <input type="text" name="school_phone" class="form-control" value="<?= e($values['school_phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Email</label>
                            <input type="email" name="school_email" class="form-control" value="<?= e($values['school_email'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Website</label>
                            <input type="text" name="school_website" class="form-control" value="<?= e($values['school_website'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">School Motto</label>
                            <input type="text" name="school_motto" class="form-control" placeholder="e.g. Knowledge, Integrity, Excellence" value="<?= e($values['school_motto'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Principal Name</label>
                            <input type="text" name="principal_name" class="form-control" value="<?= e($values['principal_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Academic Year</label>
                            <input type="text" name="academic_year" class="form-control" placeholder="e.g. 2026 - 2027" value="<?= e($values['academic_year'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Currency Code</label>
                            <input type="text" name="currency" id="currencyCode" list="currencyPresets" class="form-control" value="<?= e($values['currency'] ?? 'USD') ?>">
                            <datalist id="currencyPresets">
                                <option value="USD">Dollar américain</option>
                                <option value="CDF">Franc congolais</option>
                            </datalist>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Currency Symbol</label>
                            <input type="text" name="currency_symbol" id="currencySymbol" class="form-control" value="<?= e($values['currency_symbol'] ?? '$') ?>">
                            <div class="form-text">USD = $ &nbsp;|&nbsp; CDF = FC</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Symbol Position</label>
                            <select name="currency_position" class="form-select">
                                <option value="after" <?= ($values['currency_position'] ?? 'after') === 'after' ? 'selected' : '' ?>>After the amount (1 500 FC)</option>
                                <option value="before" <?= ($values['currency_position'] ?? 'after') === 'before' ? 'selected' : '' ?>>Before the amount ($1,500)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Timezone</label>
                            <select name="timezone" class="form-select">
                                <?php foreach (['Africa/Kinshasa' => 'Africa/Kinshasa (UTC+1)', 'Africa/Lubumbashi' => 'Africa/Lubumbashi (UTC+2)', 'UTC' => 'UTC', 'Africa/Brazzaville' => 'Africa/Brazzaville', 'Africa/Nairobi' => 'Africa/Nairobi', 'Africa/Lagos' => 'Africa/Lagos', 'Europe/Paris' => 'Europe/Paris', 'Europe/Brussels' => 'Europe/Brussels', 'Europe/London' => 'Europe/London', 'America/New_York' => 'America/New_York'] as $tz => $tzLabel): ?>
                                    <option value="<?= $tz ?>" <?= ($values['timezone'] ?? 'Africa/Kinshasa') === $tz ? 'selected' : '' ?>><?= e($tzLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Academic Session</label>
                            <select name="academic_session_id" class="form-select">
                                <option value="">-- Select --</option>
                                <?php foreach ($sessions as $sess): ?>
                                    <option value="<?= (int) $sess['id'] ?>" <?= (int) ($values['academic_session_id'] ?? 0) === (int) $sess['id'] ? 'selected' : '' ?>><?= e($sess['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">School Logo</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <?php $logoVal = $values['school_logo'] ?? ''; if ($logoVal && is_file(ROOT_PATH . '/public/' . $logoVal)): ?>
                                <div class="mt-2 d-flex align-items-center gap-2">
                                    <img src="<?= BASE_URL . '/' . e($logoVal) ?>" alt="Logo" style="height:42px;width:42px;object-fit:contain;border:1px solid #dee2e6;border-radius:6px;">
                                    <span class="small text-muted"><?= e(basename($logoVal)) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Favicon</label>
                            <input type="file" name="favicon" class="form-control" accept=".png,.gif,.ico">
                            <?php $faviconVal = $values['school_favicon'] ?? ''; if ($faviconVal && is_file(ROOT_PATH . '/public/' . $faviconVal)): ?>
                                <div class="mt-2 d-flex align-items-center gap-2">
                                    <img src="<?= BASE_URL . '/' . e($faviconVal) ?>" alt="Favicon" style="height:24px;width:24px;object-fit:contain;border:1px solid #dee2e6;border-radius:4px;">
                                    <span class="small text-muted"><?= e(basename($faviconVal)) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-4"><i class="fas fa-save me-1"></i>Save Settings</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><h6 class="fw-bold mb-0"><i class="fas fa-sliders-h me-2 text-primary"></i>Preferences</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('settings/save') ?>">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small">Fee Due After (days)</label>
                            <input type="number" name="fee_due_days" class="form-control" value="<?= e($values['fee_due_days'] ?? '30') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Attendance Cutoff Time</label>
                            <input type="time" name="attendance_cutoff_time" class="form-control" value="<?= e($values['attendance_cutoff_time'] ?? '09:00') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Language</label>
                            <select name="language" class="form-select">
                                <?php $currentLang = ($values['language'] ?? '') !== '' ? $values['language'] : Lang::locale(); ?>
                                <?php foreach (Lang::available() as $code => $info): ?>
                                    <option value="<?= e($code) ?>" <?= $currentLang === $code ? 'selected' : '' ?>><?= e($info['native'] ?? $info['name'] ?? $code) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Default language of the whole system. New languages added in the app/lang folder appear here automatically.</div>
                        </div>
                    </div>
                    <button class="btn btn-outline-primary mt-4"><i class="fas fa-save me-1"></i>Save Preferences</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><h6 class="fw-bold mb-0"><i class="fas fa-puzzle-piece me-2 text-primary"></i>Module Management</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('settings/save') ?>">
                    <?= csrf_field() ?>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="enable_transport_module" value="1" id="enableTransportModule" <?= ($values['enable_transport_module'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="enableTransportModule">Enable Transport Module</label>
                    </div>
                    <p class="small text-muted mb-0">Shows or hides the Transport menu (Vehicles, Routes, Drivers &amp; Assignments) in the sidebar.</p>
                    <button class="btn btn-outline-primary mt-3"><i class="fas fa-save me-1"></i>Save Modules</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="fw-bold mb-0"><i class="fas fa-database me-2 text-danger"></i>Database</h6></div>
            <div class="card-body">
                <p class="small text-muted">Download a full SQL dump of your data, or restore from a previously downloaded backup.</p>
                <div class="d-flex gap-2 mb-3">
                    <a href="<?= url('settings/backup') ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-download me-1"></i>Download Backup</a>
                </div>
                <form method="POST" action="<?= url('settings/restore') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="input-group">
                        <input type="file" name="backup_file" class="form-control" accept=".sql" required>
                        <button class="btn btn-outline-danger"><i class="fas fa-upload me-1"></i>Restore</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
