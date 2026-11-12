<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = $title;
$summary = $summary ?? null;

$moneyCols = ['amount', 'total', 'price', 'paid', 'fare', 'salary'];
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h5 class="mb-1"><?= e($title) ?></h5>
        <span class="text-muted small"><?= count($rows) ?> record(s)</span>
    </div>
    <div class="d-flex gap-2 no-print">
        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fas fa-print me-1"></i>Print</button>
        <a href="<?= url('reports/index') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-arrow-left me-1"></i>All Reports</a>
    </div>
</div>

<?php if (empty($headers) || empty($rows)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center text-muted py-5">
            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
            No data available for this report.
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <?php foreach ($headers as $h): ?>
                            <th><?= e($h) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <?php $values = array_values($row); ?>
                        <tr>
                            <?php foreach ($headers as $i => $h): ?>
                                <?php
                                $raw = $values[$i] ?? null;
                                $isMoney = is_numeric($raw) && array_reduce($moneyCols, fn($c, $k) => $c || stripos((string) $h, $k) !== false, false);
                                ?>
                                <td><?= $isMoney ? currency((float) $raw) : e($raw !== null ? (string) $raw : '—') ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($summary !== null): ?>
            <div class="card-footer bg-white d-flex justify-content-end align-items-center gap-2">
                <strong>Total:</strong>
                <span class="fs-5 fw-bold text-success"><?= currency((float) $summary) ?></span>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
