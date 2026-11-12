<?php
/**
 * ============================================================
 *  ResultController
 *  Marks entry, ranking, report cards and promotion.
 *  Supports admin-wide entry and teacher subject-wise entry.
 * ============================================================
 */

declare(strict_types=1);

class ResultController extends Controller
{
    protected Result $result;

    public function __construct()
    {
        parent::__construct();
        $this->result = new Result();
    }

    /**
     * Marks entry page for an exam (admin grid).
     */
    public function entry(int $examId): void
    {
        Auth::requireRole(['admin', 'prefet', 'director', 'proviseur']);

        $exam = (new Exam())->findWithDetails($examId);
        if (!$exam) {
            $this->renderError('Exam not found.');
            return;
        }

        $db = Database::getInstance();
        $students = $db->fetchAll(
            'SELECT id, official_id, admission_no, roll_no, first_name, middle_name, last_name, gender
             FROM students WHERE class_id = ? AND status = "active" ORDER BY roll_no, first_name',
            [$exam['class_id']]
        );
        $subjects = $db->fetchAll('SELECT * FROM subjects WHERE class_id = ? ORDER BY name', [$exam['class_id']]);

        // Load existing marks keyed by student + subject.
        $existing = [];
        foreach ($this->result->forExam($examId) as $row) {
            $existing[$row['student_id']][$row['subject_id']] = $row;
        }

        // Max marks per subject (same across students, first saved value wins).
        $maxMarks = [];
        foreach ($existing as $studentRows) {
            foreach ($studentRows as $subjectId => $row) {
                if (!isset($maxMarks[$subjectId])) {
                    $maxMarks[$subjectId] = $row['max_marks'];
                }
            }
        }

        $this->render('results/entry', [
            'exam'     => $exam,
            'students' => $students,
            'subjects' => $subjects,
            'existing' => $existing,
            'maxMarks' => $maxMarks,
        ]);
    }

    /**
     * Save marks for an exam (admin grid).
     */
    public function store(int $examId): void
    {
        CSRF::check();
        Auth::requireRole(['admin', 'prefet', 'director', 'proviseur']);

        $exam = (new Exam())->find($examId);
        if (!$exam) {
            $this->renderError('Exam not found.');
            return;
        }

        $count = 0;
        foreach (($_POST['marks'] ?? []) as $studentId => $subjectMarks) {
            foreach ($subjectMarks as $subjectId => $marks) {
                if ($marks === '') {
                    continue;
                }
                $obtained = max(0, min(100, (float) $marks));
                $max = 100;
                $percentage = ($obtained / $max) * 100;
                $grade = gradeFor($percentage)['grade'];

                $this->result->upsert([
                    'exam_id'       => $examId,
                    'student_id'    => (int) $studentId,
                    'subject_id'    => (int) $subjectId,
                    'marks_obtained'=> $obtained,
                    'max_marks'     => $max,
                    'grade'         => $grade,
                    'remarks'       => trim($_POST['remarks'][$studentId][$subjectId] ?? ''),
                ]);
                $count++;
            }
        }

        $this->logActivity('update', 'results', 'Saved ' . $count . ' marks for exam #' . $examId);
        Flash::set('success', 'Saved ' . $count . ' mark entries.');
        $this->redirect('results/entry/' . $examId);
    }

    // ----------------------------------------------------------
    //  Teacher subject-wise marks entry
    // ----------------------------------------------------------

    /**
     * Teacher-scoped marks entry: a teacher may only enter marks for
     * subjects they are assigned to. Admins/principals may use it too.
     */
    public function teacherEntry(): void
    {
        Auth::requireLogin();

        Auth::requireAny(['create_results', 'edit_results', 'results.manage']);

        $isTeacherRole = Auth::role() === 'teacher';

        $teacher = (new Teacher())->firstWhere('user_id', Auth::id());
        $teacherId = $teacher ? (int) $teacher['id'] : 0;

        $classId   = (int) ($_GET['class_id'] ?? 0);
        $subjectId = (int) ($_GET['subject_id'] ?? 0);
        $examId    = (int) ($_GET['exam_id'] ?? 0);

        // Teachers may only work within the classes where they teach a subject.
        $assignedClasses = $isTeacherRole
            ? ($teacherId ? (new Subject())->classIdsForTeacher($teacherId) : [])
            : [];
        if ($isTeacherRole && $classId !== 0 && !in_array($classId, $assignedClasses, true)) {
            $this->renderError('You can only enter marks for your assigned classes.');
            return;
        }

        // Teachers see only their own subjects; admins see all.
        $subjects = $isTeacherRole
            ? ($teacherId ? $this->result->teacherSubjects($teacherId) : [])
            : Database::getInstance()->fetchAll(
                'SELECT su.*, c.name AS class_name FROM subjects su LEFT JOIN classes c ON c.id = su.class_id ORDER BY su.name'
              );

        $exams = $isTeacherRole
            ? ($teacherId ? $this->result->teacherExams($teacherId, $classId ?: null, $subjectId ?: null) : [])
            : (new Exam())->listAll($classId ?: null);

        $students = [];
        $existing = [];
        $maxMarks = [];
        $publishStatus = null;
        $correctionComment = null;

        if ($classId && $subjectId && $examId) {
            // Teachers may only enter marks for their assigned subjects.
            if ($isTeacherRole && !in_array((int) $subjectId, array_column($subjects, 'id'), true)) {
                $this->renderError('You can only enter marks for subjects assigned to you.');
                return;
            }

            $db = Database::getInstance();
            $students = $db->fetchAll(
                'SELECT id, first_name, last_name, roll_no, admission_no
                 FROM students WHERE class_id = ? AND status = "active" ORDER BY roll_no',
                [$classId]
            );

            foreach ($this->result->forStudentExamWithSubject($examId, $subjectId) as $row) {
                $existing[$row['student_id']] = $row;
            }
            $maxMarks = $existing ? [(int) $subjectId => $existing[array_key_first($existing)]['max_marks']] : [];
            $publishStatus = $existing ? $existing[array_key_first($existing)]['status'] : null;
            $correctionComment = $existing ? ($existing[array_key_first($existing)]['correction_comment'] ?? null) : null;
        }

        $classes = $isTeacherRole
            ? array_values(array_filter(
                (new ClassRoom())->all('name'),
                fn($c) => in_array((int) $c['id'], $assignedClasses, true)
              ))
            : (new ClassRoom())->all('name');

        $this->render('results/teacher_entry', [
            'classes'       => $classes,
            'subjects'      => $subjects,
            'exams'         => $exams,
            'sections'      => $classId ? (new ClassRoom())->sections($classId) : [],
            'classId'       => $classId,
            'subjectId'     => $subjectId,
            'examId'        => $examId,
            'students'      => $students,
            'existing'      => $existing,
            'maxMarks'      => $maxMarks,
            'publishStatus' => $publishStatus,
            'correctionComment' => $correctionComment,
        ]);
    }

