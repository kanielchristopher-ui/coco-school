<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Reports';

$reports = [
    'admission'  => ['Admission Report', 'All registered students with class, section, gender and admission date.', 'fas fa-user-graduate', 'primary'],
    'attendance' => ['Attendance Report', 'Student attendance summary with present, late, absent and leave counts.', 'fas fa-calendar-check', 'danger'],
    'fees'       => ['Fees Report', 'Fee invoices with discounts, scholarships, paid amounts and status.', 'fas fa-money-bill-wave', 'info'],
    'exam'       => ['Exam Results Report', 'Student marks, totals, percentages and grades.', 'fas fa-file-alt', 'secondary'],
    'teacher'    => ['Teacher Report', 'Staff list with department, qualification, salary and status.', 'fas fa-users', 'success'],
    'income'     => ['Income Report', 'All payments received with receipt numbers and methods.', 'fas fa-credit-card', 'success'],
    'expense'    => ['Expense Report', 'Purchases made with quantities, unit prices and totals.', 'fas fa-boxes', 'warning'],
];
?>
<div class="row g-3">
    <?php foreach ($reports as $key => $r): ?>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3 mb-2">
                        <div class="icon-circle bg-<?= $r[3] ?>-subtle text-<?= $r[3] ?>">
                            <i class="<?= $r[2] ?>"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1"><?= e($r[0]) ?></h6>
                            <p class="text-muted small mb-0"><?= e($r[1]) ?></p>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <a href="<?= url('reports/view/' . $key) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye me-1"></i>View</a>
                        <a href="<?= url('reports/export/' . $key) ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-download me-1"></i>Export</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
