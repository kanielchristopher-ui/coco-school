<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Student Profile';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <a href="<?= url('students/index') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>All Students</a>
    <div class="d-flex gap-2">
        <a href="<?= url('students/id-card/' . $student['id']) ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-id-card me-1"></i>ID Card</a>
        <a href="<?= url('students/edit/' . $student['id']) ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit me-1"></i>Edit</a>
        <button class="btn btn-sm btn-outline-danger" data-delete-url="<?= url('students/destroy/' . $student['id']) ?>" data-student-name="<?= e(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''))) ?>" onclick="confirmDeleteStudent(this)"><i class="fas fa-trash me-1"></i>Delete</button>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body">
                <?= avatar($student['photo'], $student['first_name'], 96) ?>
                <h5 class="mt-3 mb-0"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h5>
                <div class="mt-2"><?= statusBadge($student['status']) ?></div>
                <hr>
                <div class="text-start small">
                    <p class="mb-1"><i class="fas fa-id-badge me-2 text-primary"></i><strong>Official ID:</strong> <?= e($student['official_id'] ?: '—') ?></p>
                    <p class="mb-1"><i class="fas fa-school me-2 text-primary"></i><?= e($student['class_name'] ?: '—') ?><?= $student['section_name'] ? ' / ' . e($student['section_name']) : '' ?></p>
                    <p class="mb-1"><i class="fas fa-venus-mars me-2 text-primary"></i><?= ucfirst(e($student['gender'])) ?></p>
                    <p class="mb-1"><i class="fas fa-calendar me-2 text-primary"></i>DOB: <?= formatDate($student['dob']) ?></p>
                    <p class="mb-1"><i class="fas fa-location-dot me-2 text-primary"></i>Place of Birth: <?= e($student['birth_place'] ?? '') ?: '—' ?></p>
                    <p class="mb-1"><i class="fas fa-passport me-2 text-primary"></i>PERM Number: <?= e($student['perm_number'] ?? '') ?: '—' ?></p>
                    <p class="mb-1"><i class="fas fa-droplet me-2 text-primary"></i>Blood: <?= e($student['blood_group'] ?: '—') ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="row g-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="fw-bold mb-0">Contact & Guardian</h6></div>
                    <div class="card-body">
                        <div class="row small">
                            <div class="col-md-6 mb-2"><strong>Guardian:</strong> <?= e($student['guardian_name'] ?: '—') ?> (<?= e($student['guardian_relation'] ?: '—') ?>)</div>
                            <div class="col-md-6 mb-2"><strong>Guardian Phone:</strong> <?= e($student['guardian_phone'] ?: '—') ?></div>
                            <div class="col-md-6 mb-2"><strong>Guardian Email:</strong> <?= e($student['guardian_email'] ?: '—') ?></div>
                            <div class="col-md-6 mb-2"><strong>Phone:</strong> <?= e($student['phone'] ?: '—') ?></div>
                            <div class="col-md-6 mb-2"><strong>Email:</strong> <?= e($student['email'] ?: '—') ?></div>
                            <div class="col-12 mb-2"><strong>Address:</strong> <?= e($student['address'] ?: '—') ?>, <?= e($student['city'] ?: '') ?> <?= e($student['state'] ?: '') ?> <?= e($student['zip'] ?: '') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="fw-bold mb-0">Attendance Summary</h6></div>
                    <div class="card-body">
                        <?php if (empty($student['attendance'])): ?>
                            <p class="text-muted small mb-0">No attendance recorded.</p>
                        <?php else: ?>
                            <?php
                            $total = array_sum(array_map(fn($a) => $a['total'], $student['attendance']));
                            $colors = ['present' => 'success', 'absent' => 'danger', 'late' => 'warning', 'leave' => 'info'];
                            ?>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <?php foreach ($student['attendance'] as $a): ?>
                                    <span class="badge bg-<?= $colors[$a['status']] ?? 'secondary' ?>">
                                        <?= ucfirst($a['status']) ?>: <?= $a['total'] ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <?php $present = $total ? (array_sum(array_map(fn($a) => in_array($a['status'], ['present', 'late']) ? $a['total'] : 0, $student['attendance'])) / $total) * 100 : 0; ?>
                            <div class="progress" style="height:10px;">
                                <div class="progress-bar bg-success" style="width:<?= round($present) ?>%"></div>
                            </div>
                            <small class="text-muted"><?= round($present) ?>% attendance</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="fw-bold mb-0">Medical</h6></div>
                    <div class="card-body">
                        <p class="small mb-1"><strong>Blood Group:</strong> <?= e($student['blood_group'] ?: '—') ?></p>
                        <p class="small mb-0"><strong>Notes:</strong> <?= e($student['medical_info'] ?: 'No medical information recorded.') ?></p>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0">Fee History</h6>
                        <a href="<?= url('fees/index') ?>" class="btn btn-sm btn-link">Manage Fees</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Invoice</th><th>Type</th><th>Amount</th><th>Due</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php if (empty($student['fees'])): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-3">No invoices yet.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($student['fees'] as $f): ?>
                                    <tr>
                                        <td><a href="<?= url('fees/show/' . $f['id']) ?>"><?= e($f['invoice_no']) ?></a></td>
                                        <td><?= e($f['fee_type_name'] ?: '—') ?></td>
                                        <td><?= currency((float) $f['amount']) ?></td>
                                        <td><?= formatDate($f['due_date']) ?></td>
                                        <td><?= statusBadge($f['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="fw-bold mb-0">Payments</h6></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Receipt</th><th>Invoice</th><th>Amount</th><th>Method</th><th>Date</th></tr></thead>
                            <tbody>
                                <?php if (empty($student['payments'])): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-3">No payments recorded.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($student['payments'] as $p): ?>
                                    <tr>
                                        <td><a href="<?= url('fees/receipt/' . $p['id']) ?>" target="_blank"><?= e($p['receipt_no']) ?></a></td>
                                        <td><?= e($p['invoice_no']) ?></td>
                                        <td><?= currency((float) $p['amount']) ?></td>
                                        <td><?= e($p['payment_method']) ?></td>
                                        <td><?= formatDate($p['payment_date'], 'M d, Y H:i') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
