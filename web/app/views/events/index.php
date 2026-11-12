<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Calendar & Events';
?>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="fw-bold mb-0"><i class="fas fa-calendar-alt me-2 text-primary"></i>School Calendar</h6></div>
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white"><h6 class="fw-bold mb-0">Upcoming Events</h6></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Date</th><th>Title</th><th>Type</th><th>Location</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                        <?php if (empty($events)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No events.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($events as $e): ?>
                            <tr>
                                <td class="text-nowrap"><?= formatDate($e['event_date']) ?></td>
                                <td>
                                    <strong><?= e($e['title']) ?></strong>
                                    <div class="small text-muted"><?= e($e['description'] ?: '') ?></div>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= e(ucfirst($e['event_type'])) ?></span></td>
                                <td><?= e($e['location'] ?: '—') ?></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('events/destroy/' . $e['id']) ?>')"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><h6 class="fw-bold mb-0"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Event</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('events/store') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-2"><input type="text" name="title" class="form-control" placeholder="Event title" required></div>
                    <div class="mb-2"><textarea name="description" class="form-control" rows="2" placeholder="Description"></textarea></div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><input type="date" name="event_date" class="form-control" required></div>
                        <div class="col-6"><input type="time" name="event_time" class="form-control"></div>
                    </div>
                    <div class="mb-2">
                        <select name="event_type" class="form-select">
                            <?php foreach (['event', 'meeting', 'exam', 'sport', 'holiday'] as $t): ?>
                                <option value="<?= $t ?>"><?= ucfirst($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><input type="text" name="location" class="form-control" placeholder="Location"></div>
                    <button class="btn btn-primary w-100">Add Event</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="fw-bold mb-0"><i class="fas fa-umbrella-beach me-2 text-danger"></i>Holidays</h6></div>
            <div class="card-body">
                <form method="POST" action="<?= url('events/store-holiday') ?>" class="mb-3">
                    <?= csrf_field() ?>
                    <div class="mb-2"><input type="text" name="title" class="form-control" placeholder="Holiday name" required></div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><input type="date" name="start_date" class="form-control" required></div>
                        <div class="col-6"><input type="date" name="end_date" class="form-control"></div>
                    </div>
                    <div class="mb-2"><textarea name="description" class="form-control" rows="2" placeholder="Description"></textarea></div>
                    <button class="btn btn-outline-danger w-100">Add Holiday</button>
                </form>

                <ul class="list-group list-group-flush">
                    <?php if (empty($holidays)): ?>
                        <li class="list-group-item text-muted small">No holidays.</li>
                    <?php endif; ?>
                    <?php foreach ($holidays as $h): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center small px-0">
                            <div>
                                <strong><?= e($h['title']) ?></strong>
                                <div class="text-muted"><?= formatDate($h['start_date']) ?><?= $h['end_date'] && $h['end_date'] !== $h['start_date'] ? ' &rarr; ' . formatDate($h['end_date']) : '' ?></div>
                            </div>
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('events/destroy-holiday/' . $h['id']) ?>')"><i class="fas fa-trash"></i></button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar JS is loaded globally by layouts/app_layout.php -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cal = document.getElementById('calendar');
    if (!cal || typeof FullCalendar === 'undefined') return;
    const calendar = new FullCalendar.Calendar(cal, {
        initialView: 'dayGridMonth',
        height: 'auto',
        events: <?= $calendar ?: '[]' ?>,
        eventClick: function (info) { if (info.event.url) info.jsEvent.preventDefault(); },
    });
    calendar.render();
});
</script>
