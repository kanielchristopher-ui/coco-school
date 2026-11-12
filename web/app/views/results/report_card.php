<?php if (!defined('APP_VERSION')) { exit; }
$s = $student;
$attendance = $attendance ?? [];
$attMap = [];
foreach ($attendance as $a) { $attMap[$a['status']] = (int) $a['total']; }
$attTotal = array_sum($attMap);
$logo = settings('school_logo');
$logoPath = $logo ? (ROOT_PATH . '/public/' . ltrim($logo, '/')) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Card - <?= e($exam['name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #212529; margin: 0; background: #f5f5f5; }
        .card { max-width: 800px; margin: 30px auto; background: #fff; border: 3px solid #0d6efd; border-radius: 8px; overflow: hidden; }
        .head { background: #fff; color: #0d6efd; padding: 22px 30px; border-bottom: 4px double #0d6efd; display: flex; justify-content: space-between; align-items: center; gap: 16px; }
        .head .brand { display: flex; align-items: center; gap: 14px; }
        .head .brand img { height: 60px; width: 60px; object-fit: contain; }
        .head .brand .logo-ph { height: 60px; width: 60px; border: 2px solid #0d6efd; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; color: #0d6efd; }
        .head h1 { margin: 0; font-size: 22px; text-transform: uppercase; }
        .head .motto { font-size: 12px; color: #6c757d; margin-top: 2px; }
        .head .rc-label { font-size: 18px; font-weight: 800; letter-spacing: 1px; text-align: center; }
        .head .rc-sub { font-size: 12px; color: #6c757d; text-align: center; }
        .strip { background: #0d6efd; color: #fff; text-align: center; padding: 8px; font-size: 16px; font-weight: 700; letter-spacing: 2px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { padding: 9px 12px; text-align: left; border: 1px solid #adb5bd; }
        th { background: #eef4ff; color: #0d6efd; font-weight: 700; }
        .body { padding: 26px 30px; }
        .info td { border: 0; border-bottom: 1px solid #eee; padding: 6px 12px; }
        .info { margin-bottom: 8px; }
        .summary { display: flex; gap: 12px; margin-top: 18px; }
        .box { flex: 1; border: 2px solid #0d6efd; border-radius: 6px; padding: 12px; text-align: center; background: #eef4ff; }
        .box .label { color: #6c757d; font-size: 12px; }
        .box .value { font-size: 18px; font-weight: 700; color: #0d6efd; }
        .section-title { font-weight: 700; font-size: 14px; margin: 20px 0 8px; text-transform: uppercase; color: #0d6efd; border-bottom: 1px solid #dee2e6; padding-bottom: 4px; }
        .foot { text-align: center; color: #6c757d; font-size: 12px; padding: 16px; border-top: 2px solid #0d6efd; }
        .toolbar { max-width: 800px; margin: 0 auto 16px; text-align: right; }
        .btn { background: #0d6efd; color: #fff; border: 0; border-radius: 6px; padding: 8px 16px; font-size: 14px; cursor: pointer; }
        .remarks-box { border: 1px solid #adb5bd; border-radius: 6px; padding: 12px 14px; margin-top: 14px; min-height: 48px; }
        .signatures { display: flex; justify-content: space-between; margin-top: 34px; gap: 20px; }
        .sig { text-align: center; }
        .sig .role { font-weight: 700; font-size: 13px; }
        .sig .name { font-size: 12px; color: #6c757d; margin-top: 2px; }
        .sig .line { border-top: 1px solid #adb5bd; margin-top: 44px; padding-top: 6px; font-size: 12px; color: #6c757d; min-width: 150px; }
        .stamp { width: 52px; height: 52px; border: 2px dashed #adb5bd; border-radius: 50%; margin-top: 8px;
                 display: flex; align-items: center; justify-content: center; color: #adb5bd; font-size: 14px;
                 background: rgba(13, 110, 253, .05); }
        @media print {
            body { background: #fff; }
            .card { margin: 0; border: 3px solid #0d6efd; box-shadow: none; }
            .toolbar { display: none; }
            .head { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .strip { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .box { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="toolbar"><button class="btn" onclick="window.print()">Print / Save PDF</button></div>
    <div class="card">
        <div class="head">
            <div class="brand">
                <?php if ($logoPath && is_file($logoPath)): ?>
                    <img src="<?= BASE_URL . '/' . e(ltrim($logo, '/')) ?>" alt="Logo">
                <?php else: ?>
                    <div class="logo-ph"><i class="fas fa-graduation-cap"></i></div>
                <?php endif; ?>
                <div>
                    <h1><?= e(settings('school_name') ?: APP_NAME) ?></h1>
                    <div class="motto">
                        <?php if (settings('school_motto')): ?>
                            <?= e(settings('school_motto')) ?>
                        <?php else: ?>
                            <?= e(settings('school_address')) ?><?= settings('school_phone') ? ' &bull; ' . e(settings('school_phone')) : '' ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div>
                <div class="rc-label">REPORT CARD</div>
                <div class="rc-sub"><?= e($exam['type_name'] ?? '') ?><?= $exam['term'] ? ' &bull; ' . e($exam['term']) : '' ?></div>
                <div class="rc-sub"><?= e($exam['session_name'] ?? '') ?><?= settings('academic_year') ? ' &bull; ' . e(settings('academic_year')) : '' ?></div>
            </div>
        </div>
        <div class="strip"><?= e($exam['name']) ?> &mdash; <?= formatDate($exam['start_date'] ?? null) ?></div>
        <div class="body">
            <table class="info">
                <tr>
                    <td><strong>Name:</strong> <?= e(studentName($s)) ?></td>
                    <td><strong>Admission No:</strong> <?= e($s['admission_no'] ?? '—') ?></td>
                    <td><strong>Roll:</strong> <?= e($s['roll_no'] ?? '—') ?></td>
                </tr>
                <tr>
                    <td><strong>Class:</strong> <?= e($s['class_name'] ?? ($s['class'] ?? '—')) ?></td>
                    <td><strong>Section:</strong> <?= e($s['section_name'] ?? '—') ?></td>
                    <td><strong>Gender:</strong> <?= e(ucfirst($s['gender'] ?? '—')) ?></td>
                </tr>
            </table>

            <?php if (empty($marks)): ?>
                <p class="text-center text-muted" style="padding:30px 0;">No subject marks recorded for this exam.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr><th>#</th><th>Subject</th><th>Full Mark</th><th>Student Mark</th><th>Grade</th></tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($marks as $m): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= e($m['subject_name']) ?></td>
                                <td><?= e($m['max_marks']) ?></td>
                                <td><strong><?= e($m['marks_obtained']) ?></strong></td>
                                <td><?= e($m['grade']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="summary">
                <div class="box"><div class="label">Total Marks</div><div class="value"><?= e($maxTotal) ?></div></div>
                <div class="box"><div class="label">Obtained Marks</div><div class="value"><?= e($total) ?></div></div>
                <div class="box"><div class="label">Percentage</div><div class="value"><?= e(round((float) $percentage, 2)) ?>%</div></div>
                <div class="box"><div class="label">Overall Grade</div><div class="value"><?= e($overall['grade'] ?? '—') ?></div></div>
                <div class="box"><div class="label">Position</div><div class="value"><?= (int) $position ?> / <?= (int) $rankCount ?></div></div>
            </div>

            <div class="section-title">Attendance Summary</div>
            <?php if ($attTotal === 0): ?>
                <div class="text-muted small">No attendance records for this exam period.</div>
            <?php else: ?>
                <table>
                    <thead><tr><th>Present</th><th>Late</th><th>Absent</th><th>Leave</th><th>Total Records</th></tr></thead>
                    <tbody>
                        <tr>
                            <td><?= $attMap['present'] ?? 0 ?></td>
                            <td><?= $attMap['late'] ?? 0 ?></td>
                            <td><?= $attMap['absent'] ?? 0 ?></td>
                            <td><?= $attMap['leave'] ?? 0 ?></td>
                            <td><strong><?= $attTotal ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="section-title">Teacher Remarks</div>
            <div class="remarks-box">
                <?php
                    $comments = array_values(array_filter(array_column($marks, 'teacher_comments')));
                    if (empty($comments)) { echo '<span class="text-muted small">No remarks recorded.</span>'; }
                    else { foreach ($comments as $c) { echo '<div class="small">' . e($c) . '</div>'; } }
                ?>
            </div>

            <div class="signatures">
                <div class="sig"><div class="line">Class Teacher</div></div>
                <div class="sig"><div class="line">Head Teacher</div></div>
                <div class="sig">
                    <div class="line">Principal</div>
                    <?php if (settings('principal_name')): ?>
                        <div class="name"><?= e(settings('principal_name')) ?></div>
                    <?php endif; ?>
                    <div class="stamp mx-auto"></div>
                </div>
            </div>
        </div>
        <div class="foot">
            <?= e(settings('school_address')) ?><?= settings('school_phone') ? ' &bull; ' . e(settings('school_phone')) : '' ?><?= settings('school_email') ? ' &bull; ' . e(settings('school_email')) : '' ?><br>
            Generated on <?= ldate('M d, Y h:i A') ?> &bull; This is a computer generated report card.
        </div>
    </div>
</body>
</html>
