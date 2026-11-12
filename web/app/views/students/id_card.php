<?php if (!defined('APP_VERSION')) { exit; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student ID Card - <?= e($student['first_name']) ?></title>
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <style>
        body { background: #e9ecef; font-family: 'Segoe UI', sans-serif; }
        .id-card { width: 340px; margin: 40px auto; border-radius: 16px; overflow: hidden;
                   background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,.2); border: 2px solid #0d6efd; }
        .id-head { background: #0d6efd; color: #fff; padding: 14px 16px; display: flex;
                   align-items: center; justify-content: space-between; }
        .id-body { padding: 16px; text-align: center; }
        .barcode { font-family: 'Libre Barcode 39', monospace; font-size: 22px; letter-spacing: 2px; }
        .qr { width: 70px; height: 70px; }
        .id-foot { background: #f8f9fa; padding: 10px 16px; font-size: 11px; text-align: center; }
        @media print { body { background:#fff; } .no-print { display:none; } .id-card { box-shadow:none; } }
    </style>
</head>
<body>
    <div class="text-center mt-3 no-print">
        <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Print ID Card</button>
        <a href="<?= url('students/show/' . $student['id']) ?>" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="id-card">
        <div class="id-head">
            <div>
                <div class="fw-bold"><?= e(settings('school_name', 'School')) ?></div>
                <small><?= e(settings('school_address', '')) ?></small>
            </div>
            <?php $logo = settings('school_logo'); if ($logo && is_file(ROOT_PATH . '/public/' . $logo)): ?>
                <img src="<?= BASE_URL . '/' . e($logo) ?>" width="36" height="36" style="object-fit:contain; background:#fff; border-radius:50%;">
            <?php else: ?>
                <i class="fas fa-graduation-cap fa-2x"></i>
            <?php endif; ?>
        </div>

        <div class="id-body">
            <div class="mb-3">
                <?= avatar($student['photo'], $student['first_name'] . ' ' . $student['last_name'], 100) ?>
            </div>
            <h5 class="mb-0 fw-bold"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h5>
            <div class="badge bg-primary mb-2"><?= e($student['class_name'] ?: '—') ?><?= $student['section_name'] ? ' / ' . e($student['section_name']) : '' ?></div>

            <table class="table table-sm small mb-2 text-start">
                <tr><td class="text-muted">Official ID</td><td class="fw-semibold"><?= e($student['official_id'] ?: '—') ?></td></tr>
                <tr><td class="text-muted">Admission No</td><td class="fw-semibold"><?= e($student['admission_no'] ?: '—') ?></td></tr>
                <tr><td class="text-muted">Blood Group</td><td class="fw-semibold"><?= e($student['blood_group'] ?: '—') ?></td></tr>
                <tr><td class="text-muted">Guardian</td><td class="fw-semibold"><?= e($student['guardian_name'] ?: '—') ?></td></tr>
                <tr><td class="text-muted">Student Phone</td><td class="fw-semibold"><?= e($student['phone'] ?: '—') ?></td></tr>
                <tr><td class="text-muted">Guardian Phone</td><td class="fw-semibold"><?= e($student['guardian_phone'] ?: '—') ?></td></tr>
            </table>

            <div class="d-flex justify-content-center align-items-center gap-3 mb-2">
                <!-- Simulated QR code -->
                <div class="qr" style="display:grid;grid-template-columns:repeat(8,1fr);gap:1px;">
                    <?php
                    $seed = md5((string) ($student['official_id'] ?? (string) $student['id']));
                    for ($i = 0; $i < 64; $i++) {
                        $black = hexdec($seed[$i % 32] ?? '0') > 7;
                        echo '<div style="width:100%;aspect-ratio:1;background:' . ($black ? '#000' : '#fff') . ';"></div>';
                    }
                    ?>
                </div>
                <div>
                    <div class="barcode"><?= preg_replace('/[^A-Z0-9]/', '', (string) ($student['official_id'] ?? (string) $student['id'])) ?></div>
                    <small class="text-muted">ID: <?= e($student['official_id'] ?: '—') ?></small>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-end mt-3 pt-2" style="border-top:1px dashed #dee2e6;">
                <div class="text-center">
                    <div style="border-top:1px solid #6c757d; width:110px;"></div>
                    <small class="text-muted">Student Signature</small>
                </div>
                <div class="text-center">
                    <div class="mx-auto mb-1" style="width:42px;height:42px;border:1.5px dashed #adb5bd;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#adb5bd;">
                        <i class="fas fa-stamp"></i>
                    </div>
                    <small class="text-muted">Authorized Signature</small>
                </div>
            </div>
        </div>

        <div class="id-foot text-muted">
            Valid for the academic year <?= e(settings('academic_year', date('Y'))) ?><br>
            <?= e(settings('school_phone', '')) ?> &middot; <?= e(settings('school_email', '')) ?>
        </div>
    </div>

    <script src="<?= asset('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
