<?php if (!defined('APP_VERSION')) { exit; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student List - <?= e(settings('school_name', 'School')) ?></title>
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/bootstrap.min.css') ?>">
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; color: #212529; }
        .report-head { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px double #0d6efd; padding-bottom: 10px; margin-bottom: 16px; }
        .no-print { margin: 20px 0; }
        .table th { background: #0d6efd; color: #fff; font-size: 12px; text-transform: uppercase; }
        .table td { font-size: 13px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #dee2e6; padding: 6px 8px; text-align: left; }
        th { background: #0d6efd; color: #fff; }
        tr:nth-child(even) { background: #f8f9fa; }
        .photo { width: 30px; height: 30px; object-fit: cover; border-radius: 50%; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
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
                <h5 class="mb-0">Student List</h5>
                <small class="text-muted">Generated <?= ldate('M d, Y h:i A') ?></small>
            </div>
        </div>

        <div class="text-muted small mb-3">
            Total: <strong><?= count($rows) ?></strong> student(s)
        </div>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Official ID</th>
                    <th>Student Full Name</th>
                    <th>Class / Section</th>
                    <th>Gender</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>DOB</th>
                    <th>Blood Group</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Photo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $s): ?>
                    <tr>
                        <td><?= e($s['official_id'] ?: '—') ?></td>
                        <td><?= e(studentName($s)) ?></td>
                        <td><?= e($s['class_name'] ?: '—') ?><?= $s['section_name'] ? ' / ' . e($s['section_name']) : '' ?></td>
                        <td><?= ucfirst(e($s['gender'] ?: '—')) ?></td>
                        <td><?= e($s['phone'] ?: '—') ?></td>
                        <td><?= ucfirst($s['status']) ?></td>
                        <td><?= formatDate($s['dob']) ?></td>
                        <td><?= e($s['blood_group'] ?: '—') ?></td>
                        <td><?= e($s['city'] ?: '—') ?></td>
                        <td><?= e($s['state'] ?: '—') ?></td>
                        <td><?php if ($s['photo'] && is_file(ROOT_PATH . '/public/' . ltrim($s['photo'], '/'))): ?>
                                <img class="photo" src="<?= BASE_URL . '/' . e(ltrim($s['photo'], '/')) ?>">
                            <?php endif; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="no-print text-center mt-4">
            <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Print / Save as PDF</button>
            <a href="<?= url('students/index') ?>" class="btn btn-outline-secondary">Back to Students</a>
        </div>
    </div>
</body>
</html>
