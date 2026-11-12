<?php
/**
 * ============================================================
 *  SubjectController
 *  Manage subjects and teacher assignments.
 * ============================================================
 */

declare(strict_types=1);

class SubjectController extends Controller
{
    protected Subject $subject;

    public function __construct()
    {
        parent::__construct();
        $this->subject = new Subject();
    }

    public function index(): void
    {
        if (Auth::role() === 'teacher') {
            Auth::requirePermission('view_assigned_students');
            $teacher = (new Teacher())->firstWhere('user_id', Auth::id());
            $teacherId = $teacher ? (int) $teacher['id'] : 0;
            $rows = $teacherId ? $this->subject->listForTeacher($teacherId) : [];
        } else {
            Auth::requirePermission('subjects.manage');
            $rows = $this->subject->listAll();
        }

        $this->render('subjects/index', ['rows' => $rows, 'canManage' => Auth::role() !== 'teacher']);
    }

    public function create(): void
    {
        Auth::requirePermission('subjects.manage');
        $this->render('subjects/create', [
            'classes'  => (new ClassRoom())->all('name'),
            'teachers' => (new Teacher())->listAll(),
        ]);
    }

    public function store(): void
    {
        CSRF::check();
        Auth::requirePermission('subjects.manage');

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            Flash::set('error', 'Subject name is required.');
            $this->redirect('subjects/create');
        }

        $this->subject->create([
            'name'        => $name,
            'code'        => trim($_POST['code'] ?? ''),
            'class_id'    => (int) ($_POST['class_id'] ?? 0) ?: null,
            'teacher_id'  => (int) ($_POST['teacher_id'] ?? 0) ?: null,
            'credits'     => trim($_POST['credits'] ?? '') === '' ? null : max(1, (int) $_POST['credits']),
            'description' => trim($_POST['description'] ?? ''),
        ]);
        $this->logActivity('create', 'subjects', 'Created subject ' . $name);

        Flash::set('success', 'Subject created successfully.');
        $this->redirect('subjects/index');
    }

    public function edit(int $id): void
    {
        Auth::requirePermission('subjects.manage');

        $subject = $this->subject->find($id);
        if (!$subject) {
            $this->renderError('Subject not found.');
            return;
        }
        $this->render('subjects/edit', [
            'subject'  => $subject,
            'classes'  => (new ClassRoom())->all('name'),
            'teachers' => (new Teacher())->listAll(),
        ]);
    }

    public function update(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('subjects.manage');

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            Flash::set('error', 'Subject name is required.');
            $this->redirect('subjects/edit/' . $id);
        }

        $this->subject->update($id, [
            'name'        => $name,
            'code'        => trim($_POST['code'] ?? ''),
            'class_id'    => (int) ($_POST['class_id'] ?? 0) ?: null,
            'teacher_id'  => (int) ($_POST['teacher_id'] ?? 0) ?: null,
            'credits'     => trim($_POST['credits'] ?? '') === '' ? null : max(1, (int) $_POST['credits']),
            'description' => trim($_POST['description'] ?? ''),
        ]);
        $this->logActivity('update', 'subjects', 'Updated subject #' . $id);

        Flash::set('success', 'Subject updated successfully.');
        $this->redirect('subjects/index');
    }

    public function destroy(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('subjects.manage');

        $this->subject->delete($id);
        $this->logActivity('delete', 'subjects', 'Deleted subject #' . $id);
        Flash::set('success', 'Subject deleted.');
        $this->redirect('subjects/index');
    }

    protected function renderError(string $message): void
    {
        http_response_code(404);
        $this->render('errors/404', ['message' => $message]);
    }
}