    /**
     * Save teacher-subject marks for one subject of one exam.
     */
    public function teacherStore(): void
    {
        CSRF::check();
        Auth::requireLogin();

        Auth::requireAny(['create_results', 'edit_results', 'results.manage']);

        $isTeacherRole = Auth::role() === 'teacher';
        $teacher = (new Teacher())->firstWhere('user_id', Auth::id());
        $teacherId = $teacher ? (int) $teacher['id'] : 0;

        $classId   = (int) ($_POST['class_id'] ?? 0);
        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        $examId    = (int) ($_POST['exam_id'] ?? 0);

        if (!$classId || !$subjectId || !$examId) {
            Flash::set('error', 'Please choose a class, subject and exam.');
            $this->redirect('results/teacher-entry');
        }

        // Teachers may only save marks for their own subjects within their
        // assigned classes, for students belonging to that class.
        if ($isTeacherRole) {
            if (!$teacherId) {
                $this->renderError('No teacher profile is linked to your account.');
                return;
            }
            if (!in_array($classId, Auth::assignedClassIds(), true)) {
                $this->renderError('You can only enter marks for your assigned classes.');
                return;
            }
            $owned = Database::getInstance()->fetch(
                'SELECT id, class_id FROM subjects WHERE id = ? AND teacher_id = ?',
                [$subjectId, $teacherId]
            );
            if (!$owned) {
                $this->renderError('You can only enter marks for subjects assigned to you.');
                return;
            }
            if ((int) $owned['class_id'] !== $classId) {
                $this->renderError('This subject does not belong to the selected class.');
                return;
            }

            // The exam must belong to the same class and subject the teacher
            // is assigned to, so teachers cannot post marks into other exams.
            $exam = Database::getInstance()->fetch(
                'SELECT id, class_id, subject_id FROM exams WHERE id = ?',
                [$examId]
            );
            if (!$exam
                || (int) $exam['class_id'] !== $classId
                || (int) $exam['subject_id'] !== $subjectId) {
                $this->renderError('You can only enter marks for exams assigned to you.');
                return;
            }

            // Teachers may only edit Draft or Returned results. Submitted and
            // approved results wait on the admin queue; published/locked ones
            // need an admin reopen.
            $state = Database::getInstance()->fetch(
                'SELECT status FROM results WHERE exam_id = ? AND subject_id = ? LIMIT 1',
                [$examId, $subjectId]
            );
            if ($state && in_array($state['status'], ['submitted', 'approved'], true)) {
                Flash::set('error', 'These results have been submitted and cannot be edited until the admin returns them for correction.');
                $this->redirect('results/teacher-entry?class_id=' . $classId . '&subject_id=' . $subjectId . '&exam_id=' . $examId);
                return;
            }
            if ($state && in_array($state['status'], ['published', 'locked'], true)) {
                Flash::set('error', 'These results are already ' . $state['status'] . ' and locked from editing. Ask the admin to reopen them if changes are needed.');
                $this->redirect('results/teacher-entry?class_id=' . $classId . '&subject_id=' . $subjectId . '&exam_id=' . $examId);
            }

            foreach (array_keys($_POST['marks'] ?? []) as $studentId) {
                $student = Database::getInstance()->fetch(
                    'SELECT id FROM students WHERE id = ? AND class_id = ? AND status = "active"',
                    [(int) $studentId, $classId]
                );
                if (!$student) {
                    $this->renderError('A selected student is not part of your assigned class.');
                    return;
                }
            }
        }

        $count = 0;

        foreach (($_POST['marks'] ?? []) as $studentId => $marks) {
            if ($marks === '') {
                continue;
            }
            $obtained = max(0, min(100, (float) $marks));
            $max = 100;
            $percentage = ($obtained / $max) * 100;
            $grade = gradeFor($percentage)['grade'];

            $this->result->upsert([
                'exam_id'        => $examId,
                'student_id'     => (int) $studentId,
                'subject_id'     => $subjectId,
                'marks_obtained' => $obtained,
                'max_marks'      => $max,
                'grade'          => $grade,
                'remarks'        => trim($_POST['remarks'][$studentId] ?? ''),
                'teacher_comments' => trim($_POST['teacher_comments'][$studentId] ?? '') ?: null,
                'status'         => 'draft',
            ]);
            $count++;
        }

        // A teacher save moves the whole exam+subject group back to Draft
        // (e.g. a returned set being corrected) so it can be submitted again.
        if ($count > 0) {
            Database::getInstance()->update(
                'results',
                ['status' => 'draft'],
                'exam_id = :e AND subject_id = :s',
                [':e' => $examId, ':s' => $subjectId]
            );
        }

        $this->logActivity('update', 'results', 'Saved ' . $count . ' marks (teacher #' . $teacherId . ') for exam #' . $examId . ' subject #' . $subjectId);
        Flash::set('success', 'Saved ' . $count . ' mark entries.');
        $this->redirect('results/teacher-entry?class_id=' . $classId . '&subject_id=' . $subjectId . '&exam_id=' . $examId);
    }

