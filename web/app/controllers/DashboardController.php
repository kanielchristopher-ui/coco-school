<?php
/**
 * ============================================================
 *  DashboardController
 *  Aggregates the statistics, charts and recent activity for
 *  the dashboard home page.
 * ============================================================
 */

declare(strict_types=1);

class DashboardController extends Controller
{
    /**
     * Dashboard index. Routes non-admin roles to their own view so that
     * teachers and students never see the school-wide statistics.
     */
    public function index(): void
    {
        if (Auth::role() === 'teacher') {
            $this->teacher();
            return;
        }
        if (Auth::role() === 'student') {
            $this->student();
            return;
        }
        if (Auth::role() === 'parent') {
            $this->redirect('parent/dashboard');
            return;
        }
        if (Auth::role() === 'cashier') {
            $this->cashier();
            return;
        }
        if (Auth::role() === 'discipline') {
            $this->discipline();
            return;
        }

        $db = Database::getInstance();

        $stats = [
            'total_students'   => (int) $db->count('SELECT COUNT(*) FROM students WHERE status = "active"'),
            'total_teachers'   => (int) $db->count('SELECT COUNT(*) FROM teachers WHERE status = "active"'),
            'total_classes'    => (int) $db->count('SELECT COUNT(*) FROM classes'),
            'total_sections'   => (int) $db->count('SELECT COUNT(*) FROM sections'),
            'attendance_today' => (int) $db->count('SELECT COUNT(DISTINCT student_id) FROM attendance WHERE date = CURDATE() AND status IN ("present","late")'),
            'fees_collected'   => (float) $db->fetchColumn('SELECT COALESCE(SUM(amount),0) FROM payments'),
            'pending_fees'     => (float) $db->fetchColumn('SELECT COALESCE(SUM(amount - discount_amount - scholarship),0) FROM fees WHERE status IN ("pending","partial","overdue")'),
            'exams'            => (int) $db->count('SELECT COUNT(*) FROM exams'),
            'subjects'         => (int) $db->count('SELECT COUNT(*) FROM subjects'),
            'users'            => (int) $db->count('SELECT COUNT(*) FROM users'),
        ];

        // ---- Chart 1: Student growth (admissions per month, last 6 months) ----
        $studentGrowth = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = (int) date('n', strtotime("-$i month"));
            $y = (int) date('Y', strtotime("-$i month"));
            $studentGrowth[] = [
                'month' => ldate('M', mktime(0, 0, 0, $m, 1, $y)),
                'count' => (int) $db->fetchColumn(
                    'SELECT COUNT(*) FROM students WHERE MONTH(admission_date) = ? AND YEAR(admission_date) = ?',
                    [$m, $y]
                ),
            ];
        }

