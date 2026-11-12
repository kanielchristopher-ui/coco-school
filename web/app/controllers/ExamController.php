<?php
/**
 * ============================================================
 *  ExamController
 *  Manage exam types and examinations.
 * ============================================================
 */

declare(strict_types=1);

class ExamController extends Controller
{
    protected Exam $exam;

    public function __construct()
    {
        parent::__construct();
        $this->exam = new Exam();
    }

    public function index(): void
    {
        $isTeacher = Auth::role() === 'teacher';

        $classId = (int) ($_GET['class_id'] ?? 0);
        $status  = trim($_GET['status'] ?? '');

        if ($isTeacher) {
            Auth::requirePermission('manage_own_exams');
            $teacher = (new Teacher())->firstWhere('user_id', Auth::id());
            $teacherId = $teacher ? (int) $teacher['id'] : 0;
            $rows = $teacherId ? $this->exam->teacherExams($teacherId, $classId ?: null, $status ?: null) : [];
            // Only classes where the teacher teaches a subject are visible.
            $subjects = $teacherId ? (new Subject())->listForTeacher($teacherId) : [];
            $assignedClasses = $teacherId ? (new Subject())->classIdsForTeacher($teacherId) : [];
            $classes = array_values(array_filter(
                (new ClassRoom())->all('name'),
                static fn(array $c): bool => in_array((int) $c['id'], $assignedClasses, true)
            ));
        } else {
            Auth::requirePermission('exams.manage');
            $rows = $this->exam->listAll($classId ?: null, $status ?: null);
            $classes = (new ClassRoom())->all('name');
            $subjects = $this->exam->allSubjects();
        }

        $this->render('exams/index', [
            'rows'        => $rows,
            'examTypes'   => (new ExamType())->all('name'),
            'classes'     => $classes,
            'subjects'    => $subjects,
            'sessions'    => $this->exam->sessions(),
            'classId'     => $classId,
            'statusFilter'=> $status,
            'canManage'   => !$isTeacher,
        ]);
    }

    /**
     * Student-facing list of exams published for the student's class.
     */
    public function studentExams(): void
    {
        Auth::requireRole(['student']);

        $student = (new Student())->firstWhere('user_id', Auth::id());
        if (!$student || !$student['class_id']) {
            Flash::set('warning', 'No student profile is linked to this account.');
            $this->redirect('dashboard/index');
            return;
        }

        $rows = Database::getInstance()->fetchAll(
            'SELECT e.*, su.name AS subject_name, et.name AS type_name, c.name AS class_name,
                    sec.name AS section_name, ss.name AS session_name
             FROM exams e
             INNER JOIN subjects su        ON su.id = e.subject_id AND su.class_id = e.class_id
             LEFT JOIN exam_types et       ON et.id = e.exam_type_id
             LEFT JOIN classes c           ON c.id = e.class_id
             LEFT JOIN sections sec        ON sec.id = e.section_id
             LEFT JOIN academic_sessions ss ON ss.id = e.session_id
             WHERE e.class_id = ? AND e.status IN ("published", "upcoming", "active")
             ORDER BY e.start_date ASC',
            [(int) $student['class_id']]
        );

        $this->render('exams/student', ['rows' => $rows, 'student' => $student]);
    }

    public function store(): void
    {
        CSRF::check();
        Auth::requireAny(['manage_own_exams', 'exams.manage']);
        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        $subject = $subjectId ? (new Subject())->find($subjectId) : null;
        if (!$subject) {
            Flash::set('error', 'Please select a valid subject.');
            $this->redirect('exams/index');
        }

        // Teachers may only create exams for classes/subjects assigned to them.
        if (Auth::role() === 'teacher') {
            $classId  = (int) ($_POST['class_id'] ?? 0);
            if ((int) $subject['class_id'] !== $classId
                || (int) $subject['teacher_id'] !== Auth::teacherProfileId()) {
                Flash::set('error', 'You can only create exams for classes and subjects assigned to you.');
                $this->redirect('exams/index');
            }
        }

        $startDate = trim($_POST['start_date'] ?? '') ?: null;
        $endDate   = trim($_POST['end_date'] ?? '') ?: $startDate;

        $status = in_array(($_POST['status'] ?? ''), ['upcoming', 'active', 'completed', 'draft', 'published'], true)
                ? $_POST['status'] : 'upcoming';

        $id = $this->exam->create([
            'name'         => $subject['name'],
            'subject_id'   => $subjectId,
            'exam_type_id' => (int) ($_POST['exam_type_id'] ?? 0) ?: null,
            'class_id'     => (int) ($_POST['class_id'] ?? 0) ?: null,
            'section_id'   => (int) ($_POST['section_id'] ?? 0) ?: null,
            'session_id'   => (int) ($_POST['session_id'] ?? 0) ?: (int) (new Setting())->get('academic_session_id') ?: null,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'status'       => $status,
        ]);
        $this->logActivity('create', 'exams', 'Created exam #' . $id . ' (' . $subject['name'] . ')');

        // Announce the exam to its class when it is created as active/published.
        if (in_array($status, ['active', 'published'], true)) {
            $this->notifyExamAnnouncement([
                'name'       => $subject['name'],
                'subject_id' => $subjectId,
                'class_id'   => (int) ($_POST['class_id'] ?? 0) ?: null,
                'start_date' => $startDate,
            ]);
        }

        Flash::set('success', 'Exam created successfully.');
        $this->redirect('exams/index');
    }