    /**
     * Teacher submits entered marks for admin review. Status moves from
     * Draft / Returned to Submitted, which puts the group on the admin
     * Review Queue and locks it from further teacher edits.
     */
    public function submit(int $examId, int $subjectId): void
    {
        CSRF::check();
        Auth::requireLogin();

        Auth::requireAny(['create_results', 'edit_results', 'results.manage']);

        $db = Database::getInstance();
        $exam = $db->fetch(
            'SELECT e.*, c.name AS class_name FROM exams e LEFT JOIN classes c ON c.id = e.class_id WHERE e.id = ?',
            [$examId]
        );
        $subject = (new Subject())->find($subjectId);

        $examSubjectId = $exam ? ($exam['subject_id'] !== null ? (int) $exam['subject_id'] : null) : null;
        if (!$exam || !$subject
            || ($examSubjectId !== null && $examSubjectId !== (int) $subjectId)
            || (int) $exam['class_id'] !== (int) $subject['class_id']) {
            $this->renderError('Exam or subject not found for submitting results.');
            return;
        }

        // Teachers may only submit their own subjects within assigned classes.
        if (Auth::role() === 'teacher') {
            $teacherId = Auth::teacherProfileId();
            if (!$teacherId
                || (int) $subject['teacher_id'] !== $teacherId
                || (int) $subject['class_id'] !== (int) $exam['class_id']) {
                $this->renderError('You can only submit results for classes and subjects assigned to you.');
                return;
            }
        }

        $state = $db->fetch(
            'SELECT COUNT(*) AS c, MAX(status) AS status FROM results WHERE exam_id = ? AND subject_id = ?',
            [$examId, $subjectId]
        );
        if ((int) $state['c'] === 0) {
            Flash::set('error', 'Enter and save marks before submitting.');
            $this->redirect('results/teacher-entry?class_id=' . (int) $exam['class_id'] . '&subject_id=' . $subjectId . '&exam_id=' . $examId);
            return;
        }
        if (!in_array($state['status'], ['draft', 'returned'], true)) {
            Flash::set('error', 'These results are already ' . str_replace('_', ' ', (string) $state['status']) . ' and cannot be submitted again.');
            $this->redirect('results/review/' . $examId . '/' . $subjectId);
            return;
        }

        // True when this submission follows an admin reopen, so the admins get a
        // distinct 'corrected results' notification instead of the generic one.
        $reopened = (int) $db->fetch(
            'SELECT COUNT(*) AS c FROM results WHERE exam_id = ? AND subject_id = ? AND reopened_at IS NOT NULL',
            [$examId, $subjectId]
        )['c'] > 0;

        $db->update(
            'results',
            ['status' => 'submitted', 'published_at' => null, 'reopened_at' => null],
            'exam_id = :e AND subject_id = :s',
            [':e' => $examId, ':s' => $subjectId]
        );

        if ($reopened) {
            $this->notifyResultsCorrected($exam, $subject);
        } else {
            $this->notifyResultsSubmitted($exam, $subject);
        }

        $this->logActivity('submit', 'results', 'Submitted results for exam #' . $examId . ' subject #' . $subjectId . ' for review');
        Flash::set('success', 'Results submitted for admin review.');
        $this->redirect('results/review/' . $examId . '/' . $subjectId);
    }

    /**
     * Admin approves submitted results so they are ready for publishing.
     * Accepted from Submitted or Returned.
     */
    public function approve(int $examId, int $subjectId): void
    {
        CSRF::check();
        Auth::requireRole(['admin', 'prefet', 'director', 'proviseur']);

        $db = Database::getInstance();
        $exam = (new Exam())->findWithDetails($examId);
        $subject = (new Subject())->find($subjectId);

        $examSubjectId = $exam ? ($exam['subject_id'] !== null ? (int) $exam['subject_id'] : null) : null;
        if (!$exam || !$subject
            || ($examSubjectId !== null && $examSubjectId !== (int) $subjectId)
            || (int) $exam['class_id'] !== (int) $subject['class_id']) {
            $this->renderError('Exam or subject not found for approving results.');
            return;
        }

        $state = $db->fetch(
            'SELECT COUNT(*) AS c, MAX(status) AS status FROM results WHERE exam_id = ? AND subject_id = ?',
            [$examId, $subjectId]
        );
        if ((int) $state['c'] === 0) {
            Flash::set('error', 'No results to approve.');
            $this->redirect('results/review-queue');
            return;
        }
        if (!in_array($state['status'], ['submitted', 'returned'], true)) {
            Flash::set('error', 'Only submitted results can be approved.');
            $this->redirect('results/review/' . $examId . '/' . $subjectId);
            return;
        }

        $db->update(
            'results',
            ['status' => 'approved', 'published_at' => null, 'correction_comment' => null],
            'exam_id = :e AND subject_id = :s',
            [':e' => $examId, ':s' => $subjectId]
        );

        $this->logActivity('approve', 'results', 'Approved results for exam #' . $examId . ' subject #' . $subjectId);
        Flash::set('success', 'Results approved. You can now publish them.');
        $this->redirect('results/review/' . $examId . '/' . $subjectId);
    }

