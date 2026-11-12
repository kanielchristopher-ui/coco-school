<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Finance Reports';
$reportTypes = [
    'student-fees'    => 'Student Fee Report',
    'pending-fees'    => 'Pending Fees Report',
    'collections'     => 'Fee Collection Report',
    'payroll'         => 'Payroll Report',
    'expenses'        => 'Expense Report',
    'monthly-summary' => 'Monthly Finance Summary',
];
$printUrl = url('finance/report-print') . '?type=' . urlencode($type) . ($from !== '' ? '&from=' . urlencode($from) : '') . ($to !== '' ? '&to=' . urlencode($to) : '');
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <span class="text-muted small"><?= count($report['rows'] ?? []) ?> record(s)</span>
    </div>
    <a href="<?= $printUrl ?>" target="_blank" class="btn btn-sm btn-primary"><i class="fas fa-print me-1"></i>Print Report</a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('finance/reports') ?>" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-1">Report</label>
                <select name="type" class="form-select">
                    <?php foreach ($reportTypes as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $type === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">From</label>
                <input type="date" name="from" class="form-control" value="<?= e($from) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">To</label>
                <input type="date" name="to" class="form-control" value="<?= e($to) ?>">
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100"><i class="fas fa-filter me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i><?= e($report['title'] ?? 'Report') ?></h6>
        <?php if ($from !== '' || $to !== ''): ?>
            <span class="small text-muted"><?= $from !== '' ? 'From ' . e($from) : '' ?><?= $from !== '' && $to !== '' ? ' to ' : '' ?><?= $to !== '' ? e($to) : '' ?></span>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-muted">#</th>
                    <?php foreach ($report['columns'] as $col): ?>
                        <th><?= e($col['label']) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($report['rows'])): ?>
                    <tr><td colspan="<?= count($report['columns']) + 1 ?>" class="text-center text-muted py-4">No data for this report.</td></tr>
                <?php endif; ?>
                <?php foreach ($report['rows'] as $i => $row): ?>
                    <tr>
                        <td class="text-muted"><?= $i + 1 ?></td>
                        <?php foreach ($report['columns'] as $col): ?>
                            <td><?= e((string) ($row[$col['key']] ?? '')) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <?php if (!empty($report['totals'])): ?>
                <tfoot class="table-light">
                    <tr>
                        <th></th>
                        <?php foreach ($report['columns'] as $col): ?>
                            <th>
                                <?php if (isset($report['totals'][$col['label']])): ?>
                                    <span class="fw-bold text-success"><?= e($report['totals'][$col['label']]) ?></span>
                                <?php endif; ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