    public function edit(int $id): void
    {
        Auth::requireAny(['manage_own_exams', 'exams.manage']);

        $exam = $this->exam->find($id);
        if (!$exam) {
            $this->renderError('Exam not found.');
            return;
        }

        // Teachers may only edit exams for classes/subjects assigned to them.
        if (Auth::role() === 'teacher') {
            $examSubject = $exam['subject_id'] ? (new Subject())->find((int) $exam['subject_id']) : null;
            if (!$examSubject
                || (int) $examSubject['class_id'] !== (int) $exam['class_id']
                || (int) $examSubject['teacher_id'] !== Auth::teacherProfileId()) {
                $this->renderError('You can only edit exams for classes and subjects assigned to you.');
                return;
            }
        }

        $this->render('exams/edit', [
            'exam'      => $exam,
            'examTypes' => (new ExamType())->all('name'),
            'classes'   => (new ClassRoom())->all('name'),
            'subjects'  => $this->exam->allSubjects(),
            'sections'  => (new ClassRoom())->sections((int) $exam['class_id']),
            'sessions'  => $this->exam->sessions(),
        ]);
    }

    public function update(int $id): void
    {
        CSRF::check();
        Auth::requireAny(['manage_own_exams', 'exams.manage']);

        $exam = $this->exam->find($id);
        if (!$exam) {
            $this->renderError('Exam not found.');
            return;
        }

        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        $subject = $subjectId ? (new Subject())->find($subjectId) : null;
        if (!$subject) {
            Flash::set('error', 'Please select a valid subject.');
            $this->redirect('exams/edit/' . $id);
        }

        // Teachers may only update exams for classes/subjects assigned to them.
        if (Auth::role() === 'teacher') {
            $classId  = (int) ($_POST['class_id'] ?? 0);
            if ((int) $subject['class_id'] !== $classId
                || (int) $subject['teacher_id'] !== Auth::teacherProfileId()) {
                Flash::set('error', 'You can only update exams for classes and subjects assigned to you.');
                $this->redirect('exams/edit/' . $id);
            }
        }

        $startDate = trim($_POST['start_date'] ?? '') ?: null;
        $endDate   = trim($_POST['end_date'] ?? '') ?: $startDate;

        $status = in_array(($_POST['status'] ?? ''), ['upcoming', 'active', 'completed', 'draft', 'published'], true)
                ? $_POST['status'] : 'upcoming';

        $this->exam->update($id, [
            'name'         => $subject['name'],
            'subject_id'   => $subjectId,
            'exam_type_id' => (int) ($_POST['exam_type_id'] ?? 0) ?: null,
            'class_id'     => (int) ($_POST['class_id'] ?? 0) ?: null,
            'section_id'   => (int) ($_POST['section_id'] ?? 0) ?: null,
            'session_id'   => (int) ($_POST['session_id'] ?? 0) ?: null,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'status'       => $status,
        ]);
        $this->logActivity('update', 'exams', 'Updated exam #' . $id . ' (' . $subject['name'] . ')');

        // Announce the exam to its class when it (re)becomes active/published.
        if (in_array($status, ['active', 'published'], true)
            && $status !== (string) $exam['status']) {
            $this->notifyExamAnnouncement([
                'name'       => $subject['name'],
                'subject_id' => $subjectId,
                'class_id'   => (int) ($_POST['class_id'] ?? 0) ?: null,
                'start_date' => $startDate,
            ]);
        }

        Flash::set('success', 'Exam updated successfully.');
        $this->redirect('exams/index');
    }

    public function updateStatus(int $id): void
    {
        CSRF::check();
        Auth::requireAny(['manage_own_exams', 'exams.manage']);

        $exam = $this->exam->find($id);
        if (!$exam) {
            $this->renderError('Exam not found.');
            return;
        }

        // Teachers may only publish/update their own exams.
        if (Auth::role() === 'teacher') {
            $examSubject = $exam['subject_id'] ? (new Subject())->find((int) $exam['subject_id']) : null;
            if (!$examSubject
                || (int) $examSubject['class_id'] !== (int) $exam['class_id']
                || (int) $examSubject['teacher_id'] !== Auth::teacherProfileId()) {
                $this->renderError('You can only update exams for classes and subjects assigned to you.');
                return;
            }
        }

        $status = trim($_POST['status'] ?? '');
        if (in_array($status, ['upcoming', 'active', 'completed', 'draft', 'published'], true)) {
            $this->exam->update($id, ['status' => $status]);
            $this->logActivity('update', 'exams', 'Updated exam #' . $id . ' status to ' . $status);

            // Announce the exam to its class when it (re)becomes active/published.
            if (in_array($status, ['active', 'published'], true)
                && $status !== (string) $exam['status']) {
                $this->notifyExamAnnouncement($exam);
            }

            Flash::set('success', 'Exam status updated.');
        }
        $this->redirect('exams/index');
    }

