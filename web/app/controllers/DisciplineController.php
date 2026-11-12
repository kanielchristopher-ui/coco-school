<?php
/**
 * ============================================================
 *  DisciplineController
 *  Module du directeur de discipline : incidents, sanctions,
 *  convocation des parents, suivi par élève.
 * ============================================================
 */

declare(strict_types=1);

class DisciplineController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('discipline.manage');
        $db = Database::getInstance();

        $classId  = (int) ($_GET['class_id'] ?? 0);
        $status   = (string) ($_GET['status'] ?? '');
        $severity = (string) ($_GET['severity'] ?? '');
        $search   = trim((string) ($_GET['q'] ?? ''));

        $where = ['1=1'];
        $params = [];
        if ($classId > 0) { $where[] = 's.class_id = ?'; $params[] = $classId; }
        if (in_array($status, ['open', 'resolved'], true)) { $where[] = 'd.status = ?'; $params[] = $status; }
        if (in_array($severity, ['minor', 'moderate', 'serious'], true)) { $where[] = 'd.severity = ?'; $params[] = $severity; }
        if ($search !== '') {
            $where[] = '(s.first_name LIKE ? OR s.middle_name LIKE ? OR s.last_name LIKE ? OR d.type LIKE ?)';
            array_push($params, "%$search%", "%$search%", "%$search%", "%$search%");
        }

        $records = $db->fetchAll(
            'SELECT d.*, s.first_name, s.middle_name, s.last_name, s.official_id, c.name AS class_name,
                    u.first_name AS by_first, u.last_name AS by_last
             FROM discipline_records d
             INNER JOIN students s ON s.id = d.student_id
             LEFT JOIN classes c ON c.id = s.class_id
             LEFT JOIN users u ON u.id = d.recorded_by
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY d.incident_date DESC, d.id DESC LIMIT 300',
            $params
        );

        $this->render('discipline/index', [
            'records'  => $records,
            'classes'  => $db->fetchAll('SELECT id, name FROM classes ORDER BY FIELD(cycle, \'maternel\', \'primaire\', \'secondaire\'), CAST(numeric_name AS UNSIGNED), name'),
            'students' => $db->fetchAll('SELECT s.id, s.first_name, s.middle_name, s.last_name, c.name AS class_name FROM students s LEFT JOIN classes c ON c.id = s.class_id WHERE s.status = \'active\' ORDER BY s.first_name'),
            'filters'  => ['class_id' => $classId, 'status' => $status, 'severity' => $severity, 'q' => $search],
        ]);
    }

    public function store(): void
    {
        CSRF::check();
        Auth::requirePermission('discipline.manage');

        $studentId = (int) ($_POST['student_id'] ?? 0);
        $type      = trim((string) ($_POST['type'] ?? ''));
        $date      = trim((string) ($_POST['incident_date'] ?? ''));
        $severity  = (string) ($_POST['severity'] ?? 'minor');
        $sanction  = trim((string) ($_POST['sanction'] ?? ''));

        if ($studentId <= 0 || $type === '' || !Validator::isValidDate($date)) {
            Flash::set('error', 'Student, incident type and a valid date are required.');
            $this->redirect('discipline/index');
        }
        if (!in_array($severity, ['minor', 'moderate', 'serious'], true)) {
            $severity = 'minor';
        }

        $db = Database::getInstance();
        $id = $db->insert('discipline_records', [
            'student_id'      => $studentId,
            'incident_date'   => $date,
            'type'            => $type,
            'severity'        => $severity,
            'description'     => trim((string) ($_POST['description'] ?? '')),
            'sanction'        => $sanction,
            'parent_notified' => isset($_POST['parent_notified']) ? 1 : 0,
            'status'          => 'open',
            'recorded_by'     => Auth::id(),
        ]);

        // Prévenir l'élève / le parent via les notifications si un compte existe
        $student = $db->fetch('SELECT user_id FROM students WHERE id = ?', [$studentId]);
        if (!empty($student['user_id'])) {
            (new Notification())->create([
                'user_id' => (int) $student['user_id'],
                'title'   => 'Disciplinary notice',
                'message' => $type . ($sanction !== '' ? ' — ' . $sanction : ''),
                'type'    => 'warning',
            ]);
        }

        ActivityLog::log('create', 'discipline', 'Incident #' . $id . ' recorded');
        Flash::set('success', 'Incident recorded.');
        $this->redirect('discipline/index');
    }

    public function resolve(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('discipline.manage');
        Database::getInstance()->update('discipline_records', ['status' => 'resolved'], 'id = ?', [$id]);
        Flash::set('success', 'Incident marked as resolved.');
        $this->redirect('discipline/index');
    }

    public function destroy(int $id): void
    {
        Auth::requirePermission('discipline.manage');
        Database::getInstance()->delete('discipline_records', 'id = ?', [$id]);
        ActivityLog::log('delete', 'discipline', 'Incident #' . $id . ' deleted');
        Flash::set('success', 'Incident deleted.');
        $this->redirect('discipline/index');
    }
}
