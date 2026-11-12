<?php if (!defined('APP_VERSION')) { exit; }
$title = $report['title'] ?? 'Finance Report';
$logo = settings('school_logo');
$logoPath = $logo ? (ROOT_PATH . '/public/' . ltrim($logo, '/')) : '';
$period = '';
if ($from !== '' && $to !== '') {
    $period = $from . ' to ' . $to;
} elseif ($from !== '') {
    $period = 'From ' . $from;
} elseif ($to !== '') {
    $period = 'Until ' . $to;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= e($title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #212529; margin: 0; background: #f5f5f5; }
        .sheet { max-width: 900px; margin: 24px auto; background: #fff; border: 1px solid #dee2e6; border-radius: 8px; padding: 28px; }
        .head { display: flex; justify-content: space-between; align-items: center; gap: 14px; border-bottom: 2px solid #0d6efd; padding-bottom: 16px; margin-bottom: 18px; }
        .head .brand { display: flex; align-items: center; gap: 12px; }
        .head .brand img { height: 48px; width: 48px; object-fit: contain; border-radius: 50%; padding: 2px; }
        .head h1 { margin: 0; font-size: 20px; }
        .head small { color: #6c757d; }
        h2 { font-size: 16px; margin: 0 0 4px; }
        .period { color: #6c757d; font-size: 12px; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { padding: 8px 10px; text-align: left; border: 1px solid #e9ecef; }
        th { background: #f8f9fa; }
        tfoot th { background: #f8f9fa; font-weight: 700; }
        tfoot th .tot { color: #198754; }
        .foot { margin-top: 20px; text-align: center; color: #6c757d; font-size: 12px; border-top: 1px dashed #dee2e6; padding-top: 12px; }
        .toolbar { max-width: 900px; margin: 0 auto 14px; text-align: right; }
        .btn { background: #0d6efd; color: #fff; border: 0; border-radius: 6px; padding: 8px 16px; font-size: 14px; cursor: pointer; }
        .btn:hover { opacity: .9; }
        @media print {
            body { background: #fff; }
            .sheet { margin: 0; border: 0; box-shadow: none; }
            .toolbar { display: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn" onclick="window.print()">Print / Save PDF</button>
    </div>
    <div class="sheet">
        <div class="head">
            <div class="brand">
                <?php if ($logoPath && is_file($logoPath)): ?>
                    <img src="<?= BASE_URL . '/' . e(ltrim($logo, '/')) ?>" alt="Logo">
                <?php else: ?>
                    <i class="fas fa-graduation-cap fa-2x"></i>
                <?php endif; ?>
                <div>
                    <h1><?= e(settings('school_name') ?: APP_NAME) ?></h1>
                    <small><?= e(settings('school_address')) ?></small>
                </div>
            </div>
            <div class="text-end">
                <h2><?= e($title) ?></h2>
                <?php if ($period !== ''): ?><div class="period"><?= e($period) ?></div><?php endif; ?>
                <div class="period">Generated on <?= ldate('M d, Y H:i') ?></div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:36px;">#</th>
                    <?php foreach ($report['columns'] as $col): ?>
                        <th><?= e($col['label']) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($report['rows'])): ?>
                    <tr><td colspan="<?= count($report['columns']) + 1 ?>" style="text-align:center;color:#6c757d;padding:24px;">No data for this report.</td></tr>
                <?php endif; ?>
                <?php foreach ($report['rows'] as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <?php foreach ($report['columns'] as $col): ?>
                            <td><?= e((string) ($row[$col['key']] ?? '')) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <?php if (!empty($report['totals'])): ?>
                <tfoot>
                    <tr>
                        <th></th>
                        <?php foreach ($report['columns'] as $col): ?>
                            <th>
                                <?php if (isset($report['totals'][$col['label']])): ?>
                                    <span class="tot"><?= e($report['totals'][$col['label']]) ?></span>
                                <?php endif; ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>

        <div class="foot">
            <?= e(settings('school_phone')) ?><?= settings('school_email') ? ' &bull; ' . e(settings('school_email')) : '' ?><br>
            This is a computer generated report.
        </div>
    </div>
</body>
</html>
