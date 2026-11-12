<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Result - ' . ($exam['name'] ?? 'Exam');
$s = $student;
$logo = settings('school_logo');
$logoPath = $logo ? (ROOT_PATH . '/public/' . ltrim($logo, '/')) : '';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
    <a href="<?= url('results/my-results') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>My Results</a>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()"><i class="fas fa-print me-1"></i>Print / Save PDF</button>
        <a href="<?= url('results/report-card/' . (int) $exam['id'] . '/' . (int) $s['id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-pdf me-1"></i>Report Card</a>
    </div>
</div>

<div class="result-sheet">
    <!-- School header -->
    <div class="sheet-head">
        <div class="sheet-logo">
            <?php if ($logoPath && is_file($logoPath)): ?>
                <img src="<?= BASE_URL . '/' . e(ltrim($logo, '/')) ?>" alt="Logo">
            <?php else: ?>
                <div class="sheet-logo-placeholder"><i class="fas fa-graduation-cap"></i></div>
            <?php endif; ?>
        </div>
        <div class="sheet-title">
            <h1><?= e(settings('school_name') ?: APP_NAME) ?></h1>
            <p><?= e(settings('school_address')) ?><?= settings('school_phone') ? ' &bull; ' . e(settings('school_phone')) : '' ?></p>
            <h2>EXAMINATION RESULT SHEET</h2>
        </div>
    </div>

    <!-- Exam details -->
    <div class="sheet-exam-info">
        <div><span>Exam:</span> <strong><?= e($exam['name']) ?></strong></div>
        <div><span>Type:</span> <?= e($exam['type_name'] ?: '—') ?></div>
        <div><span>Academic Year:</span> <?= e($exam['session_name'] ?: '—') ?></div>
        <div><span>Term:</span> <?= e($exam['term'] ?: '—') ?></div>
        <div><span>Exam Date:</span> <?= formatDate($exam['start_date'] ?? null) ?></div>
    </div>

    <!-- Student details -->
    <div class="sheet-student-info">
        <div><span>Student Name:</span> <strong><?= e(studentName($s)) ?></strong></div>
        <div><span>Admission No:</span> <?= e($s['admission_no'] ?? '—') ?></div>
        <div><span>Class:</span> <?= e($s['class_name'] ?? '—') ?><?= !empty($s['section_name']) ? ' - ' . e($s['section_name']) : '' ?></div>
        <div><span>Roll No:</span> <?= e($s['roll_no'] ?? '—') ?></div>
    </div>

    <?php if (empty($marks)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-file-alt fa-3x mb-3 d-block"></i>
            No subject marks recorded for this exam.
        </div>
    <?php else: ?>
        <table class="sheet-table">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th>Subject</th>
                    <th class="text-center">Full Mark</th>
                    <th class="text-center">Student Mark</th>
                    <th class="text-center">Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($marks as $m): ?>
                    <tr>
                        <td class="text-center"><?= $i++ ?></td>
                        <td class="fw-semibold"><?= e($m['subject_name']) ?></td>
                        <td class="text-center"><?= e($m['max_marks']) ?></td>
                        <td class="text-center"><strong><?= e($m['marks_obtained']) ?></strong></td>
                        <td class="text-center"><span class="grade-badge"><?= e($m['grade']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Summary -->
        <div class="sheet-summary">
            <div><span>Total Marks:</span> <strong><?= e($maxTotal) ?></strong></div>
            <div><span>Obtained Marks:</span> <strong><?= e($total) ?></strong></div>
            <div><span>Average Percentage:</span> <strong><?= e(round((float) $percentage, 2)) ?>%</strong></div>
            <div><span>Overall Grade:</span> <strong><?= e($overall['grade'] ?? '—') ?> (<?= e($overall['remarks'] ?? '') ?>)</strong></div>
            <div><span>Position:</span> <strong><?= (int) $position ?> / <?= (int) $rankCount ?></strong></div>
        </div>

        <!-- Teacher remarks -->
        <?php
            $comments = array_values(array_filter(array_column($marks, 'teacher_comments')));
            $remarks  = array_values(array_filter(array_column($marks, 'remarks')));
            if (!empty($comments) || !empty($remarks)):
        ?>
        <div class="sheet-remarks">
            <span>Teacher Remarks:</span>
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $c): ?>
                    <p><?= e($c) ?></p>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($remarks as $r): ?>
                    <p><?= e($r) ?></p>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.result-sheet {
    max-width: 800px;
    margin: 0 auto;
    background: #fff;
    border: 2px solid #0d6efd;
    border-radius: 8px;
    padding: 24px 28px;
    font-size: 14px;
}
.sheet-head { display: flex; align-items: center; gap: 16px; border-bottom: 3px double #0d6efd; padding-bottom: 14px; margin-bottom: 14px; }
.sheet-logo img { height: 64px; width: 64px; object-fit: contain; }
.sheet-logo-placeholder { width: 64px; height: 64px; border: 2px solid #0d6efd; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #0d6efd; font-size: 28px; }
.sheet-title { flex: 1; text-align: center; }
.sheet-title h1 { font-size: 20px; font-weight: 700; margin: 0; color: #0d6efd; text-transform: uppercase; }
.sheet-title p { margin: 2px 0 4px; font-size: 12px; color: #6c757d; }
.sheet-title h2 { font-size: 15px; font-weight: 700; margin: 0; letter-spacing: 1px; }
.sheet-exam-info, .sheet-student-info { display: grid; grid-template-columns: repeat(2, 1fr); gap: 4px 24px; border: 1px solid #dee2e6; border-radius: 6px; padding: 10px 14px; margin-bottom: 12px; background: #f8f9fa; }
.sheet-exam-info span, .sheet-student-info span { color: #6c757d; }
.sheet-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
.sheet-table th, .sheet-table td { border: 1px solid #adb5bd; padding: 8px 10px; }
.sheet-table th { background: #0d6efd; color: #fff; text-align: left; }
.sheet-table tbody tr:nth-child(even) { background: #f8f9fa; }
.grade-badge { background: #0d6efd; color: #fff; border-radius: 4px; padding: 2px 10px; font-weight: 700; }
.sheet-summary { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px 24px; border: 2px solid #0d6efd; border-radius: 6px; padding: 12px 16px; margin-top: 14px; background: #eef4ff; }
.sheet-summary span { color: #6c757d; }
.sheet-remarks { border: 1px dashed #adb5bd; border-radius: 6px; padding: 10px 14px; margin-top: 14px; }
.sheet-remarks span { font-weight: 700; }
.sheet-remarks p { margin: 4px 0 0; }
@media print {
    body { background: #fff !important; }
    .result-sheet { border: 2px solid #0d6efd; max-width: 100%; box-shadow: none; margin: 0; }
    .no-print { display: none !important; }
    .sheet-table th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .sheet-summary { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