    /**
     * Admin returns submitted/approved results to the teacher for correction.
     * The teacher can then edit them and submit again.
     */
    public function returnResults(int $examId, int $subjectId): void
    {
        CSRF::check();
        Auth::requireRole(['admin', 'prefet', 'director', 'proviseur']);

        $db = Database::getInstance();
        $exam = (new Exam())->findWithDetails($examId);
        $subject = (new Subject())->find($subjectId);

        $examSubjectId = $exam ? ($exam['subject_id'] !== null ? (int) $exam['subject_id'] : null) : null;
        if (!$exam || !$subject
            || ($examSubjectId !== null && $examSubjectId !== (int) $subjectId)
            || (int) $exam['class_id'] !== (int) $subject['class_id']) {
            $this->renderError('Exam or subject not found for returning results.');
            return;
        }

        $state = $db->fetch(
            'SELECT COUNT(*) AS c, MAX(status) AS status FROM results WHERE exam_id = ? AND subject_id = ?',
            [$examId, $subjectId]
        );
        if ((int) $state['c'] === 0) {
            Flash::set('error', 'No results to return.');
            $this->redirect('results/review-queue');
            return;
        }
        if (!in_array($state['status'], ['submitted', 'approved'], true)) {
            Flash::set('error', 'Only submitted or approved results can be returned for correction.');
            $this->redirect('results/review/' . $examId . '/' . $subjectId);
            return;
        }

        // A correction comment is required so the teacher knows what to fix.
        $comment = trim((string) ($_POST['comment'] ?? ''));
        if ($comment === '') {
            Flash::set('error', 'Please enter a correction comment so the teacher knows what to fix.');
            $this->redirect('results/review/' . $examId . '/' . $subjectId);
            return;
        }
        $comment = mb_substr($comment, 0, 500);

        $db->update(
            'results',
            ['status' => 'returned', 'published_at' => null, 'correction_comment' => $comment],
            'exam_id = :e AND subject_id = :s',
            [':e' => $examId, ':s' => $subjectId]
        );

        $this->notifyResultsReturned($exam, $subject, $comment);

        $this->logActivity('return', 'results', 'Returned results for exam #' . $examId . ' subject #' . $subjectId . ' for correction: ' . $comment);
        Flash::set('success', 'Results returned to the teacher for correction.');
        $this->redirect('results/review/' . $examId . '/' . $subjectId);
    }

    /**
     * Review entered results for one exam + subject before publishing.
     * Read-only table with publish / reopen / lock controls.
     */
    public function review(int $examId, int $subjectId): void
    {
        Auth::requireAny(['publish_results', 'edit_results', 'create_results', 'view_class_results', 'results.manage']);

        $db = Database::getInstance();
        $exam = $db->fetch(
            'SELECT e.*, c.name AS class_name, sec.name AS section_name, su.name AS subject_name
             FROM exams e
             LEFT JOIN classes c ON c.id = e.class_id
             LEFT JOIN sections sec ON sec.id = e.section_id
             LEFT JOIN subjects su ON su.id = e.subject_id
             WHERE e.id = ?',
            [$examId]
        );
        $subject = (new Subject())->find($subjectId);

        // Class-wide exams (subject_id NULL) may review any subject of the
        // exam's class; subject-specific exams must match their own subject.
        $examSubjectId = $exam['subject_id'] !== null ? (int) $exam['subject_id'] : null;
        if (!$exam || !$subject
            || ($examSubjectId !== null && $examSubjectId !== (int) $subjectId)
            || (int) $exam['class_id'] !== (int) $subject['class_id']) {
            $this->renderError('Exam or subject not found for this result review.');
            return;
        }

        // Teachers may only review their own subjects within assigned classes.
        if (Auth::role() === 'teacher'
            && ((int) $subject['teacher_id'] !== Auth::teacherProfileId()
                || (int) $subject['class_id'] !== (int) $exam['class_id'])) {
            $this->renderError('You can only review results for classes and subjects assigned to you.');
            return;
        }

        $rows = $this->result->forStudentExamWithSubject($examId, $subjectId);

        $this->render('results/review', [
            'exam'      => $exam,
            'subject'   => $subject,
            'rows'      => $rows,
            'status'    => $rows ? $rows[0]['status'] : 'draft',
            'correctionComment' => $rows ? ($rows[0]['correction_comment'] ?? null) : null,
            'publishedAt' => $rows ? $rows[0]['published_at'] : null,
            'canPublish'  => Auth::can('publish_results'),
            'canManage'   => Auth::can('results.manage'),
            'canSubmit'   => Auth::can('create_results') || Auth::can('edit_results') || Auth::can('results.manage'),
        ]);
    }

    /**
     * Review queue for admins: every exam+subject that has entered results,
     * with its status, so submitted (draft) results can be reviewed/published.
     */
    public function reviewQueue(): void
    {
        Auth::requireRole(['admin', 'prefet', 'director', 'proviseur']);

        $db = Database::getInstance();
        $status = trim((string) ($_GET['status'] ?? ''));

        $sql = 'SELECT r.exam_id, r.subject_id,
                       MAX(e.name) AS exam_name,
                       MAX(e.class_id) AS class_id,
                       MAX(et.name) AS type_name,
                       MAX(c.name) AS class_name,
                       MAX(su.name) AS subject_name,
                       MAX(r.status) AS status,
                       MAX(r.correction_comment) AS correction_comment,
                       MAX(r.published_at) AS published_at,
                       COUNT(*) AS students,
                       MIN(r.created_at) AS first_entered,
                       MAX(r.updated_at) AS last_updated
                FROM results r
                INNER JOIN exams e ON e.id = r.exam_id
                LEFT JOIN exam_types et ON et.id = e.exam_type_id
                LEFT JOIN classes c ON c.id = e.class_id
                LEFT JOIN subjects su ON su.id = r.subject_id
                WHERE 1 = 1';
        $params = [];

        if ($status !== '' && in_array($status, ['draft', 'submitted', 'approved', 'returned', 'published', 'locked'], true)) {
            $sql .= ' AND r.status = ?';
            $params[] = $status;
        }

        $sql .= ' GROUP BY r.exam_id, r.subject_id
                  ORDER BY
                    CASE MAX(r.status)
                        WHEN "submitted" THEN 0
                        WHEN "approved"  THEN 1
                        WHEN "returned"  THEN 2
                        WHEN "draft"     THEN 3
                        WHEN "published" THEN 4
                        ELSE 5
                    END ASC,
                    MAX(e.start_date) DESC, r.exam_id DESC';

        $rows = $db->fetchAll($sql, $params);

        $this->render('results/review_queue', [
            'rows'   => $rows,
            'status' => $status,
        ]);
    }

