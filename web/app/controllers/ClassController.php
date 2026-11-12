<?php
/**
 * ============================================================
 *  ClassController
 *  Manage classes, sections and timetables.
 * ============================================================
 */

declare(strict_types=1);

class ClassController extends Controller
{
    protected ClassRoom $classRoom;
    protected Teacher $teacher;

    public function __construct()
    {
        parent::__construct();
        $this->classRoom = new ClassRoom();
        $this->teacher = new Teacher();
    }

    public function index(): void
    {
        $isTeacher = Auth::role() === 'teacher';

        if ($isTeacher) {
            Auth::requirePermission('view_assigned_students');
            $assigned = Auth::assignedClassIds();
            $rows = array_values(array_filter(
                $this->classRoom->listAll(),
                static fn(array $c): bool => in_array((int) $c['id'], $assigned, true)
            ));
        } else {
            Auth::requirePermission('classes.manage');
            $rows = $this->classRoom->listAll();
        }

        $this->render('classes/index', ['rows' => $rows, 'canManage' => !$isTeacher]);
    }

    public function create(): void
    {
        Auth::requirePermission('classes.manage');
        $this->render('classes/create', ['teachers' => $this->teacher->listAll()]);
    }

    public function store(): void
    {
        CSRF::check();
        Auth::requirePermission('classes.manage');

        $name = trim($_POST['name'] ?? '');
        $numeric = trim($_POST['numeric_name'] ?? '');
        $cycle = $this->cleanCycle($_POST['cycle'] ?? '');
        $classTeacher = (int) ($_POST['class_teacher_id'] ?? 0);

        if ($name === '') {
            Flash::set('error', 'Class name is required.');
            $this->redirect('classes/create');
        }

        $id = $this->classRoom->create([
            'name'             => $name,
            'numeric_name'     => $numeric,
            'cycle'            => $cycle,
            'class_teacher_id' => $classTeacher ?: null,
        ]);
        $this->logActivity('create', 'classes', 'Created class #' . $id . ' (' . $name . ')');

        Flash::set('success', 'Class created successfully.');
        $this->redirect('classes/manage/' . $id);
    }

    /**
     * JSON endpoint for the sections of a class (used by forms).
     */
    public function sectionsJson(): void
    {
        $classId = (int) ($_GET['class_id'] ?? 0);

        if (Auth::role() === 'teacher') {
            Auth::requirePermission('view_assigned_students');
            if (!in_array($classId, Auth::assignedClassIds(), true)) {
                $this->json([], 403);
                return;
            }
        } else {
            Auth::requirePermission('classes.manage');
        }

        $this->json($this->classRoom->sections($classId));
    }

    /**
     * Manage a class: sections, subjects, timetable, students.
     */
    public function manage(int $id): void
    {
        Auth::requirePermission('classes.manage');

        $class = $this->classRoom->find($id);
        if (!$class) {
            $this->renderError('Class not found.');
            return;
        }

        $db = Database::getInstance();
        $sections = $db->fetchAll('SELECT * FROM sections WHERE class_id = ? ORDER BY name', [$id]);
        $subjects = $db->fetchAll('SELECT su.*, t.first_name, t.last_name FROM subjects su LEFT JOIN teachers t ON t.id = su.teacher_id WHERE su.class_id = ?', [$id]);
        $students = $db->fetchAll('SELECT s.*, sec.name AS section_name FROM students s LEFT JOIN sections sec ON sec.id = s.section_id WHERE s.class_id = ? ORDER BY s.roll_no', [$id]);
        $timetable = $db->fetchAll(
            'SELECT tt.*, sub.name AS subject_name, t.first_name AS teacher_first, t.last_name AS teacher_last, sec.name AS section_name
             FROM timetables tt
             LEFT JOIN subjects sub ON sub.id = tt.subject_id
             LEFT JOIN teachers t ON t.id = tt.teacher_id
             LEFT JOIN sections sec ON sec.id = tt.section_id
             WHERE tt.class_id = ? ORDER BY tt.day_of_week, tt.start_time',
            [$id]
        );

        $this->render('classes/manage', [
            'class'    => $class,
            'sections' => $sections,
            'subjects' => $subjects,
            'students' => $students,
            'timetable'=> $timetable,
            'teachers' => $this->teacher->listAll(),
        ]);
    }

