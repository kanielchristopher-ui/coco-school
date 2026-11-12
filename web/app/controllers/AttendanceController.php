<?php
/**
 * ============================================================
 *  AttendanceController
 *  Mark and report student & teacher attendance.
 * ============================================================
 */

declare(strict_types=1);

class AttendanceController extends Controller
{
    protected Attendance $attendance;

    public function __construct()
    {
        parent::__construct();
        $this->attendance = new Attendance();
    }

    // ----------------------------------------------------------
    //  Student attendance
    // ----------------------------------------------------------

    public function index(): void
    {
        $isTeacher = Auth::role() === 'teacher';

        if ($isTeacher) {
            Auth::requirePermission('manage_own_attendance');
            $assignedClasses = Auth::assignedClassIds();
        } else {
            Auth::requirePermission('attendance.manage');
            $assignedClasses = [];
        }

        $classId  = (int) ($_GET['class_id'] ?? 0);
        $sectionId = (int) ($_GET['section_id'] ?? 0);
        $date     = trim($_GET['date'] ?? date('Y-m-d'));
        $subjectId = (int) ($_GET['subject_id'] ?? 0);

        // Teachers may only mark attendance for their assigned classes.
        if ($isTeacher) {
            if ($classId === 0) {
                $classId = $assignedClasses[0] ?? 0;
            } elseif (!in_array($classId, $assignedClasses, true)) {
                $this->renderError('You can only mark attendance for your assigned classes.');
                return;
            }
        }

        $rows = [];
        $students = [];
        $subjects = [];
        if ($classId) {
            $students = Database::getInstance()->fetchAll(
                'SELECT id, official_id, first_name, last_name
                 FROM students WHERE class_id = ? AND status = "active" ORDER BY id',
                [$classId]
            );
            $rows = $this->attendance->forDate($classId, $sectionId ?: null, $date, $subjectId ?: null);
            $subjectWhere  = 'WHERE class_id = ?';
            $subjectParams = [$classId];
            if ($isTeacher) {
                // Teachers only see the subjects assigned to them in this class.
                $subjectWhere  .= ' AND teacher_id = ?';
                $subjectParams[] = Auth::teacherProfileId();
            }
            $subjects = Database::getInstance()->fetchAll(
                'SELECT id, name FROM subjects ' . $subjectWhere . ' ORDER BY name',
                $subjectParams
            );
        }

        // Re-open the sheet with the previously saved subject when none was chosen.
        if ($subjectId === 0) {
            foreach ($rows as $r) {
                if ((int) ($r['subject_id'] ?? 0) > 0) {
                    $subjectId = (int) $r['subject_id'];
                    break;
                }
            }
            if ($subjectId > 0) {
                $rows = $this->attendance->forDate($classId, $sectionId ?: null, $date, $subjectId);
            }
        }

        $subjectName = null;
        foreach ($subjects as $s) {
            if ((int) $s['id'] === $subjectId) {
                $subjectName = $s['name'];
                break;
            }
        }

        $classes = $isTeacher
            ? array_values(array_filter(
                (new ClassRoom())->all('name'),
                fn($c) => in_array((int) $c['id'], $assignedClasses, true)
              ))
            : (new ClassRoom())->all('name');

        $this->render('attendance/index', [
            'classes'    => $classes,
            'classId'    => $classId,
            'sectionId'  => $sectionId,
            'date'       => $date,
            'subjectId'  => $subjectId,
            'subjectName'=> $subjectName,
            'subjects'   => $subjects,
            'students'   => $students,
            'rows'       => $rows,
            'sections'   => $classId ? (new ClassRoom())->sections($classId) : [],
        ]);
    }