    /**
     * Publish finalized results for an exam + subject: they become visible
     * to the students of that class and a notification is sent to each
     * student with a linked user account.
     */
    public function publish(int $examId, int $subjectId): void
    {
        CSRF::check();
        Auth::requireAny(['publish_results', 'results.manage']);

        $db = Database::getInstance();
        $exam = (new Exam())->findWithDetails($examId);
        $subject = (new Subject())->find($subjectId);

        // Class-wide exams (subject_id NULL) may publish any subject of the
        // exam's class; subject-specific exams must match their own subject.
        $examSubjectId = $exam['subject_id'] !== null ? (int) $exam['subject_id'] : null;
        if (!$exam || !$subject
            || ($examSubjectId !== null && $examSubjectId !== (int) $subjectId)
            || (int) $exam['class_id'] !== (int) $subject['class_id']) {
            $this->renderError('Exam or subject not found for publishing.');
            return;
        }

        // Teachers may only publish their own subjects within assigned classes.
        if (Auth::role() === 'teacher'
            && ((int) $subject['teacher_id'] !== Auth::teacherProfileId()
                || (int) $subject['class_id'] !== (int) $exam['class_id'])) {
            $this->renderError('You can only publish results for classes and subjects assigned to you.');
            return;
        }

        // Approved (or Submitted for direct review-and-publish) results may be
        // published: Draft -> Submitted -> (Approved) -> Published. The whole
        // exam+subject group moves together.
        $state = $db->fetch(
            'SELECT COUNT(*) AS c, MAX(status) AS status FROM results WHERE exam_id = ? AND subject_id = ?',
            [$examId, $subjectId]
        );
        if ((int) $state['c'] === 0) {
            Flash::set('error', 'No results to publish.');
            $this->redirect('results/review/' . $examId . '/' . $subjectId);
            return;
        }
        if (!in_array($state['status'], ['submitted', 'approved'], true)) {
            Flash::set('error', 'Only submitted or approved results can be published.');
            $this->redirect('results/review/' . $examId . '/' . $subjectId);
            return;
        }

        $db->update(
            'results',
            ['status' => 'published', 'published_at' => date('Y-m-d H:i:s'), 'correction_comment' => null],
            'exam_id = :e AND subject_id = :s',
            [':e' => $examId, ':s' => $subjectId]
        );

        // Make a previously-draft exam visible to students as published.
        if ($exam['status'] === 'draft') {
            (new Exam())->update($examId, ['status' => 'published']);
        }

        $this->notifyResultsPublished($exam, $subject);

        $this->logActivity('publish', 'results', 'Published results for exam #' . $examId . ' subject #' . $subjectId);
        Flash::set('success', 'Results published to students.');
        $this->redirect('results/review/' . $examId . '/' . $subjectId);
    }

    /**
     * Reopen published results (back to draft) so they can be corrected.
     * Admin / principal only. Notifies the submitting teacher with the
     * reopening reason.
     */
    public function reopen(int $examId, int $subjectId): void
    {
        CSRF::check();
        Auth::requireRole(['admin', 'prefet', 'director', 'proviseur']);

        $db = Database::getInstance();
        $exam = (new Exam())->findWithDetails($examId);
        $subject = (new Subject())->find($subjectId);

        $examSubjectId = $exam ? ($exam['subject_id'] !== null ? (int) $exam['subject_id'] : null) : null;
        if (!$exam || !$subject
            || ($examSubjectId !== null && $examSubjectId !== (int) $subjectId)
            || (int) $exam['class_id'] !== (int) $subject['class_id']) {
            $this->renderError('Exam or subject not found for reopening results.');
            return;
        }

        $state = $db->fetch(
            'SELECT COUNT(*) AS c, MAX(status) AS status FROM results WHERE exam_id = ? AND subject_id = ?',
            [$examId, $subjectId]
        );
        if ((int) $state['c'] === 0) {
            Flash::set('error', 'No results to reopen.');
            $this->redirect('results/review/' . $examId . '/' . $subjectId);
            return;
        }
        $wasStatus = $state['status'];

        // A reason is required so the teacher knows what to correct.
        $comment = trim((string) ($_POST['comment'] ?? ''));
        if ($comment === '') {
            Flash::set('error', 'Please enter a reason so the teacher knows what to correct.');
            $this->redirect('results/review/' . $examId . '/' . $subjectId);
            return;
        }
        $comment = mb_substr($comment, 0, 500);

        $db->update(
            'results',
            ['status' => 'draft', 'published_at' => null, 'correction_comment' => null, 'reopened_at' => date('Y-m-d H:i:s')],
            'exam_id = :e AND subject_id = :s',
            [':e' => $examId, ':s' => $subjectId]
        );

        if (in_array($wasStatus, ['published', 'locked'], true)) {
            $this->notifyResultsReopened($exam, $subject, $comment);
        }

        $this->logActivity('reopen', 'results', 'Reopened results for exam #' . $examId . ' subject #' . $subjectId . ' for correction: ' . mb_substr($comment, 0, 150));
        Flash::set('success', 'Results reopened for editing.');
        $this->redirect('results/review/' . $examId . '/' . $subjectId);
    }