    public function edit(int $id): void
    {
        Auth::requirePermission('classes.manage');

        $class = $this->classRoom->find($id);
        if (!$class) {
            $this->renderError('Class not found.');
            return;
        }
        $this->render('classes/edit', [
            'class'    => $class,
            'teachers' => $this->teacher->listAll(),
        ]);
    }

    public function update(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('classes.manage');

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            Flash::set('error', 'Class name is required.');
            $this->redirect('classes/edit/' . $id);
        }

        $this->classRoom->update($id, [
            'name'             => $name,
            'numeric_name'     => trim($_POST['numeric_name'] ?? ''),
            'cycle'            => $this->cleanCycle($_POST['cycle'] ?? ''),
            'class_teacher_id' => (int) ($_POST['class_teacher_id'] ?? 0) ?: null,
        ]);
        $this->logActivity('update', 'classes', 'Updated class #' . $id);

        Flash::set('success', 'Class updated successfully.');
        $this->redirect('classes/manage/' . $id);
    }

    /**
     * Cycle d'enseignement (RDC) : maternel, primaire ou secondaire.
     */
    private function cleanCycle(string $cycle): string
    {
        return in_array($cycle, ['maternel', 'primaire', 'secondaire'], true) ? $cycle : 'primaire';
    }

    public function destroy(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('classes.manage');

        $this->classRoom->delete($id);
        $this->logActivity('delete', 'classes', 'Deleted class #' . $id);
        Flash::set('success', 'Class deleted successfully.');
        $this->redirect('classes/index');
    }

    // ----------------------------------------------------------
    //  Section management
    // ----------------------------------------------------------

    public function addSection(int $classId): void
    {
        CSRF::check();
        Auth::requirePermission('classes.manage');

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            Flash::set('error', 'Section name is required.');
            $this->redirect('classes/manage/' . $classId);
        }

        (new Section())->create([
            'class_id' => $classId,
            'name'     => $name,
            'room_no'  => trim($_POST['room_no'] ?? ''),
            'capacity' => (int) ($_POST['capacity'] ?? 0) ?: null,
        ]);
        Flash::set('success', 'Section added.');
        $this->redirect('classes/manage/' . $classId);
    }

    public function deleteSection(int $classId, int $sectionId): void
    {
        CSRF::check();
        Auth::requirePermission('classes.manage');

        (new Section())->delete($sectionId);
        Flash::set('success', 'Section deleted.');
        $this->redirect('classes/manage/' . $classId);
    }

    // ----------------------------------------------------------
    //  Timetable management
    // ----------------------------------------------------------

    public function storeTimetable(int $classId): void
    {
        CSRF::check();
        Auth::requirePermission('classes.manage');

        Database::getInstance()->insert('timetables', [
            'class_id'    => $classId,
            'section_id'  => (int) ($_POST['section_id'] ?? 0) ?: null,
            'day_of_week' => (int) ($_POST['day_of_week'] ?? 1),
            'period'      => trim($_POST['period'] ?? ''),
            'subject_id'  => (int) ($_POST['subject_id'] ?? 0) ?: null,
            'teacher_id'  => (int) ($_POST['teacher_id'] ?? 0) ?: null,
            'start_time'  => trim($_POST['start_time'] ?? '') ?: null,
            'end_time'    => trim($_POST['end_time'] ?? '') ?: null,
        ]);

        Flash::set('success', 'Timetable entry added.');
        $this->redirect('classes/manage/' . $classId);
    }

    public function deleteTimetable(int $classId, int $ttId): void
    {
        CSRF::check();
        Auth::requirePermission('classes.manage');

        Database::getInstance()->delete('timetables', 'id = ?', [$ttId]);
        Flash::set('success', 'Timetable entry deleted.');
        $this->redirect('classes/manage/' . $classId);
    }

    protected function renderError(string $message): void
    {
        http_response_code(404);
        $this->render('errors/404', ['message' => $message]);
    }
}
