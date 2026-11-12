<?php
/** Tableau de bord du directeur de discipline. */
$pageTitle = 'Dashboard';
$sev = ['minor' => 'info', 'moderate' => 'warning', 'serious' => 'danger'];
?>
<div class="dash-hello mb-4">
    <div class="row align-items-center g-3">
        <div class="col-lg-8">
            <h3><?= e(t('Hello, :name', ['name' => Auth::name()])) ?></h3>
            <p><?= e(role_label('discipline')) ?> · <?= e(ldate('l j F Y')) ?></p>
        </div>
        <div class="col-lg-4 text-lg-end"><a href="<?= url('discipline/index') ?>" class="btn"><i class="fas fa-gavel me-1"></i>Record Incident</a></div>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php foreach ([
        ['user-xmark', 'danger', $stats['absent'], 'Absent today', 'attendance/index'],
        ['clock', 'warning', $stats['late'], 'Late today', 'attendance/index'],
        ['gavel', 'primary', $stats['month'], 'Incidents this month', 'discipline/index'],
        ['folder-open', 'info', $stats['open'], 'Open incidents', 'discipline/index?status=open'],
        ['triangle-exclamation', 'danger', $stats['serious'], 'Serious cases open', 'discipline/index?severity=serious'],
        ['user-clock', 'warning', $stats['teachers_absent'], 'Teachers absent today', 'attendance/teacher'],
    ] as $k): ?>
        <div class="col-sm-6 col-xl-4">
            <a href="<?= url($k[4]) ?>" class="card stat-card kpi2 fade-up">
                <span><span class="n d-block"><?= nf($k[2]) ?></span><span class="l d-block"><?= e(t($k[3])) ?></span></span><span class="round r-<?= $k[1] === 'success' || $k[1] === 'primary' ? 'primary' : $k[1] ?>"><i class="fas fa-<?= $k[0] ?>"></i></span>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="card mb-4">
            <div class="card-header"><h6 class="fw-bold mb-0">Attendance Trend</h6></div>
            <div class="card-body"><div class="chart-box"><canvas id="attChart" height="260"></canvas></div></div>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between"><h6 class="fw-bold mb-0">Latest incidents</h6><a href="<?= url('discipline/index') ?>" class="small">View all</a></div>
            <div class="card-body">
                <?php if (!$recent): ?><p class="text-muted mb-0">No incidents recorded.</p><?php endif; ?>
                <?php foreach ($recent as $r): ?>
                    <div class="list-row">
                        <span class="stat-icon tone-<?= $sev[$r['severity']] ?? 'info' ?>" style="width:40px;height:40px;font-size:.95rem"><i class="fas fa-gavel"></i></span>
                        <div class="flex-grow-1"><strong><?= e(studentName($r)) ?></strong><div class="small text-muted"><?= e($r['type']) ?> · <?= e($r['class_name'] ?? '') ?> · <?= e(formatDate($r['incident_date'])) ?></div></div>
                        <?= statusBadge($r['status']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card mb-4">
            <div class="card-header"><h6 class="fw-bold mb-0">Most absent (30 days)</h6></div>
            <div class="card-body">
                <?php if (!$topAbsent): ?><p class="text-muted mb-0">Nothing to show.</p><?php endif; ?>
                <?php foreach ($topAbsent as $s): ?>
                    <div class="list-row">
                        <?= avatar(null, studentName($s), 40) ?>
                        <div class="flex-grow-1"><strong><?= e(studentName($s)) ?></strong><div class="small text-muted"><?= e($s['class_name'] ?? '') ?></div></div>
                        <span class="badge bg-danger-subtle text-danger"><?= (int) $s['total'] ?> <?= e(t('Absent')) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="row g-3">
            <?php foreach ([['clipboard-check', 'Attendance', 'attendance/index'], ['user-check', 'Teacher Attendance', 'attendance/teacher'], ['chart-line', 'Attendance Reports', 'attendance/report'], ['calendar-days', 'Calendar & Events', 'events/index']] as $l): ?>
                <div class="col-6"><a class="quick-link" href="<?= url($l[2]) ?>"><span class="stat-icon tone-primary"><i class="fas fa-<?= $l[0] ?>"></i></span><?= e(t($l[1])) ?></a></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new Chart(document.getElementById('attChart'), { type: 'line', data: { labels: <?= json_encode(array_column($attendanceTrend, 'date'), JSON_UNESCAPED_UNICODE) ?>,
        datasets: [
            { label: <?= json_encode(t('Present'), JSON_UNESCAPED_UNICODE) ?>, data: <?= json_encode(array_column($attendanceTrend, 'present')) ?>, borderColor: '#0fa968' },
            { label: <?= json_encode(t('Absent'), JSON_UNESCAPED_UNICODE) ?>, data: <?= json_encode(array_column($attendanceTrend, 'absent')) ?>, borderColor: '#ff5b5c' },
            { label: <?= json_encode(t('Leave'), JSON_UNESCAPED_UNICODE) ?>, data: <?= json_encode(array_column($attendanceTrend, 'leave')) ?>, borderColor: '#f7c32e', fill: false }] } });
});
</script>