    /**
     * Save the attendance sheet.
     */
    public function store(): void
    {
        CSRF::check();

        $classId  = (int) ($_POST['class_id'] ?? 0);
        $sectionId = (int) ($_POST['section_id'] ?? 0);
        $date     = trim($_POST['date'] ?? date('Y-m-d'));

        $isTeacher = Auth::role() === 'teacher';
        if ($isTeacher) {
            Auth::requirePermission('manage_own_attendance');
            if ($classId === 0 || !in_array($classId, Auth::assignedClassIds(), true)) {
                Flash::set('error', 'You can only save attendance for your assigned classes.');
                $this->redirect('attendance/index');
            }
        } else {
            Auth::requirePermission('attendance.manage');
        }

        // The teacher-selected subject is saved with each attendance record.
        // Teachers may only save a subject that is assigned to them within the
        // selected class (subjects.teacher_id = current teacher AND
        // subjects.class_id = selected class). A forged/unauthorized subject_id
        // sent via POST is rejected outright. Admins keep the existing
        // class-only validation.
        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        if ($subjectId > 0) {
            $sql  = 'SELECT id FROM subjects WHERE id = ? AND class_id = ?';
            $args = [$subjectId, $classId];
            if ($isTeacher) {
                $sql .= ' AND teacher_id = ?';
                $args[] = Auth::teacherProfileId();
            }
            $valid = Database::getInstance()->fetchColumn($sql, $args);
            if (!$valid) {
                if ($isTeacher) {
                    Flash::set('error', 'You can only save attendance for subjects assigned to you in this class.');
                    $this->redirect('attendance/index');
                }
                $subjectId = 0;
            }
        }
        $subjectId = $subjectId ?: null;

        $records = [];
        foreach (($_POST['status'] ?? []) as $studentId => $status) {
            $status = in_array($status, ['present', 'absent', 'late', 'leave'], true) ? $status : 'present';
            $records[] = [
                'student_id' => (int) $studentId,
                'class_id'   => $classId,
                'section_id' => $sectionId ?: null,
                'subject_id' => $subjectId,
                'date'       => $date,
                'status'     => $status,
                'remark'     => trim($_POST['remark'][$studentId] ?? ''),
                'taken_by'   => $this->userId(),
            ];
        }

        if (empty($records)) {
            Flash::set('error', 'No students were submitted for attendance.');
            $this->redirect('attendance/index?class_id=' . $classId . '&section_id=' . $sectionId . '&date=' . $date . '&subject_id=' . (int) $subjectId);
        }

        // Capture each student's current status before saving so we only notify
        // the students whose attendance was actually submitted or changed.
        // Re-saving the same sheet (identical statuses) produces no duplicates.
        $existingStatuses = [];
        if ($records) {
            foreach (Database::getInstance()->fetchAll(
                'SELECT student_id, subject_id, status FROM attendance WHERE class_id = ? AND date = ?',
                [$classId, $date]
            ) as $row) {
                $existingStatuses[(int) $row['student_id'] . ':' . ((int) $row['subject_id'] ?: 0)] = $row['status'];
            }
        }

        $affected = [];
        foreach ($records as $r) {
            $key = (int) $r['student_id'] . ':' . ((int) $r['subject_id'] ?: 0);
            $prevStatus = $existingStatuses[$key] ?? null;
            if ($prevStatus === null || $prevStatus !== $r['status']) {
                $affected[] = $r;
            }
        }

        $this->attendance->bulkUpsert($records);

        // Notify each affected student through their linked user account, so
        // attendance is pushed into the communication network automatically
        // (no manual linking required). Runs only after a successful save.
        if ($affected) {
            $studentIds = implode(',', array_map('intval', array_unique(array_column($affected, 'student_id'))));
            $linked = [];
            foreach (Database::getInstance()->fetchAll(
                'SELECT s.id AS student_id, u.id AS user_id
                 FROM students s
                 INNER JOIN users u ON u.id = s.user_id
                 WHERE s.id IN (' . $studentIds . ') AND u.status = "active"'
            ) as $row) {
                $linked[$row['student_id']] = (int) $row['user_id'];
            }
            $className = (string) (Database::getInstance()->fetchColumn('SELECT name FROM classes WHERE id = ?', [$classId]) ?: '');
            $labels = ['present' => 'present', 'absent' => 'absent', 'late' => 'late', 'leave' => 'on leave'];
            $notification = new Notification();
            foreach ($affected as $r) {
                $userId = $linked[(int) $r['student_id']] ?? 0;
                if ($userId < 1) {
                    continue;
                }
                $message = 'You were marked ' . ($labels[$r['status']] ?? $r['status'])
                    . ' on ' . formatDate($r['date'])
                    . ($className !== '' ? ' (' . $className . ')' : '') . '.';
                $notification->push($userId, 'Attendance Update', $message, 'attendance', 'dashboard/index');
            }
        }

        $this->logActivity('update', 'attendance', 'Marked attendance for ' . count($records) . ' students on ' . $date);

        Flash::set('success', 'Attendance saved for ' . count($records) . ' students.');
        $this->redirect('attendance/index?class_id=' . $classId . '&section_id=' . $sectionId . '&date=' . $date . '&subject_id=' . (int) $subjectId);
    }

