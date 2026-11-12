<?php
/**
 * ============================================================
 *  GradeController
 *  Exam Result - simple manual marks entry and display.
 *  Results are stored in the `results` table (exam_id NULL)
 *  and listed without grades, percentages or ranking.
 * ============================================================
 */

declare(strict_types=1);

class GradeController extends Controller
{
    protected Grade $grade;

    public function __construct()
    {
        parent::__construct();
        $this->grade = new Grade();
    }

    public function index(): void
    {
        Auth::requirePermission('grades.manage');

        $db = Database::getInstance();

        $students = $db->fetchAll(
            'SELECT id, official_id, first_name, middle_name, last_name, gender
             FROM students WHERE status = "active" ORDER BY first_name ASC'
        );

        $rows = $db->fetchAll(
            'SELECT r.id, r.student_id, r.subject_id, r.marks_obtained, r.result_date,
                    su.name AS subject_name
             FROM results r
             INNER JOIN subjects su ON su.id = r.subject_id
             WHERE r.exam_id IS NULL
             ORDER BY r.student_id ASC, su.name ASC'
        );

        // One result profile per student: the profile ID is the student ID,
        // and every subject mark is appended underneath the same profile.
        $profiles = [];
        foreach ($students as $st) {
            $profiles[$st['id']] = ['student' => $st, 'subjects' => []];
        }
        foreach ($rows as $r) {
            if (isset($profiles[$r['student_id']])) {
                $profiles[$r['student_id']]['subjects'][] = $r;
            }
        }

        $subjects = $db->fetchAll(
            'SELECT id, name FROM subjects ORDER BY name ASC'
        );

        $this->render('grades/index', [
            'profiles' => $profiles,
            'subjects' => $subjects,
        ]);
    }

    public function store(): void
    {
        CSRF::check();
        Auth::requirePermission('grades.manage');

        $studentId = (int) ($_POST['student_id'] ?? 0);
        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        $date      = trim((string) ($_POST['result_date'] ?? ''));
        $marks     = (float) ($_POST['marks_obtained'] ?? -1);

        if ($studentId < 1 || $subjectId < 1 || $date === '' || $marks < 0 || $marks > 100) {
            Flash::set('error', 'Please choose a student and subject, a valid date, and marks between 0 and 100.');
            $this->redirect('grades/index');
        }

        $db = Database::getInstance();
        if (!$db->fetch('SELECT id FROM students WHERE id = ?', [$studentId])) {
            Flash::set('error', 'Student not found.');
            $this->redirect('grades/index');
        }
        if (!$db->fetch('SELECT id FROM subjects WHERE id = ?', [$subjectId])) {
            Flash::set('error', 'Subject not found.');
            $this->redirect('grades/index');
        }

        $data = [
            'student_id'     => $studentId,
            'subject_id'     => $subjectId,
            'result_date'    => $date,
            'marks_obtained' => $marks,
        ];

        // A student keeps a single result profile. Adding a subject appends
        // to that profile; if the subject already exists, its marks are
        // updated instead of creating a second record for the same student.
        $existing = $db->fetch(
            'SELECT id FROM results WHERE exam_id IS NULL AND student_id = ? AND subject_id = ?',
            [$studentId, $subjectId]
        );

        if ($existing) {
            $this->grade->updateResult((int) $existing['id'], $data);
            Flash::set('success', 'Marks updated for this student and subject.');
        } else {
            $this->grade->storeResult($data);
            Flash::set('success', 'Subject added to the student result profile.');
        }

        $this->logActivity('update', 'grades', 'Saved exam result for student #' . $studentId);
        $this->redirect('grades/index');
    }

    public function update(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('grades.manage');

        $studentId = (int) ($_POST['student_id'] ?? 0);
        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        $date      = trim((string) ($_POST['result_date'] ?? ''));
        $marks     = (float) ($_POST['marks_obtained'] ?? -1);

        if ($studentId < 1 || $subjectId < 1 || $date === '' || $marks < 0 || $marks > 100) {
            Flash::set('error', 'Please choose a student and subject, a valid date, and marks between 0 and 100.');
            $this->redirect('grades/index');
        }

        $db = Database::getInstance();
        $duplicate = $db->fetch(
            'SELECT id FROM results
             WHERE exam_id IS NULL AND student_id = ? AND subject_id = ? AND id <> ?',
            [$studentId, $subjectId, $id]
        );
        if ($duplicate) {
            Flash::set('error', 'This subject is already recorded for the student.');
            $this->redirect('grades/index');
        }

        $this->grade->updateResult($id, [
            'student_id'     => $studentId,
            'subject_id'     => $subjectId,
            'result_date'    => $date,
            'marks_obtained' => $marks,
        ]);
        $this->logActivity('update', 'grades', 'Updated exam result #' . $id);
        Flash::set('success', 'Result updated.');
        $this->redirect('grades/index');
    }

    public function destroy(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('grades.manage');

        $this->grade->deleteResult($id);
        $this->logActivity('delete', 'grades', 'Deleted exam result #' . $id);
        Flash::set('success', 'Result deleted.');
        $this->redirect('grades/index');
    }
}
