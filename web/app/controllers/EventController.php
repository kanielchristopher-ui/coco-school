<?php
/**
 * ============================================================
 *  EventController
 *  School calendar, events, meetings and holidays.
 * ============================================================
 */

declare(strict_types=1);

class EventController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('events.manage');

        $db = Database::getInstance();
        $events = $db->fetchAll('SELECT * FROM events ORDER BY event_date DESC LIMIT 200');
        $holidays = (new Holiday())->all('start_date');

        // Build JSON for the calendar widget.
        $calendar = [];
        foreach ($events as $e) {
            $calendar[] = [
                'title' => $e['title'],
                'start' => $e['event_date'],
                'url'   => '#',
                'color'=> match ($e['event_type']) {
                    'holiday' => '#dc3545',
                    'meeting' => '#0d6efd',
                    'exam'    => '#6f42c1',
                    'sport'   => '#fd7e14',
                    default   => '#198754',
                },
            ];
        }
        foreach ($holidays as $h) {
            $calendar[] = ['title' => $h['title'] . ' (Holiday)', 'start' => $h['start_date'], 'end' => $h['end_date'], 'color' => '#dc3545'];
        }

        $this->render('events/index', [
            'events'   => $events,
            'holidays' => $holidays,
            'calendar' => json_encode($calendar),
        ]);
    }

    public function store(): void
    {
        CSRF::check();
        Auth::requirePermission('events.manage');

        $title = trim($_POST['title'] ?? '');
        $date = trim($_POST['event_date'] ?? '');
        if ($title === '' || $date === '') {
            Flash::set('error', 'Title and date are required.');
            $this->redirect('events/index');
        }

        $allowedTypes = ['event', 'meeting', 'exam', 'sport', 'holiday'];
        $type = trim($_POST['event_type'] ?? 'event');
        if (!in_array($type, $allowedTypes, true)) {
            $type = 'event';
        }

        Database::getInstance()->insert('events', [
            'title'       => $title,
            'description' => trim($_POST['description'] ?? ''),
            'event_date'  => $date,
            'event_time'  => trim($_POST['event_time'] ?? '') ?: null,
            'event_type'  => $type,
            'location'    => trim($_POST['location'] ?? ''),
            'created_by'  => Auth::id(),
        ]);

        $this->logActivity('create', 'events', 'Created event: ' . $title);
        Flash::set('success', 'Event added to the calendar.');
        $this->redirect('events/index');
    }

    public function destroy(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('events.manage');

        Database::getInstance()->delete('events', 'id = ?', [$id]);
        Flash::set('success', 'Event deleted.');
        $this->redirect('events/index');
    }

    public function storeHoliday(): void
    {
        CSRF::check();
        Auth::requirePermission('events.manage');

        $title = trim($_POST['title'] ?? '');
        $start = trim($_POST['start_date'] ?? '');
        $end   = trim($_POST['end_date'] ?? '') ?: $start;

        if ($title === '' || $start === '') {
            Flash::set('error', 'Holiday title and start date are required.');
            $this->redirect('events/index');
        }

        (new Holiday())->create([
            'title'       => $title,
            'start_date'  => $start,
            'end_date'    => $end,
            'description' => trim($_POST['description'] ?? ''),
        ]);
        Flash::set('success', 'Holiday added.');
        $this->redirect('events/index');
    }

    public function destroyHoliday(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('events.manage');

        (new Holiday())->delete($id);
        Flash::set('success', 'Holiday deleted.');
        $this->redirect('events/index');
    }
}