    /**
     * Student portal: the logged-in student's own attendance history.
     * Read-only view of records taken by teachers. No edit or delete
     * controls are rendered, so students can never alter attendance.
     */
    public function myAttendance(): void
    {
        Auth::requirePermission('view_own_attendance');

        $student = (new Student())->firstWhere('user_id', Auth::id());
        if (!$student) {
            Flash::set('warning', 'No student profile is linked to this account.');
            $this->redirect('dashboard/index');
            return;
        }

        $rows = Database::getInstance()->fetchAll(
            'SELECT a.date, a.status,
                    c.name AS class_name, sec.name AS section_name,
                    su.name AS subject_name
             FROM attendance a
             LEFT JOIN classes c ON c.id = a.class_id
             LEFT JOIN sections sec ON sec.id = a.section_id
             LEFT JOIN subjects su ON su.id = a.subject_id
             WHERE a.student_id = ?
             ORDER BY a.date DESC',
            [(int) $student['id']]
        );

        $this->render('attendance/my_attendance', [
            'student' => $student,
            'rows'    => $rows,
        ]);
    }

    // ----------------------------------------------------------
    //  Teacher attendance
    // ----------------------------------------------------------

    public function teacher(): void
    {
        Auth::requireRole(['admin', 'principal', 'prefet', 'director', 'proviseur', 'discipline']);

        $date = trim($_GET['date'] ?? date('Y-m-d'));
        $teachers = Database::getInstance()->fetchAll(
            'SELECT t.id, t.official_id, t.first_name, t.last_name, t.department,
                    (SELECT GROUP_CONCAT(su.name ORDER BY su.name SEPARATOR ", ")
                       FROM subjects su WHERE su.teacher_id = t.id) AS subjects
             FROM teachers t WHERE t.status = "active" ORDER BY t.id'
        );

        $db = Database::getInstance();
        $rows = $db->fetchAll('SELECT * FROM teacher_attendance WHERE date = ?', [$date]);
        $existing = [];
        foreach ($rows as $r) {
            $existing[$r['teacher_id']] = $r;
        }

        $this->render('attendance/teacher', [
            'teachers' => $teachers,
            'existing' => $existing,
            'date'     => $date,
        ]);
    }

    public function storeTeacher(): void
    {
        CSRF::check();
        Auth::requireRole(['admin', 'principal', 'prefet', 'director', 'proviseur', 'discipline']);

        $date = trim($_POST['date'] ?? date('Y-m-d'));
        $db = Database::getInstance();

        foreach (($_POST['status'] ?? []) as $teacherId => $status) {
            $status = in_array($status, ['present', 'absent', 'late', 'leave'], true) ? $status : 'present';

            $exists = $db->fetch('SELECT id FROM teacher_attendance WHERE teacher_id = ? AND date = ?', [(int) $teacherId, $date]);
            if ($exists) {
                $db->update('teacher_attendance', ['status' => $status, 'remark' => trim($_POST['remark'][$teacherId] ?? '')], 'id = :pk', [':pk' => $exists['id']]);
            } else {
                $db->insert('teacher_attendance', [
                    'teacher_id' => (int) $teacherId,
                    'date'       => $date,
                    'status'     => $status,
                    'remark'     => trim($_POST['remark'][$teacherId] ?? ''),
                ]);
            }
        }

        $this->logActivity('update', 'attendance', 'Marked teacher attendance on ' . $date);
        Flash::set('success', 'Teacher attendance saved.');
        $this->redirect('attendance/teacher?date=' . $date);
    }

    // ----------------------------------------------------------
    //  Reports
    // ----------------------------------------------------------

