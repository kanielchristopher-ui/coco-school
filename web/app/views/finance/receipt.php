<?php if (!defined('APP_VERSION')) { exit; }
$r = $receipt;
$isVoided = ($r['status'] ?? 'active') === 'voided';
$logo = settings('school_logo');
$logoPath = $logo ? (ROOT_PATH . '/public/' . ltrim($logo, '/')) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt <?= e($r['receipt_no']) ?><?= $isVoided ? ' (Voided)' : '' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #212529; margin: 0; background: #f5f5f5; }
        .receipt { max-width: 640px; margin: 30px auto; background: #fff; border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden; }
        .receipt.voided { border-color: #dc3545; }
        .head { background: #0d6efd; color: #fff; padding: 20px 28px; display: flex; justify-content: space-between; align-items: center; gap: 14px; }
        .receipt.voided .head { background: #dc3545; }
        .head .brand { display: flex; align-items: center; gap: 12px; }
        .head .brand img { height: 48px; width: 48px; object-fit: contain; background: #fff; border-radius: 50%; padding: 2px; }
        .head h2 { margin: 0; font-size: 20px; }
        .head small { opacity: .9; }
        .head .motto { font-size: 11px; opacity: .85; }
        .body { padding: 28px; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 18px; font-size: 14px; }
        .void-stamp { text-align: center; font-size: 40px; font-weight: 800; letter-spacing: 8px; color: #dc3545; opacity: .35; transform: rotate(-8deg); margin: 18px 0 6px; user-select: none; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #eef2ff; color: #1e293b; width: 40%; }
        .total td { font-size: 18px; font-weight: 700; color: #198754; background: #f8f9fa; }
        .total.voided td { color: #dc3545; }
        .foot { text-align: center; color: #495057; font-size: 12px; padding: 16px; border-top: 1px dashed #dee2e6; }
        .toolbar { max-width: 640px; margin: 0 auto 16px; text-align: right; }
        .btn { background: #0d6efd; color: #fff; border: 0; border-radius: 6px; padding: 8px 16px; font-size: 14px; cursor: pointer; }
        .btn:hover { opacity: .9; }
        @media print {
            body { background: #fff; }
            .receipt { margin: 0; border: 0; box-shadow: none; }
            .toolbar { display: none; }
            .head { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn" onclick="window.print()">Print / Save PDF</button>
    </div>
    <div class="receipt<?= $isVoided ? ' voided' : '' ?>">
        <div class="head">
            <div class="brand">
                <?php if ($logoPath && is_file($logoPath)): ?>
                    <img src="<?= BASE_URL . '/' . e(ltrim($logo, '/')) ?>" alt="Logo">
                <?php else: ?>
                    <i class="fas fa-graduation-cap fa-2x"></i>
                <?php endif; ?>
                <div>
                    <h2><?= e(settings('school_name') ?: APP_NAME) ?></h2>
                    <small><?= e(settings('school_address')) ?></small>
                    <?php if (settings('school_motto')): ?><div class="motto"><?= e(settings('school_motto')) ?></div><?php endif; ?>
                </div>
            </div>
            <div class="text-end">
                <div style="font-size:16px;"><strong>PAYMENT RECEIPT</strong></div>
                <small>Receipt No: <?= e($r['receipt_no']) ?></small>
            </div>
        </div>
        <?php if ($isVoided): ?>
            <div style="background:#dc3545;color:#fff;text-align:center;padding:8px;font-weight:700;letter-spacing:3px;">VOIDED</div>
        <?php endif; ?>
        <div class="body">
            <div class="meta">
                <div>
                    <div><strong>Student:</strong> <?= e(studentName($r)) ?></div>
                    <div><strong>Admission No:</strong> <?= e($r['admission_no']) ?></div>
                    <div><strong>Class:</strong> <?= e($r['class_name'] ?: '—') ?></div>
                </div>
                <div class="text-end">
                    <div><strong>Date:</strong> <?= formatDate($r['payment_date']) ?></div>
                </div>
            </div>
            <?php if ($isVoided): ?>
                <div class="void-stamp">VOIDED</div>
            <?php endif; ?>
            <table>
                <tr><th>Fee Type</th><td><?= e($r['fee_type']) ?></td></tr>
                <tr><th>Payment Method</th><td><?= e(ucwords(str_replace('_', ' ', $r['payment_method']))) ?></td></tr>
                <tr><th>Received By</th><td><?= e(trim(($r['cashier_first'] ?? '') . ' ' . ($r['cashier_last'] ?? ''))) ?: '—' ?></td></tr>
                <?php if (!empty($r['notes'])): ?>
                    <tr><th>Notes</th><td><?= e($r['notes']) ?></td></tr>
                <?php endif; ?>
                <tr class="total<?= $isVoided ? ' voided' : '' ?>"><td>Amount Paid</td><td><?= currency((float) $r['amount_paid']) ?></td></tr>
            </table>
            <div class="foot">
                <?= e(settings('school_phone')) ?><?= settings('school_email') ? ' &bull; ' . e(settings('school_email')) : '' ?><br>
                This is a computer generated receipt.
            </div>
        </div>
    </div>
</body>
</html>
