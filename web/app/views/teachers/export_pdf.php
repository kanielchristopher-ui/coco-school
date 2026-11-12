<?php if (!defined('APP_VERSION')) { exit; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher List - <?= e(settings('school_name', 'School')) ?></title>
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/bootstrap.min.css') ?>">
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; color: #212529; }
        .report-head { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px double #0d6efd; padding-bottom: 10px; margin-bottom: 16px; }
        .no-print { margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #dee2e6; padding: 6px 8px; text-align: left; font-size: 13px; }
        th { background: #0d6efd; color: #fff; text-transform: uppercase; font-size: 12px; }
        tr:nth-child(even) { background: #f8f9fa; }
        .photo { width: 30px; height: 30px; object-fit: cover; border-radius: 50%; }
        @media print { .no-print { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
    <div class="container-lg">
        <div class="report-head">
            <div>
                <h4 class="mb-0 fw-bold"><?= e(settings('school_name', 'School')) ?></h4>
                <small class="text-muted"><?= e(settings('school_address', '')) ?></small>
            </div>
            <div class="text-end">
                <h5 class="mb-0">Teacher List</h5>
                <small class="text-muted">Generated <?= ldate('M d, Y h:i A') ?></small>
            </div>
        </div>

        <div class="text-muted small mb-3">
            Total: <strong><?= count($rows) ?></strong> teacher(s)
        </div>

        <table>
            <thead>
                <tr>
                    <th>Official ID</th>
                    <th>Teacher Full Name</th>
                    <th>Department</th>
                    <th>Qualification</th>
                    <th>Phone</th>
                    <th>Salary</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $t): ?>
                    <tr>
                        <td><?= e($t['official_id'] ?: '—') ?></td>
                        <td><?= e(trim($t['first_name'] . ' ' . $t['last_name'])) ?></td>
                        <td><?= e($t['department'] ?: '—') ?></td>
                        <td><?= e($t['qualification'] ?: '—') ?></td>
                        <td><?= e($t['phone'] ?: '—') ?></td>
                        <td><?= currency((float) $t['salary']) ?></td>
                        <td><?= ucfirst($t['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="no-print text-center mt-4">
            <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Print / Save as PDF</button>
            <a href="<?= url('teachers/index') ?>" class="btn btn-outline-secondary">Back to Teachers</a>
        </div>
    </div>
</body>
</html>