    /**
     * Lock results (final, immutable). Admin / principal only.
     */
    public function lock(int $examId, int $subjectId): void
    {
        CSRF::check();
        Auth::requireRole(['admin', 'prefet', 'director', 'proviseur']);

        $db = Database::getInstance();
        $db->update(
            'results',
            ['status' => 'locked'],
            'exam_id = :e AND subject_id = :s',
            [':e' => $examId, ':s' => $subjectId]
        );

        $this->logActivity('lock', 'results', 'Locked results for exam #' . $examId . ' subject #' . $subjectId);
        Flash::set('success', 'Results locked.');
        $this->redirect('results/review/' . $examId . '/' . $subjectId);
    }

    /**
     * Send a 'results published' notification to each student who has a
     * result record for the given exam + subject and a linked user account.
     */
    protected function notifyResultsPublished(array $exam, array $subject): void
    {
        $db = Database::getInstance();
        $recipients = $db->fetchAll(
            'SELECT DISTINCT s.user_id
             FROM results r
             INNER JOIN students s ON s.id = r.student_id
             INNER JOIN users u ON u.id = s.user_id
             WHERE r.exam_id = ? AND r.subject_id = ?
               AND s.status = "active"',
            [(int) $exam['id'], (int) $subject['id']]
        );

        $title = 'Exam Results Published';
        $message = 'Your results for ' . $exam['name'] . ' (' . $subject['name'] . ') have been published. View them under My Results.';

        foreach ($recipients as $recipient) {
            (new Notification())->push((int) $recipient['user_id'], $title, $message, 'results');
        }
    }

    /**
     * Send a 'results submitted' notification to every admin and super admin
     * user so they can review the queue.
     */
    protected function notifyResultsSubmitted(array $exam, array $subject): void
    {
        $db = Database::getInstance();

        $teacherId = Auth::teacherProfileId();
        if ($teacherId < 1) {
            $owner = $db->fetch('SELECT teacher_id FROM subjects WHERE id = ?', [(int) $subject['id']]);
            $teacherId = $owner ? (int) $owner['teacher_id'] : 0;
        }
        $teacherName = '';
        if ($teacherId > 0) {
            $teacher = $db->fetch('SELECT first_name, last_name FROM teachers WHERE id = ?', [$teacherId]);
            if ($teacher) {
                $teacherName = trim(($teacher['first_name'] ?? '') . ' ' . ($teacher['last_name'] ?? ''));
            }
        }
        $who = $teacherName !== '' ? $teacherName : 'A teacher';

        $title = 'Results Submitted for Review';
        $message = $who . ' submitted ' . ($exam['class_name'] ?? '')
            . ' ' . $subject['name'] . ' ' . $exam['name']
            . ' results for review on ' . ldate('M d, Y H:i') . '.';

        $admins = $db->fetchAll(
            'SELECT u.id FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE LOWER(REPLACE(REPLACE(r.name, " ", ""), "_", "")) IN ("admin", "superadmin")'
        );
        foreach ($admins as $admin) {
            (new Notification())->push((int) $admin['id'], $title, $message, 'warning', 'results/review-queue');
        }
    }

    /**
     * Send a 'corrected results submitted' notification to every admin and super
     * admin when a teacher resubmits results that an admin had reopened.
     */
    protected function notifyResultsCorrected(array $exam, array $subject): void
    {
        $db = Database::getInstance();

        $teacherId = Auth::teacherProfileId();
        if ($teacherId < 1) {
            $owner = $db->fetch('SELECT teacher_id FROM subjects WHERE id = ?', [(int) $subject['id']]);
            $teacherId = $owner ? (int) $owner['teacher_id'] : 0;
        }
        $teacherName = '';
        if ($teacherId > 0) {
            $teacher = $db->fetch('SELECT first_name, last_name FROM teachers WHERE id = ?', [$teacherId]);
            if ($teacher) {
                $teacherName = trim(($teacher['first_name'] ?? '') . ' ' . ($teacher['last_name'] ?? ''));
            }
        }
        $firstName = trim((string) ($teacher['first_name'] ?? ''));
        $who = $firstName !== '' ? $firstName : ($teacherName !== '' ? $teacherName : 'A teacher');

        $title = 'Corrected Results Submitted';
        $message = 'Teacher ' . $who . ' has corrected and resubmitted the reopened '
            . ($exam['class_name'] ?? '') . ' ' . $subject['name'] . ' ' . $exam['name']
            . ' results on ' . ldate('M d, Y H:i') . '. Please review again.';

        $admins = $db->fetchAll(
            'SELECT u.id FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE LOWER(REPLACE(REPLACE(r.name, " ", ""), "_", "")) IN ("admin", "superadmin")'
        );
        foreach ($admins as $admin) {
            (new Notification())->push((int) $admin['id'], $title, $message, 'warning', 'results/review-queue');
        }
    }