    public function report(): void
    {
        Auth::requireRole(['admin', 'principal', 'prefet', 'director', 'proviseur', 'discipline']);

        $type = trim($_GET['type'] ?? 'daily');   // daily | monthly | yearly
        $date = trim($_GET['date'] ?? date('Y-m-d'));
        $month = (int) ($_GET['month'] ?? (int) date('n'));
        $year = (int) ($_GET['year'] ?? (int) date('Y'));

        $db = Database::getInstance();
        $rows = [];

        if ($type === 'daily') {
            $rows = $db->fetchAll(
                'SELECT a.*, s.first_name, s.last_name, c.name AS class_name
                 FROM attendance a
                 INNER JOIN students s ON s.id = a.student_id
                 LEFT JOIN classes c ON c.id = a.class_id
                 WHERE a.date = ? ORDER BY s.first_name',
                [$date]
            );
        } elseif ($type === 'monthly') {
            $rows = $db->fetchAll(
                'SELECT s.id, s.first_name, s.last_name, c.name AS class_name,
                        SUM(a.status = "present") AS present, SUM(a.status = "late") AS late,
                        SUM(a.status = "absent") AS absent, SUM(a.status = "leave") AS `leave`, COUNT(*) AS total
                 FROM attendance a
                 INNER JOIN students s ON s.id = a.student_id
                 LEFT JOIN classes c ON c.id = a.class_id
                 WHERE MONTH(a.date) = ? AND YEAR(a.date) = ?
                 GROUP BY s.id ORDER BY s.first_name',
                [$month, $year]
            );
        } else {
            $rows = $db->fetchAll(
                'SELECT s.id, s.first_name, s.last_name, c.name AS class_name,
                        SUM(a.status = "present") AS present, SUM(a.status = "absent") AS absent, COUNT(*) AS total
                 FROM attendance a
                 INNER JOIN students s ON s.id = a.student_id
                 LEFT JOIN classes c ON c.id = a.class_id
                 WHERE YEAR(a.date) = ?
                 GROUP BY s.id ORDER BY s.first_name',
                [$year]
            );
        }

        $this->render('attendance/report', [
            'type'  => $type,
            'date'  => $date,
            'month' => $month,
            'year'  => $year,
            'rows'  => $rows,
        ]);
    }

    /**
     * Export the current attendance report.
     */
    public function export(): void
    {
        Auth::requireRole(['admin', 'principal', 'prefet', 'director', 'proviseur', 'discipline']);

        $type = trim($_GET['type'] ?? 'daily');
        $db = Database::getInstance();

        $headers = ['Student', 'Class', 'Date', 'Status'];
        $rows = [];

        if ($type === 'daily') {
            $date = trim($_GET['date'] ?? date('Y-m-d'));
            $rows = $db->fetchAll(
                'SELECT s.first_name, s.last_name, c.name AS class_name, a.date, a.status
                 FROM attendance a INNER JOIN students s ON s.id = a.student_id LEFT JOIN classes c ON c.id = a.class_id
                 WHERE a.date = ?', [$date]
            );
            $rows = array_map(fn($r) => [
                $r['first_name'] . ' ' . $r['last_name'], $r['class_name'], $r['date'], $r['status'],
            ], $rows);
        } else {
            $year = (int) ($_GET['year'] ?? date('Y'));
            $month = (int) ($_GET['month'] ?? date('n'));
            $headers = ['Student', 'Class', 'Present', 'Late', 'Absent', 'Leave'];
            $rows = $db->fetchAll(
                'SELECT s.first_name, s.last_name, c.name AS class_name,
                        SUM(a.status = "present") AS present, SUM(a.status = "late") AS late,
                        SUM(a.status = "absent") AS absent, SUM(a.status = "leave") AS `leave`
                 FROM attendance a INNER JOIN students s ON s.id = a.student_id LEFT JOIN classes c ON c.id = a.class_id
                 WHERE MONTH(a.date) = ? AND YEAR(a.date) = ? GROUP BY s.id',
                [$month, $year]
            );
            $rows = array_map(fn($r) => [
                $r['first_name'] . ' ' . $r['last_name'], $r['class_name'],
                (int) $r['present'], (int) $r['late'], (int) $r['absent'], (int) $r['leave'],
            ], $rows);
        }

        Exporter::csv('attendance_report_' . $type . '_' . date('Ymd'), $headers, $rows);
    }

    /**
     * Render a friendly error page.
     */
    protected function renderError(string $message): void
    {
        http_response_code(404);
        $this->render('errors/404', ['message' => $message]);
    }
}