        // ---- Chart 2: Monthly fees collected (last 6 months) ----
        $feeTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = (int) date('n', strtotime("-$i month"));
            $y = (int) date('Y', strtotime("-$i month"));
            $feeTrend[] = [
                'month' => ldate('M', mktime(0, 0, 0, $m, 1, $y)),
                'total' => (float) $db->fetchColumn(
                    'SELECT COALESCE(SUM(amount),0) FROM payments WHERE MONTH(payment_date) = ? AND YEAR(payment_date) = ?',
                    [$m, $y]
                ),
            ];
        }

        // ---- Chart 3: Attendance graph (last 10 school days) ----
        $attendanceTrend = [];
        $days = $db->fetchAll(
            'SELECT date, status, COUNT(*) AS total
             FROM attendance
             WHERE date >= CURDATE() - INTERVAL 14 DAY
             GROUP BY date, status
             ORDER BY date ASC'
        );
        $byDate = [];
        foreach ($days as $d) {
            $byDate[$d['date']][$d['status']] = (int) $d['total'];
        }
        $dates = array_slice(array_keys($byDate), -10);
        foreach ($dates as $date) {
            $attendanceTrend[] = [
                'date'    => ldate('M d', strtotime($date)),
                'present' => ($byDate[$date]['present'] ?? 0) + ($byDate[$date]['late'] ?? 0),
                'absent'  => $byDate[$date]['absent'] ?? 0,
                'leave'   => $byDate[$date]['leave'] ?? 0,
            ];
        }

        // ---- Notifications & recent activity ----
        $notifications = (new Notification())->latest($this->userId(), 5);
        $activities = $db->fetchAll(
            'SELECT al.*, u.first_name, u.last_name
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.id DESC LIMIT 5'
        );

        $upcomingEvents = (new Event())->upcoming(5);

        // Indicateurs propres à la direction (préfet, proviseur)
        $extra = [
            'pending_review' => (int) $db->count('SELECT COUNT(DISTINCT exam_id, subject_id) FROM results WHERE status = \'submitted\''),
            'open_incidents' => (int) $db->count('SELECT COUNT(*) FROM discipline_records WHERE status = \'open\''),
            'absent_today'   => (int) $db->count('SELECT COUNT(*) FROM attendance WHERE date = CURDATE() AND status = \'absent\''),
            'by_cycle'       => $db->fetchAll('SELECT c.cycle, COUNT(s.id) AS total FROM classes c LEFT JOIN students s ON s.class_id = c.id AND s.status = \'active\' GROUP BY c.cycle'),
        ];

        $this->render('dashboard/index', [
            'extra'           => $extra,
            'stats'           => $stats,
            'studentGrowth'   => $studentGrowth,
            'feeTrend'        => $feeTrend,
            'attendanceTrend' => $attendanceTrend,
            'notifications'   => $notifications,
            'activities'      => $activities,
            'upcomingEvents'  => $upcomingEvents,
        ]);
    }

    /**
     * Tableau de bord de la caissière : encaissements, impayés, derniers reçus.
     */
    public function cashier(): void
    {
        $db = Database::getInstance();
        $stats = [
            'today'    => (float) $db->fetchColumn('SELECT COALESCE(SUM(amount),0) FROM payments WHERE DATE(payment_date) = CURDATE()'),
            'month'    => (float) $db->fetchColumn('SELECT COALESCE(SUM(amount),0) FROM payments WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())'),
            'pending'  => (float) $db->fetchColumn('SELECT COALESCE(SUM(amount - discount_amount - scholarship),0) FROM fees WHERE status IN (\'pending\',\'partial\',\'overdue\')'),
            'receipts' => (int) $db->count('SELECT COUNT(*) FROM payments WHERE DATE(payment_date) = CURDATE()'),
            'unpaid'   => (int) $db->count('SELECT COUNT(DISTINCT student_id) FROM fees WHERE status IN (\'pending\',\'partial\',\'overdue\')'),
        ];
        $feeTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = (int) date('n', strtotime("-$i month"));
            $y = (int) date('Y', strtotime("-$i month"));
            $feeTrend[] = [
                'month' => ldate('M', mktime(0, 0, 0, $m, 1, $y)),
                'total' => (float) $db->fetchColumn('SELECT COALESCE(SUM(amount),0) FROM payments WHERE MONTH(payment_date) = ? AND YEAR(payment_date) = ?', [$m, $y]),
            ];
        }
        $lastPayments = $db->fetchAll(
            'SELECT p.*, s.first_name, s.middle_name, s.last_name
             FROM payments p LEFT JOIN students s ON s.id = p.student_id
             ORDER BY p.payment_date DESC, p.id DESC LIMIT 6'
        );
        $debtors = $db->fetchAll(
            'SELECT s.id, s.first_name, s.middle_name, s.last_name, c.name AS class_name,
                    SUM(f.amount - f.discount_amount - f.scholarship) AS due
             FROM fees f
             INNER JOIN students s ON s.id = f.student_id
             LEFT JOIN classes c ON c.id = s.class_id
             WHERE f.status IN (\'pending\',\'partial\',\'overdue\')
             GROUP BY s.id, s.first_name, s.middle_name, s.last_name, c.name
             ORDER BY due DESC LIMIT 6'
        );
        $this->render('dashboard/cashier', [
            'stats' => $stats, 'feeTrend' => $feeTrend, 'lastPayments' => $lastPayments, 'debtors' => $debtors,
        ]);
    }

    /**
     * Tableau de bord du directeur de discipline : présences et incidents.
     */
    public function discipline(): void
    {
        $db = Database::getInstance();
        $stats = [
            'absent'    => (int) $db->count('SELECT COUNT(*) FROM attendance WHERE date = CURDATE() AND status = \'absent\''),
            'late'      => (int) $db->count('SELECT COUNT(*) FROM attendance WHERE date = CURDATE() AND status = \'late\''),
            'month'     => (int) $db->count('SELECT COUNT(*) FROM discipline_records WHERE MONTH(incident_date) = MONTH(CURDATE()) AND YEAR(incident_date) = YEAR(CURDATE())'),
            'open'      => (int) $db->count('SELECT COUNT(*) FROM discipline_records WHERE status = \'open\''),
            'serious'   => (int) $db->count('SELECT COUNT(*) FROM discipline_records WHERE severity = \'serious\' AND status = \'open\''),
            'teachers_absent' => (int) $db->count('SELECT COUNT(*) FROM teacher_attendance WHERE date = CURDATE() AND status = \'absent\''),
        ];
        $byDate = [];
        foreach ($db->fetchAll('SELECT date, status, COUNT(*) AS total FROM attendance WHERE date >= CURDATE() - INTERVAL 14 DAY GROUP BY date, status ORDER BY date ASC') as $d) {
            $byDate[$d['date']][$d['status']] = (int) $d['total'];
        }
        $attendanceTrend = [];
        foreach (array_slice(array_keys($byDate), -10) as $date) {
            $attendanceTrend[] = [
                'date'    => ldate('M d', strtotime($date)),
                'present' => ($byDate[$date]['present'] ?? 0) + ($byDate[$date]['late'] ?? 0),
                'absent'  => $byDate[$date]['absent'] ?? 0,
                'leave'   => $byDate[$date]['leave'] ?? 0,
            ];
        }
        $recent = $db->fetchAll(
            'SELECT d.*, s.first_name, s.middle_name, s.last_name, c.name AS class_name
             FROM discipline_records d
             INNER JOIN students s ON s.id = d.student_id
             LEFT JOIN classes c ON c.id = s.class_id
             ORDER BY d.incident_date DESC, d.id DESC LIMIT 6'
        );
        $topAbsent = $db->fetchAll(
            'SELECT s.id, s.first_name, s.middle_name, s.last_name, c.name AS class_name, COUNT(*) AS total
             FROM attendance a
             INNER JOIN students s ON s.id = a.student_id
             LEFT JOIN classes c ON c.id = s.class_id
             WHERE a.status = \'absent\' AND a.date >= CURDATE() - INTERVAL 30 DAY
             GROUP BY s.id, s.first_name, s.middle_name, s.last_name, c.name
             ORDER BY total DESC LIMIT 5'
        );
        $this->render('dashboard/discipline', [
            'stats' => $stats, 'attendanceTrend' => $attendanceTrend, 'recent' => $recent, 'topAbsent' => $topAbsent,
        ]);
    }

    /**
     * Teacher dashboard. Shows only the classes, students, subjects and
     * exams assigned to the logged-in teacher, plus their quick actions.
     */
    public function teacher(): void
    {
        Auth::requireRole(['teacher']);

        $db = Database::getInstance();
        $teacher = (new Teacher())->firstWhere('user_id', Auth::id());
        $teacherId = $teacher ? (int) $teacher['id'] : 0;

        $assignedClassIds = Auth::assignedClassIds();

        $classes = $teacherId
            ? $db->fetchAll('SELECT * FROM classes WHERE class_teacher_id = ? ORDER BY name ASC', [$teacherId])
            : [];
        $classIds = array_column($classes, 'id');
        $classIds = array_map('intval', $classIds);

        $studentCount = 0;
        $attendanceToday = 0;
        $students = [];
        if ($classIds !== []) {
            $in = implode(',', array_fill(0, count($classIds), '?'));
            $studentCount = (int) $db->count("SELECT COUNT(*) FROM students WHERE status = 'active' AND class_id IN ($in)", $classIds);
            $students = $db->fetchAll(
                "SELECT s.id, s.first_name, s.last_name, s.admission_no, s.class_id, c.name AS class_name
                 FROM students s
                 LEFT JOIN classes c ON c.id = s.class_id
                 WHERE s.status = 'active' AND s.class_id IN ($in)
                 ORDER BY c.name ASC, s.first_name ASC",
                $classIds
            );
            $attendanceToday = (int) $db->count(
                "SELECT COUNT(DISTINCT student_id) FROM attendance WHERE date = CURDATE() AND status IN ('present','late') AND class_id IN ($in)",
                $classIds
            );
        }

        $subjects = $teacherId
            ? $db->fetchAll(
                'SELECT su.*, c.name AS class_name
                 FROM subjects su
                 LEFT JOIN classes c ON c.id = su.class_id
                 WHERE su.teacher_id = ? ORDER BY su.name ASC',
                [$teacherId]
            )
            : [];
        $subjectIds = array_map('intval', array_column($subjects, 'id'));

        $exams = [];
        if ($subjectIds !== []) {
            $in = implode(',', array_fill(0, count($subjectIds), '?'));
            $exams = $db->fetchAll(
                "SELECT e.*, c.name AS class_name, su.name AS subject_name
                 FROM exams e
                 LEFT JOIN classes c ON c.id = e.class_id
                 LEFT JOIN subjects su ON su.id = e.subject_id
                 WHERE e.subject_id IN ($in) AND e.status = 'scheduled'
                 ORDER BY e.start_date ASC LIMIT 8",
                $subjectIds
            );
        }

        $notifications = (new Notification())->latest($this->userId(), 5);
        $recentActivities = $db->fetchAll(
            'SELECT al.*, u.first_name, u.last_name
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE al.user_id = ? ORDER BY al.id DESC LIMIT 5',
            [Auth::id()]
        );

        $this->render('dashboard/teacher', [
            'teacher'          => $teacher,
            'classes'          => $classes,
            'students'         => $students,
            'studentCount'     => $studentCount,
            'attendanceToday'  => $attendanceToday,
            'subjects'         => $subjects,
            'exams'            => $exams,
            'notifications'    => $notifications,
            'recentActivities' => $recentActivities,
            'assignedClassIds' => $assignedClassIds,
        ]);
    }

    /**
     * Student dashboard. Shows only the logged-in student's own class,
     * subjects, results, attendance and profile.
     */
    public function student(): void
    {
        Auth::requireRole(['student']);

        $student = (new Student())->firstWhere('user_id', Auth::id());
        if (!$student) {
            // Repair legacy Student accounts without a profile so the dashboard
            // always resolves the logged-in student's id.
            (new Student())->ensureForUser(Auth::id());
            $student = (new Student())->firstWhere('user_id', Auth::id());
        }
        if (!$student) {
            Flash::set('warning', 'No student profile is linked to this account.');
            $this->redirect('profile/index');
            return;
        }

        $db = Database::getInstance();
        $studentId = (int) $student['id'];

        // Class + class teacher info.
        $class = $db->fetch(
            'SELECT c.*, t.first_name AS ct_first, t.last_name AS ct_last, sec.name AS section_name
             FROM classes c
             LEFT JOIN teachers t ON t.id = c.class_teacher_id
             LEFT JOIN sections sec ON sec.id = ?
             WHERE c.id = ?',
            [(int) $student['section_id'], (int) $student['class_id']]
        );

        // Sections are gated by the student's read-only permissions.
        $subjects = Auth::can('view_own_subjects')
            ? $db->fetchAll(
                'SELECT su.*, t.first_name AS teacher_first, t.last_name AS teacher_last
                 FROM subjects su
                 LEFT JOIN teachers t ON t.id = su.teacher_id
                 WHERE su.class_id = ? ORDER BY su.name ASC',
                [(int) $student['class_id']]
              )
            : [];

        $attendance = Auth::can('view_own_attendance')
            ? $db->fetchAll(
                'SELECT status, COUNT(*) AS total FROM attendance
                 WHERE student_id = ? GROUP BY status',
                [$studentId]
              )
            : [];

        $results = Auth::can('view_own_results_only')
            ? $db->fetchAll(
                'SELECT e.id AS exam_id, e.name AS exam_name, et.name AS type_name, e.term,
                        ROUND(SUM(r.marks_obtained),2) AS total,
                        ROUND(SUM(r.max_marks),2) AS max_total,
                        ROUND((SUM(r.marks_obtained)/SUM(r.max_marks))*100,2) AS percentage
                 FROM results r
                 INNER JOIN exams e ON e.id = r.exam_id
                 LEFT JOIN exam_types et ON et.id = e.exam_type_id
                 WHERE r.student_id = ? AND r.status IN ("published", "locked")
                 GROUP BY r.exam_id ORDER BY e.start_date DESC',
                [$studentId]
              )
            : [];

        $notifications = (new Notification())->latest($this->userId(), 5);

        $this->render('dashboard/student', [
            'student'       => $student,
            'class'         => $class,
            'subjects'      => $subjects,
            'attendance'    => $attendance,
            'results'       => $results,
            'notifications' => $notifications,
        ]);
    }
}