    /**
     * Send a 'results returned' notification to the teacher who submitted the
     * subject's results, linking straight back to the marks entry page.
     */
    protected function notifyResultsReturned(array $exam, array $subject, string $comment): void
    {
        $db = Database::getInstance();
        $teacher = $db->fetch(
            'SELECT t.user_id FROM subjects su JOIN teachers t ON t.id = su.teacher_id WHERE su.id = ?',
            [(int) $subject['id']]
        );
        if (!$teacher || (int) $teacher['user_id'] < 1) {
            return;
        }

        $title = 'Results Returned for Correction';
        $message = $subject['name'] . ' results for ' . $exam['name']
            . ' (' . ($exam['class_name'] ?? '') . ') were returned by the admin.'
            . ' Comment: ' . $comment . '. Please correct the marks and resubmit.';
        $link = 'results/teacher-entry?class_id=' . (int) $exam['class_id']
            . '&subject_id=' . (int) $subject['id']
            . '&exam_id=' . (int) $exam['id'];

        (new Notification())->push((int) $teacher['user_id'], $title, $message, 'warning', $link);
    }

    /**
     * Send a 'results reopened' notification to the teacher who submitted the
     * subject's results, linking straight back to the marks entry page.
     */
    protected function notifyResultsReopened(array $exam, array $subject, string $comment): void
    {
        $db = Database::getInstance();
        $teacher = $db->fetch(
            'SELECT t.user_id FROM subjects su JOIN teachers t ON t.id = su.teacher_id WHERE su.id = ?',
            [(int) $subject['id']]
        );
        if (!$teacher || (int) $teacher['user_id'] < 1) {
            return;
        }

        $title = 'Results Reopened for Correction';
        $message = 'Your ' . $subject['name'] . ' results for ' . $exam['name']
            . ' (' . ($exam['class_name'] ?? '') . ') have been reopened by the Administrator'
            . ' for correction on ' . ldate('M d, Y H:i') . '.'
            . ' Reason: ' . $comment . '. Please review and resubmit the results.';
        $link = 'results/teacher-entry?class_id=' . (int) $exam['class_id']
            . '&subject_id=' . (int) $subject['id']
            . '&exam_id=' . (int) $exam['id'];

        (new Notification())->push((int) $teacher['user_id'], $title, $message, 'warning', $link);
    }

    /**
     * Ranked results for an exam.
     */
    public function rank(int $examId): void
    {
        Auth::requireRole(['admin', 'prefet', 'director', 'proviseur']);

        $exam = (new Exam())->find($examId);
        if (!$exam) {
            $this->renderError('Exam not found.');
            return;
        }

        $this->render('results/rank', [
            'exam'  => $exam,
            'ranks' => $this->result->ranks($examId),
        ]);
    }

    /**
     * Full report card for a student in an exam (printable).
     */
    public function reportCard(int $examId, int $studentId): void
    {
        Auth::requireLogin();

        // Students may only view their own report card; parents may only view
        // their linked children; other roles need the management permission.
        if (!self::canViewResults($studentId)) {
            http_response_code(403);
            $view = new View();
            $view->render('errors/403', ['message' => 'You are not allowed to view this report card.'], 'layouts/app_layout');
            return;
        }

        $student = (new Student())->findWithDetails($studentId);
        $exam = (new Exam())->findWithDetails($examId);

        if (!$student || !$exam) {
            $this->renderError('Report card data not found.');
            return;
        }

        $marks = $this->result->forStudentExam($studentId, $examId);

        // Draft results are not visible to students or parents.
        if (self::isRestrictedResultViewer() && !self::hasPublishedResult($marks)) {
            http_response_code(403);
            $view = new View();
            $view->render('errors/403', ['message' => 'These results have not been published yet.'], 'layouts/app_layout');
            return;
        }

        $total = 0.0;
        $maxTotal = 0.0;
        foreach ($marks as $m) {
            $total += (float) $m['marks_obtained'];
            $maxTotal += (float) $m['max_marks'];
        }
        $percentage = $maxTotal > 0 ? ($total / $maxTotal) * 100 : 0;
        $overall = gradeFor($percentage);

        // Position within the exam.
        $ranks = $this->result->ranks($examId);
        $position = 1;
        foreach ($ranks as $i => $rank) {
            if ((int) $rank['student_id'] === $studentId) {
                $position = $i + 1;
                break;
            }
        }

        // Attendance summary across the exam window (or all-time if no dates).
        $attendance = $this->result->attendanceSummary($studentId, $exam['start_date'] ?: null, $exam['end_date'] ?: null);

        $this->renderRaw('results/report_card', [
            'student'    => $student,
            'exam'       => $exam,
            'marks'      => $marks,
            'total'      => $total,
            'maxTotal'   => $maxTotal,
            'percentage' => $percentage,
            'overall'    => $overall,
            'position'   => $position,
            'rankCount'  => count($ranks),
            'attendance' => $attendance,
        ]);
    }

    /**
     * Student portal: list the logged-in student's own results.
     */
    public function myResults(): void
    {
        Auth::requireAny(['view_own_results_only', 'results.manage']);

        $student = (new Student())->firstWhere('user_id', Auth::id());
        if (!$student) {
            Flash::set('warning', 'No student profile linked to this account.');
            $this->redirect('dashboard/index');
            return;
        }

        $db = Database::getInstance();
        $exams = $db->fetchAll(
            'SELECT e.*, et.name AS type_name,
                    ROUND(SUM(r.marks_obtained),2) AS total,
                    ROUND(SUM(r.max_marks),2) AS max_total,
                    ROUND((SUM(r.marks_obtained)/SUM(r.max_marks))*100,2) AS percentage
             FROM results r
             INNER JOIN exams e ON e.id = r.exam_id
             LEFT JOIN exam_types et ON et.id = e.exam_type_id
             WHERE r.student_id = ? AND r.status IN ("published", "locked")
             GROUP BY r.exam_id ORDER BY e.start_date DESC',
            [$student['id']]
        );

        $this->render('results/my_results', ['student' => $student, 'exams' => $exams]);
    }

