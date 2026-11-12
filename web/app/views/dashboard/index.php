<?php
/** Tableau de bord de la direction : administrateur, préfet, proviseur (directeur des études). */
$pageTitle = 'Dashboard';
$role = Auth::role();
$canFinance = Auth::gate(['admin', 'principal', 'prefet', 'director', 'cashier']);
$extra = $extra ?? [];
$cycleNames = ['maternel' => 'Nursery', 'primaire' => 'Primary', 'secondaire' => 'Secondary'];
$cycleColors = ['#0fa968', '#f7c32e', '#14b8c4'];

$kpi = function (string $icon, string $tone, string $value, string $label, string $sub, ?string $href = null): string {
    $tag = $href ? 'a href="' . e(url($href)) . '"' : 'div';
    $end = $href ? 'a' : 'div';
    return '<div class="col-6 col-xl-4"><' . $tag . ' class="card stat-card kpi2 fade-up"><span><span class="n d-block">' . $value . '</span><span class="l d-block">' . e(t($label)) . '</span><small>' . e($sub) . '</small></span>'
        . '<span class="round r-' . $tone . '"><i class="fas fa-' . $icon . '"></i></span></' . $end . '></div>';
};
$totalStudents = max(1, (int) $stats['total_students']);
$events = array_map(fn($e) => ['title' => $e['title'], 'start' => $e['event_date']], $upcomingEvents);
?>
<div class="row g-4 mb-4">
    <div class="col-xl-4">
        <div class="dash-hello h-100 d-flex flex-column justify-content-center" style="min-height:230px">
            <?php include APP_PATH . '/views/layouts/partials/school_art.php'; ?>
            <div style="position:relative;z-index:1;max-width:62%">
                <h3><?= e(t('Hello, :name', ['name' => Auth::name()])) ?></h3>
                <p class="mb-3"><?= e(role_label($role)) ?> · <?= e(settings('academic_year', '')) ?></p>
                <?php if ($role === 'proviseur'): ?>
                    <a href="<?= url('results/review-queue') ?>" class="btn btn-sm"><?= e(t('Review & Publish')) ?></a>
                <?php else: ?>
                    <a href="<?= url('students/create') ?>" class="btn btn-sm"><?= e(t('Add Student')) ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="row g-3">
            <?= $kpi('user-graduate', 'primary', nf($stats['total_students']), 'Total Students', e(t('Active')), 'students/index') ?>
            <?= $kpi('chalkboard-user', 'info', nf($stats['total_teachers']), 'Total Teachers', e(t('Active')), 'teachers/index') ?>
            <?= $kpi('school', 'accent', nf($stats['total_classes']), 'Total Classes', e(t('Sections')) . ' ' . nf($stats['total_sections'] ?? 0), 'classes/index') ?>
            <?= $kpi('clipboard-check', 'primary', nf($stats['attendance_today']), 'Attendance Today', round(100 * $stats['attendance_today'] / $totalStudents) . ' %', 'attendance/index') ?>
            <?php if ($canFinance): ?>
                <?= $kpi('hand-holding-dollar', 'primary', e(currency($stats['fees_collected'])), 'Fees Collected', e(t('This month')), 'finance/fees') ?>
                <?= $kpi('file-invoice-dollar', 'danger', e(currency($stats['pending_fees'])), 'Pending Fees', e(t('To collect')), 'finance/fees') ?>
            <?php else: ?>
                <?= $kpi('file-signature', 'danger', nf((int) ($extra['pending_review'] ?? 0)), 'Results awaiting review', e(t('Exams')), 'results/review-queue') ?>
                <?= $kpi('gavel', 'warning', nf((int) ($extra['open_incidents'] ?? 0)), 'Open incidents', e(t('Discipline')), null) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h6 class="fw-bold mb-0"><?= e(t('Overview')) ?></h6>
                <div class="seg" id="mainSeg"><button type="button" class="active" data-k="adm"><?= e(t('Admissions')) ?></button><?php if ($canFinance): ?><button type="button" data-k="fee"><?= e(t('Collected')) ?></button><?php endif; ?></div>
            </div>
            <div class="card-body"><div class="chart-box"><canvas id="mainChart" height="290"></canvas></div></div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card h-100 cal-card">
            <div class="card-header"><h6 class="fw-bold mb-0"><?= e(t('Calendar')) ?></h6></div>
            <div class="card-body"><div id="miniCal"></div></div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header"><h6 class="fw-bold mb-0"><?= e(t('Attendance Trend')) ?></h6></div>
            <div class="card-body"><div class="chart-box"><canvas id="attChart" height="270"></canvas></div></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-header"><h6 class="fw-bold mb-0"><?= e(t('Students by cycle')) ?></h6></div>
            <div class="card-body">
                <div class="chart-box"><canvas id="cycleChart" height="190"></canvas></div>
                <div class="bar-list mt-3">
                    <?php foreach (($extra['by_cycle'] ?? []) as $i => $c): $pct = round(100 * (int) $c['total'] / $totalStudents); ?>
                        <div class="bl"><div class="bl-top"><span><?= e(t($cycleNames[$c['cycle']] ?? $c['cycle'])) ?></span><span><?= nf($c['total']) ?></span></div>
                            <div class="bl-track"><div class="bl-fill" data-w="<?= $pct ?>" style="background:<?= $cycleColors[$i % 3] ?>"></div></div></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between"><h6 class="fw-bold mb-0"><?= e(t('Upcoming Events')) ?></h6><a href="<?= url('events/index') ?>" class="small"><?= e(t('View all')) ?></a></div>
            <div class="card-body">
                <?php if (!$upcomingEvents): ?><p class="text-muted mb-0"><?= e(t('No upcoming events.')) ?></p><?php endif; ?>
                <?php foreach ($upcomingEvents as $ev): $ts = strtotime($ev['event_date']); ?>
                    <div class="list-row">
                        <div class="date-chip"><?= e(ldate('d', $ts)) ?><small><?= e(ldate('M', $ts)) ?></small></div>
                        <div><strong><?= e($ev['title']) ?></strong><div class="small text-muted"><?= e($ev['location'] ?? '') ?></div></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header"><h6 class="fw-bold mb-0"><?= e(t('Recent Activities')) ?></h6></div>
            <div class="card-body">
                <?php if (!$activities): ?><p class="text-muted mb-0"><?= e(t('No activity recorded yet.')) ?></p><?php endif; ?>
                <?php foreach ($activities as $a): ?>
                    <div class="list-row">
                        <span class="stat-icon tone-primary" style="width:40px;height:40px;font-size:.95rem"><i class="fas fa-bolt"></i></span>
                        <div class="flex-grow-1"><div><?= e($a['description'] ?? $a['action']) ?></div>
                            <small class="text-muted"><?= e(trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''))) ?> · <?= e(timeAgo($a['created_at'])) ?></small></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="row g-3">
            <?php
            $links = [['users', 'Students', 'students/index', Auth::can('students.manage')], ['school', 'Classes', 'classes/index', Auth::can('classes.manage')],
                      ['file-signature', 'Exams', 'exams/index', Auth::can('exams.manage')], ['gavel', 'Discipline', 'discipline/index', Auth::can('discipline.manage')],
                      ['money-check-dollar', 'Fee Collection', 'finance/collections', $canFinance], ['chart-pie', 'Reports', 'reports/index', Auth::can('reports.view')]];
            foreach ($links as $l): if (!$l[3]) continue; ?>
                <div class="col-6"><a class="quick-link" href="<?= url($l[2]) ?>"><span class="stat-icon tone-primary"><i class="fas fa-<?= $l[0] ?>"></i></span><?= e(t($l[1])) ?></a></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var adm = { labels: <?= json_encode(array_column($studentGrowth, 'month'), JSON_UNESCAPED_UNICODE) ?>, datasets: [{ label: <?= json_encode(t('Admissions'), JSON_UNESCAPED_UNICODE) ?>, data: <?= json_encode(array_column($studentGrowth, 'count')) ?>, borderColor: '#0fa968' }] };
    var fee = { labels: <?= json_encode(array_column($feeTrend, 'month'), JSON_UNESCAPED_UNICODE) ?>, datasets: [{ label: <?= json_encode(t('Collected'), JSON_UNESCAPED_UNICODE) ?>, data: <?= json_encode(array_column($feeTrend, 'total')) ?>, backgroundColor: '#0fa968' }] };
    var main = null, canvas = document.getElementById('mainChart');
    function show(k) {
        if (main) main.destroy();
        main = new Chart(canvas, k === 'fee' ? { type: 'bar', data: fee } : { type: 'line', data: adm });
    }
    show('adm');
    document.querySelectorAll('#mainSeg button').forEach(function (b) {
        b.addEventListener('click', function () {
            document.querySelectorAll('#mainSeg button').forEach(function (x) { x.classList.remove('active'); });
            b.classList.add('active'); show(b.getAttribute('data-k'));
        });
    });
    new Chart(document.getElementById('cycleChart'), { type: 'doughnut', data: { labels: <?= json_encode(array_map(fn($c) => t($cycleNames[$c['cycle']] ?? $c['cycle']), $extra['by_cycle'] ?? []), JSON_UNESCAPED_UNICODE) ?>, datasets: [{ data: <?= json_encode(array_map(fn($c) => (int) $c['total'], $extra['by_cycle'] ?? [])) ?>, backgroundColor: <?= json_encode($cycleColors) ?> }] } });
    new Chart(document.getElementById('attChart'), { type: 'line', data: { labels: <?= json_encode(array_column($attendanceTrend, 'date'), JSON_UNESCAPED_UNICODE) ?>,
        datasets: [
            { label: <?= json_encode(t('Present'), JSON_UNESCAPED_UNICODE) ?>, data: <?= json_encode(array_column($attendanceTrend, 'present')) ?>, borderColor: '#0fa968' },
            { label: <?= json_encode(t('Absent'), JSON_UNESCAPED_UNICODE) ?>, data: <?= json_encode(array_column($attendanceTrend, 'absent')) ?>, borderColor: '#ff5b5c' },
            { label: <?= json_encode(t('Leave'), JSON_UNESCAPED_UNICODE) ?>, data: <?= json_encode(array_column($attendanceTrend, 'leave')) ?>, borderColor: '#f7c32e', fill: false }] } });
    new FullCalendar.Calendar(document.getElementById('miniCal'), { events: <?= json_encode($events, JSON_UNESCAPED_UNICODE) ?> }).render();
    setTimeout(function () { document.querySelectorAll('.bl-fill').forEach(function (f) { f.style.width = f.getAttribute('data-w') + '%'; }); }, 200);
});
</script>
