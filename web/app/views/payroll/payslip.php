<?php if (!defined('APP_VERSION')) { exit; }
$r = $record;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip <?= e($r['employee_id']) ?> <?= e($r['month']) ?>/<?= e($r['year']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #212529; margin: 0; background: #f5f5f5; }
        .slip { max-width: 720px; margin: 30px auto; background: #fff; border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden; }
        .head { background: #0d6efd; color: #fff; padding: 20px 28px; display: flex; justify-content: space-between; align-items: center; }
        .head h2 { margin: 0; font-size: 20px; }
        .body { padding: 28px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; }
        .net td { font-size: 18px; font-weight: 700; color: #198754; background: #f8f9fa; }
        .foot { text-align: center; color: #6c757d; font-size: 12px; padding: 16px; border-top: 1px dashed #dee2e6; }
        .toolbar { max-width: 720px; margin: 0 auto 16px; text-align: right; }
        .btn { background: #0d6efd; color: #fff; border: 0; border-radius: 6px; padding: 8px 16px; font-size: 14px; cursor: pointer; }
        @media print {
            body { background: #fff; }
            .slip { margin: 0; border: 0; }
            .toolbar { display: none; }
            .head { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="toolbar"><button class="btn" onclick="window.print()">Print / Save PDF</button></div>
    <div class="slip">
        <div class="head">
            <div>
                <h2><?= e(settings('school_name') ?: APP_NAME) ?></h2>
                <small>Salary Payslip</small>
            </div>
            <div class="text-end">
                <div><strong><?= e(ldate('F Y', mktime(0, 0, 0, (int) $r['month'], 1, (int) $r['year']))) ?></strong></div>
            </div>
        </div>
        <div class="body">
            <div class="row">
                <table style="margin-bottom:16px;">
                    <tr><th style="width:40%;">Employee ID</th><td><?= e($r['employee_id']) ?></td></tr>
                    <tr><th>Name</th><td><?= e(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''))) ?></td></tr>
                    <tr><th>Department</th><td><?= e($r['department'] ?: '—') ?></td></tr>
                    <tr><th>Status</th><td><?= e(ucfirst($r['status'])) ?></td></tr>
                </table>
                <table>
                    <tr><th>Basic Salary</th><td class="text-end"><?= currency($r['basic_salary']) ?></td></tr>
                    <tr><th>Allowances</th><td class="text-end text-success">+<?= currency($r['allowances']) ?></td></tr>
                    <tr><th>Deductions</th><td class="text-end text-danger">-<?= currency($r['deductions']) ?></td></tr>
                    <tr class="net"><td>Net Salary</td><td class="text-end"><?= currency($r['net_salary']) ?></td></tr>
                </table>
            </div>
            <div class="foot">
                Payment Date: <?= formatDate($r['payment_date']) ?><br>
                This is a computer generated payslip.
            </div>
        </div>
    </div>
</body>
</html>