    /**
     * Student result display: subject-by-subject results for a student
     * in a completed exam. Accessible to the student, their parents,
     * or anyone with results.manage.
     */
    public function view(int $examId, int $studentId): void
    {
        Auth::requireLogin();

        // Students may only view their own result; parents may only view their
        // linked children; other roles need the management permission.
        if (!self::canViewResults($studentId)) {
            http_response_code(403);
            $view = new View();
            $view->render('errors/403', ['message' => 'You are not allowed to view this result.'], 'layouts/app_layout');
            return;
        }

        $student = (new Student())->findWithDetails($studentId);
        $exam    = (new Exam())->findWithDetails($examId);

        if (!$student || !$exam) {
            $this->renderError('Result data not found.');
            return;
        }

        $marks = $this->result->forStudentExam($studentId, $examId);

        // Draft results are not visible to students or parents.
        if (self::isRestrictedResultViewer() && !self::hasPublishedResult($marks)) {
            http_response_code(403);
            $view = new View();
            $view->render('errors/403', ['message' => 'These results have not been published yet.'], 'layouts/app_layout');
            return;
        }

        $total = 0.0;
        $maxTotal = 0.0;
        foreach ($marks as $m) {
            $total += (float) $m['marks_obtained'];
            $maxTotal += (float) $m['max_marks'];
        }
        $percentage = $maxTotal > 0 ? ($total / $maxTotal) * 100 : 0;
        $overall = gradeFor($percentage);

        $ranks = $this->result->ranks($examId);
        $position = 1;
        foreach ($ranks as $i => $rank) {
            if ((int) $rank['student_id'] === $studentId) {
                $position = $i + 1;
                break;
            }
        }

        $this->render('results/view', [
            'student'    => $student,
            'exam'       => $exam,
            'marks'      => $marks,
            'total'      => $total,
            'maxTotal'   => $maxTotal,
            'percentage' => $percentage,
            'overall'    => $overall,
            'position'   => $position,
            'rankCount'  => count($ranks),
        ]);
    }

    /**
     * Promote a student to the next class.
     */
    public function promote(int $studentId): void
    {
        CSRF::check();
        Auth::requireRole(['admin', 'prefet', 'director', 'proviseur']);

        $toClass = (int) ($_POST['to_class_id'] ?? 0);
        if (!$toClass) {
            Flash::set('error', 'Please select a destination class.');
            $this->redirect('results/promotions');
        }

        $student = (new Student())->find($studentId);
        if ($student) {
            (new Student())->update($studentId, ['class_id' => $toClass]);
            Database::getInstance()->insert('promotions', [
                'student_id'     => $studentId,
                'from_class_id'  => $student['class_id'],
                'to_class_id'    => $toClass,
                'session_id'     => (int) (new Setting())->get('academic_session_id') ?: null,
                'promoted_date'  => date('Y-m-d'),
                'remarks'        => trim($_POST['remarks'] ?? ''),
            ]);
            $this->logActivity('promote', 'results', 'Promoted student #' . $studentId);
            Flash::set('success', 'Student promoted successfully.');
        }
        $this->redirect('results/promotions');
    }

    /**
     * Promotion management page.
     */
    public function promotions(): void
    {
        Auth::requireRole(['admin', 'prefet', 'director', 'proviseur']);

        $db = Database::getInstance();
        $students = $db->fetchAll(
            'SELECT s.id, s.official_id, s.first_name, s.last_name, c.name AS class_name
             FROM students s LEFT JOIN classes c ON c.id = s.class_id WHERE s.status = "active" ORDER BY s.first_name'
        );
        $history = $db->fetchAll(
            'SELECT p.*, s.first_name, s.last_name, fc.name AS from_class, tc.name AS to_class
             FROM promotions p
             INNER JOIN students s ON s.id = p.student_id
             LEFT JOIN classes fc ON fc.id = p.from_class_id
             LEFT JOIN classes tc ON tc.id = p.to_class_id
             ORDER BY p.id DESC LIMIT 50'
        );

        $this->render('results/promotions', [
            'students' => $students,
            'classes'  => (new ClassRoom())->all('name'),
            'history'  => $history,
        ]);
    }

    protected function renderError(string $message): void
    {
        http_response_code(404);
        $this->render('errors/404', ['message' => $message]);
    }

    /**
     * Whether the current user may view results for a given student.
     * Students see only their own, parents only linked children, teachers
     * only students in their assigned classes, and any staff/management
     * role with results.manage may view everything.
     */
    protected static function canViewResults(int $studentId): bool
    {
        if (Auth::role() === 'student') {
            if (!Auth::can('view_own_results_only')) {
                return false;
            }
            $linked = (new Student())->firstWhere('user_id', Auth::id());
            return $linked && (int) $linked['id'] === $studentId;
        }

        if (Auth::role() === 'parent') {
            return (new Student())->isLinkedToParent($studentId, Auth::id());
        }

        if (Auth::role() === 'teacher') {
            if (!Auth::can('view_class_results')) {
                return false;
            }
            $student = Database::getInstance()->fetch('SELECT class_id FROM students WHERE id = ?', [$studentId]);
            if (!$student || !$student['class_id']) {
                return false;
            }
            return in_array((int) $student['class_id'], Auth::assignedClassIds(), true);
        }

        return Auth::can('results.manage');
    }

    /**
     * Whether the current role only ever sees published/locked results
     * (students and parents) as opposed to staff who may preview drafts.
     */
    protected static function isRestrictedResultViewer(): bool
    {
        return in_array(Auth::role(), ['student', 'parent'], true);
    }

    /**
     * Whether at least one of the given result rows has been published
     * or locked (i.e. is visible to students/parents).
     */
    protected static function hasPublishedResult(array $marks): bool
    {
        foreach ($marks as $m) {
            if (in_array($m['status'] ?? '', ['published', 'locked'], true)) {
                return true;
            }
        }
        return false;
    }
}
