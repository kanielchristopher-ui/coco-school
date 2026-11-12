<?php
/** Tableau de bord de la caissière. */
$pageTitle = 'Dashboard';
?>
<div class="dash-hello mb-4">
    <div class="row align-items-center g-3">
        <div class="col-lg-8">
            <h3><?= e(t('Hello, :name', ['name' => Auth::name()])) ?></h3>
            <p><?= e(role_label('cashier')) ?> · <?= e(ldate('l j F Y')) ?></p>
        </div>
        <div class="col-lg-4 text-lg-end"><a href="<?= url('finance/collections') ?>" class="btn"><i class="fas fa-cash-register me-1"></i>Record Payment</a></div>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php foreach ([
        ['coins', 'success', currency($stats['today']), 'Collected today', 'finance/receipts'],
        ['calendar-days', 'primary', currency($stats['month']), 'Collected this month', 'finance/reports'],
        ['file-invoice-dollar', 'danger', currency($stats['pending']), 'Pending Fees', 'finance/fees'],
        ['receipt', 'info', nf($stats['receipts']), 'Receipts issued today', 'finance/receipts'],
        ['user-clock', 'warning', nf($stats['unpaid']), 'Students with unpaid fees', 'finance/fees'],
    ] as $k): ?>
        <div class="col-sm-6 col-xl-4">
            <a href="<?= url($k[4]) ?>" class="card stat-card kpi2 fade-up">
                <span><span class="n d-block"><?= e($k[2]) ?></span><span class="l d-block"><?= e(t($k[3])) ?></span></span><span class="round r-<?= $k[1] === 'success' || $k[1] === 'primary' ? 'primary' : $k[1] ?>"><i class="fas fa-<?= $k[0] ?>"></i></span>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="card mb-4">
            <div class="card-header"><h6 class="fw-bold mb-0">Monthly Fees Collected</h6></div>
            <div class="card-body"><div class="chart-box"><canvas id="feeChart" height="260"></canvas></div></div>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between"><h6 class="fw-bold mb-0">Latest payments</h6><a href="<?= url('finance/receipts') ?>" class="small">View all</a></div>
            <div class="card-body">
                <?php if (!$lastPayments): ?><p class="text-muted mb-0">No payments recorded yet.</p><?php endif; ?>
                <?php foreach ($lastPayments as $p): ?>
                    <div class="list-row">
                        <span class="stat-icon tone-success" style="width:40px;height:40px;font-size:.95rem"><i class="fas fa-receipt"></i></span>
                        <div class="flex-grow-1"><strong><?= e(studentName($p)) ?></strong><div class="small text-muted"><?= e($p['receipt_no']) ?> · <?= e(formatDate($p['payment_date'])) ?></div></div>
                        <strong class="text-success"><?= e(currency((float) $p['amount'])) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card mb-4">
            <div class="card-header"><h6 class="fw-bold mb-0">Biggest unpaid balances</h6></div>
            <div class="card-body">
                <?php if (!$debtors): ?><p class="text-muted mb-0">Nothing to show.</p><?php endif; ?>
                <?php foreach ($debtors as $d): ?>
                    <div class="list-row">
                        <?= avatar(null, studentName($d), 40) ?>
                        <div class="flex-grow-1"><strong><?= e(studentName($d)) ?></strong><div class="small text-muted"><?= e($d['class_name'] ?? '') ?></div></div>
                        <strong class="text-danger"><?= e(currency((float) $d['due'])) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="row g-3">
            <?php foreach ([['money-check-dollar', 'Fee Collection', 'finance/collections'], ['file-invoice', 'Payment Receipts', 'finance/receipts'], ['receipt', 'School Expenses', 'finance/expenses'], ['chart-bar', 'Finance Reports', 'finance/reports']] as $l): ?>
                <div class="col-6"><a class="quick-link" href="<?= url($l[2]) ?>"><span class="stat-icon tone-primary"><i class="fas fa-<?= $l[0] ?>"></i></span><?= e(t($l[1])) ?></a></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new Chart(document.getElementById('feeChart'), { type: 'bar', data: { labels: <?= json_encode(array_column($feeTrend, 'month'), JSON_UNESCAPED_UNICODE) ?>,
        datasets: [{ label: <?= json_encode(t('Collected'), JSON_UNESCAPED_UNICODE) ?>, data: <?= json_encode(array_column($feeTrend, 'total')) ?>, backgroundColor: '#0fa968' }] } });
});
</script>
