<?php if (!defined('APP_VERSION')) { exit; }
$logo = settings('school_logo');
$logoPath = $logo ? (ROOT_PATH . '/public/' . ltrim($logo, '/')) : '';
$academicYear = settings('academic_year', date('Y'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student ID Cards</title>
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/fontawesome/css/fontawesome.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/fontawesome/css/solid.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/fontawesome/css/regular.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/fontawesome/css/brands.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <style>
        body { background: #e9ecef; font-family: 'Segoe UI', sans-serif; }
        .no-print { text-align: center; margin: 20px 0; }
        .cards-wrap { display: flex; flex-wrap: wrap; gap: 18px; justify-content: center; padding: 0 12px 40px; }
        .id-card { width: 340px; border-radius: 16px; overflow: hidden; background: #fff;
                   box-shadow: 0 10px 30px rgba(0,0,0,.2); border: 2px solid #0d6efd; }
        .id-card .id-head { background: #0d6efd; color: #fff; padding: 12px 14px; display: flex;
                            align-items: center; justify-content: space-between; gap: 10px; }
        .id-card .id-head .school-name { font-weight: 700; font-size: 13px; line-height: 1.15; }
        .id-card .id-head small { opacity: .9; font-size: 10px; }
        .id-card .id-body { padding: 12px 14px; text-align: center; }
        .barcode { font-family: 'Libre Barcode 39', monospace; font-size: 20px; letter-spacing: 2px; }
        .qr { width: 60px; height: 60px; }
        .id-card .id-foot { background: #f8f9fa; padding: 8px 14px; font-size: 10px; text-align: center; }
        .id-card .table td { font-size: 11px; padding: 2px 6px; }
        .btn { display: inline-block; background: #0d6efd; color: #fff; border: 0; border-radius: 6px;
               padding: 8px 16px; font-size: 14px; cursor: pointer; text-decoration: none; }
        .btn-secondary { background: #6c757d; }
        @page { size: A4; margin: 8mm; }
        @media print {
            body { background: #fff; }
            .no-print { display: none; }
            .cards-wrap { gap: 10mm; padding: 0; }
            .id-card { width: 42%; box-shadow: none; page-break-inside: avoid; }
            .id-head, .id-foot { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn" onclick="window.print()"><i class="fas fa-print"></i> Print / Save as PDF</button>
        <a href="<?= url('students/index') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        <div class="text-muted mt-2 small"><?= count($rows) ?> ID card(s) ready to print.</div>
    </div>

    <div class="cards-wrap">
        <?php if (empty($rows)): ?>
            <div class="text-center text-muted py-5">No students match the current filters.</div>
        <?php endif; ?>
        <?php foreach ($rows as $student): ?>
        <div class="id-card">
            <div class="id-head">
                <div>
                    <div class="school-name"><?= e(settings('school_name', 'School')) ?></div>
                    <small><?= e(settings('school_motto', '')) ?></small>
                </div>
                <?php if ($logoPath && is_file($logoPath)): ?>
                    <img src="<?= BASE_URL . '/' . e(ltrim($logo, '/')) ?>" width="34" height="34" style="object-fit:contain; background:#fff; border-radius:50%;">
                <?php else: ?>
                    <i class="fas fa-graduation-cap fa-lg"></i>
                <?php endif; ?>
            </div>
            <div class="id-body">
                <div class="mb-2">
                    <?= avatar($student['photo'], trim($student['first_name'] . ' ' . $student['last_name']), 84) ?>
                </div>
                <h6 class="mb-1 fw-bold" style="font-size:14px;"><?= e(trim($student['first_name'] . ' ' . $student['last_name'])) ?></h6>
                <div class="badge bg-primary mb-2"><?= e($student['class_name'] ?: '—') ?><?= $student['section_name'] ? ' / ' . e($student['section_name']) : '' ?></div>
                <table class="table table-sm mb-2 text-start">
                    <tr><td class="text-muted">Official ID</td><td class="fw-semibold"><?= e($student['official_id'] ?: '—') ?></td></tr>
                    <tr><td class="text-muted">Admission No</td><td class="fw-semibold"><?= e($student['admission_no'] ?: '—') ?></td></tr>
                    <tr><td class="text-muted">Blood Group</td><td class="fw-semibold"><?= e($student['blood_group'] ?: '—') ?></td></tr>
                    <tr><td class="text-muted">Guardian Phone</td><td class="fw-semibold"><?= e($student['guardian_phone'] ?: $student['phone']) ?></td></tr>
                </table>
                <div class="d-flex justify-content-center align-items-center gap-3">
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
            </div>
            <div class="id-foot text-muted">
                Valid for the academic year <?= e($academicYear) ?><br>
                <?= e(settings('school_phone', '')) ?> &middot; <?= e(settings('school_email', '')) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
