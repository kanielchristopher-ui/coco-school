<?php if (!defined('APP_VERSION')) { exit; }
$p = $payment;
$balance = $balance ?? 0.0;
$netAmount = $netAmount ?? (float) $p['amount'];
$paidTotal = $paidTotal ?? (float) $p['amount'];
$logo = settings('school_logo');
$logoPath = $logo ? (ROOT_PATH . '/public/' . ltrim($logo, '/')) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt <?= e($p['receipt_no']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #212529; margin: 0; background: #f5f5f5; }
        .receipt { max-width: 640px; margin: 30px auto; background: #fff; border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden; }
        .head { background: #0d6efd; color: #fff; padding: 20px 28px; display: flex; justify-content: space-between; align-items: center; gap: 14px; }
        .head .brand { display: flex; align-items: center; gap: 12px; }
        .head .brand img { height: 48px; width: 48px; object-fit: contain; background: #fff; border-radius: 50%; padding: 2px; }
        .head h2 { margin: 0; font-size: 20px; }
        .head small { opacity: .9; }
        .head .motto { font-size: 11px; opacity: .85; }
        .body { padding: 28px; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 18px; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; width: 40%; }
        .total td { font-size: 18px; font-weight: 700; color: #198754; background: #f8f9fa; }
        .balance td { font-size: 15px; font-weight: 700; color: #dc3545; background: #fff5f5; }
        .foot { text-align: center; color: #6c757d; font-size: 12px; padding: 16px; border-top: 1px dashed #dee2e6; }
        .toolbar { max-width: 640px; margin: 0 auto 16px; text-align: right; }
        .btn { background: #0d6efd; color: #fff; border: 0; border-radius: 6px; padding: 8px 16px; font-size: 14px; cursor: pointer; }
        .btn:hover { opacity: .9; }
        .amount { font-size: 26px; font-weight: 700; color: #198754; }
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
    <div class="receipt">
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
                <small>Receipt No: <?= e($p['receipt_no']) ?></small>
            </div>
        </div>
        <div class="body">
            <div class="meta">
                <div>
                    <div><strong>Student:</strong> <?= e($p['first_name'] . ' ' . $p['last_name']) ?></div>
                    <div><strong>Admission No:</strong> <?= e($p['admission_no']) ?></div>
                    <div><strong>Class:</strong> <?= e($p['class_name'] ?: '—') ?></div>
                </div>
                <div class="text-end">
                    <div><strong>Invoice:</strong> <?= e($p['invoice_no']) ?></div>
                    <div><strong>Date:</strong> <?= formatDate($p['payment_date']) ?></div>
                </div>
            </div>
            <table>
                <tr><th>Particulars</th><td><?= e($p['fee_type_name'] ?: 'Fee Payment') ?></td></tr>
                <tr><th>Payment Method</th><td><?= e(ucwords(str_replace('_', ' ', $p['payment_method']))) ?></td></tr>
                <tr><th>Transaction ID</th><td><?= e($p['transaction_id'] ?: '—') ?></td></tr>
                <tr><th>Received By</th><td><?= e(trim(($p['cashier_first'] ?? '') . ' ' . ($p['cashier_last'] ?? ''))) ?: '—' ?></td></tr>
                <?php if (!empty($p['remark'])): ?>
                    <tr><th>Remark</th><td><?= e($p['remark']) ?></td></tr>
                <?php endif; ?>
                <tr class="total"><td>Amount Paid</td><td><?= currency((float) $p['amount']) ?></td></tr>
                <?php if ($balance > 0): ?>
                    <tr class="balance"><td>Balance Remaining</td><td><?= currency($balance) ?></td></tr>
                <?php endif; ?>
            </table>
            <div class="foot">
                <?= e(settings('school_phone')) ?><?= settings('school_email') ? ' &bull; ' . e(settings('school_email')) : '' ?><br>
                This is a computer generated receipt.
            </div>
        </div>
    </div>
</body>
</html>