    public function destroy(int $id): void
    {
        CSRF::check();
        Auth::requireRole(['admin', 'principal', 'prefet', 'director', 'proviseur']);

        $this->exam->delete($id);
        $this->logActivity('delete', 'exams', 'Deleted exam #' . $id);
        Flash::set('success', 'Exam deleted.');
        $this->redirect('exams/index');
    }

    /**
     * Manage exam types.
     */
    public function types(): void
    {
        Auth::requireRole(['admin', 'principal', 'prefet', 'director', 'proviseur']);
        $this->render('exams/types', ['rows' => (new ExamType())->all('name')]);
    }

    public function storeType(): void
    {
        CSRF::check();
        Auth::requireRole(['admin', 'principal', 'prefet', 'director', 'proviseur']);

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            Flash::set('error', 'Exam type name is required.');
            $this->redirect('exams/types');
        }

        (new ExamType())->create([
            'name'        => $name,
            'description' => trim($_POST['description'] ?? ''),
        ]);
        Flash::set('success', 'Exam type added.');
        $this->redirect('exams/types');
    }

    public function destroyType(int $id): void
    {
        CSRF::check();
        Auth::requireRole(['admin', 'principal', 'prefet', 'director', 'proviseur']);

        (new ExamType())->delete($id);
        Flash::set('success', 'Exam type deleted.');
        $this->redirect('exams/types');
    }

    /**
     * JSON endpoint for the sections of a class (used by exam forms).
     */
    public function sectionsJson(): void
    {
        $classId = (int) ($_GET['class_id'] ?? 0);

        if (Auth::role() === 'teacher') {
            Auth::requirePermission('manage_own_exams');
            $teacherId = Auth::teacherProfileId();
            if (!$teacherId || !in_array($classId, (new Subject())->classIdsForTeacher($teacherId), true)) {
                $this->json([], 403);
                return;
            }
        } else {
            Auth::requirePermission('exams.manage');
        }

        $this->json((new ClassRoom())->sections($classId));
    }

    /**
     * Notify every active student (with a linked account) in the exam's class
     * that the exam has been announced for their class.
     */
    protected function notifyExamAnnouncement(array $exam): void
    {
        $classId = (int) ($exam['class_id'] ?? 0);
        if ($classId < 1) {
            return;
        }

        $db = Database::getInstance();

        $class   = $db->fetch('SELECT name FROM classes WHERE id = ?', [$classId]);
        $subject = $exam['subject_id']
            ? $db->fetch('SELECT name FROM subjects WHERE id = ?', [(int) $exam['subject_id']])
            : null;

        $examName    = trim((string) ($exam['name'] ?? ''));
        $subjectName = $subject ? trim((string) $subject['name']) : '';
        if ($examName === '') {
            $examName = $subjectName;
        }
        $className = $class ? (string) $class['name'] : '—';

        // The teacher responsible for the exam: the subject's assigned teacher,
        // falling back to the current user's linked teacher profile.
        $teacherName = '';
        if ($exam['subject_id']) {
            $teacher = $db->fetch(
                'SELECT t.first_name, t.last_name
                 FROM subjects su JOIN teachers t ON t.id = su.teacher_id
                 WHERE su.id = ?',
                [(int) $exam['subject_id']]
            );
            if ($teacher) {
                $teacherName = trim(($teacher['first_name'] ?? '') . ' ' . ($teacher['last_name'] ?? ''));
            }
        }
        if ($teacherName === '') {
            $teacher = $db->fetch(
                'SELECT first_name, last_name FROM teachers WHERE user_id = ? LIMIT 1',
                [Auth::id()]
            );
            if ($teacher) {
                $teacherName = trim(($teacher['first_name'] ?? '') . ' ' . ($teacher['last_name'] ?? ''));
            }
        }
        if ($teacherName === '') {
            $teacherName = '—';
        }

        $label = $subjectName !== '' ? $subjectName : $examName;
        if ($subjectName !== '' && $examName !== '' && $subjectName !== $examName) {
            $label = $examName . ' (' . $subjectName . ')';
        }

        $title   = 'New Exam Announced';
        $message = 'New exam announced for ' . $className . ': ' . $label
            . ' on ' . formatDate($exam['start_date'] ?? null)
            . '. Teacher: ' . $teacherName . '.';

        $students = $db->fetchAll(
            'SELECT DISTINCT s.user_id
             FROM students s
             INNER JOIN users u ON u.id = s.user_id
             WHERE s.class_id = ? AND s.status = "active"',
            [$classId]
        );
        foreach ($students as $student) {
            (new Notification())->push(
                (int) $student['user_id'],
                $title,
                $message,
                'info',
                'exams/student-exams'
            );
        }
    }

    protected function renderError(string $message): void
    {
        http_response_code(404);
        $this->render('errors/404', ['message' => $message]);
    }
}
