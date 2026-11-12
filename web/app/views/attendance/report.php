<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Attendance Reports';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Attendance Report</h6>
    <a href="<?= url('attendance/export?type=' . $type . '&date=' . urlencode($date) . '&month=' . $month . '&year=' . $year) ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-file-csv me-1"></i>Export</a>
</div>

<ul class="nav nav-pills mb-3">
    <li class="nav-item"><a class="nav-link <?= $type === 'daily' ? 'active' : '' ?>" href="<?= url('attendance/report?type=daily&date=' . date('Y-m-d')) ?>">Daily</a></li>
    <li class="nav-item"><a class="nav-link <?= $type === 'monthly' ? 'active' : '' ?>" href="<?= url('attendance/report?type=monthly&month=' . date('n') . '&year=' . date('Y')) ?>">Monthly</a></li>
    <li class="nav-item"><a class="nav-link <?= $type === 'yearly' ? 'active' : '' ?>" href="<?= url('attendance/report?type=yearly&year=' . date('Y')) ?>">Yearly</a></li>
</ul>

<form method="GET" action="<?= url('attendance/report') ?>" class="row g-2 mb-3">
    <input type="hidden" name="type" value="<?= e($type) ?>">
    <?php if ($type === 'daily'): ?>
        <div class="col-md-3"><input type="date" name="date" class="form-control" value="<?= e($date) ?>"></div>
    <?php elseif ($type === 'monthly'): ?>
        <div class="col-md-2">
            <select name="month" class="form-select">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $month === $m ? 'selected' : '' ?>><?= ldate('F', mktime(0,0,0,$m,1)) ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-2"><input type="number" name="year" class="form-control" value="<?= $year ?>" min="2000" max="2100"></div>
    <?php else: ?>
        <div class="col-md-2"><input type="number" name="year" class="form-control" value="<?= $year ?>" min="2000" max="2100"></div>
    <?php endif; ?>
    <div class="col-md-1"><button class="btn btn-outline-primary w-100"><i class="fas fa-search"></i></button></div>
</form>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Student</th><th>Class</th>
                    <?php if ($type === 'daily'): ?>
                        <th>Date</th><th>Status</th><th>Remark</th>
                    <?php else: ?>
                        <th>Present</th><th>Late</th><th>Absent</th><th>Leave</th><th>%</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="10" class="text-center text-muted py-4">No records found.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <?php if ($type === 'daily'): ?>
                            <td class="fw-semibold"><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td>
                            <td><?= e($r['class_name'] ?: '—') ?></td>
                            <td><?= formatDate($r['date']) ?></td>
                            <td><?= statusBadge($r['status']) ?></td>
                            <td class="small"><?= e($r['remark'] ?: '—') ?></td>
                        <?php else: ?>
                            <?php
                            $present = (int) $r['present']; $late = (int) $r['late'];
                            $absent = (int) $r['absent']; $leave = (int) $r['leave'];
                            $total = $present + $late + $absent + $leave;
                            $pct = $total ? round((($present + $late) / $total) * 100, 1) : 0;
                            ?>
                            <td class="fw-semibold"><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td>
                            <td><?= e($r['class_name'] ?: '—') ?></td>
                            <td><span class="badge bg-success"><?= $present ?></span></td>
                            <td><span class="badge bg-warning"><?= $late ?></span></td>
                            <td><span class="badge bg-danger"><?= $absent ?></span></td>
                            <td><span class="badge bg-info"><?= $leave ?></span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:8px;min-width:60px;">
                                        <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
                                    </div>
                                    <small><?= $pct ?>%</small>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
