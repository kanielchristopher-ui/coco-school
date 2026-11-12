<?php
/**
 * ============================================================
 *  TeacherController
 *  CRUD for teacher profiles with photo & CV upload.
 * ============================================================
 */

declare(strict_types=1);

class TeacherController extends Controller
{
    protected Teacher $teacher;

    public function __construct()
    {
        parent::__construct();
        $this->teacher = new Teacher();
    }

    public function index(): void
    {
        Auth::requirePermission('teachers.manage');

        $search        = trim($_GET['q'] ?? '');
        $department    = trim($_GET['department'] ?? '');
        $qualification = trim($_GET['qualification'] ?? '');
        $status        = trim($_GET['status'] ?? '');
        $sort          = trim($_GET['sort'] ?? 'id');
        $dir           = strtolower(trim($_GET['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';

        $rows = $this->teacher->listAll(
            $search ?: null,
            $department ?: null,
            $qualification ?: null,
            $status ?: null,
            $sort,
            $dir
        );
        $total = count($rows);

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $pagination = new Pagination($total, $page, PAGINATION_LIMIT);
        $rows = array_slice($rows, $pagination->offset(), PAGINATION_LIMIT);

        $this->render('teachers/index', [
            'rows'          => $rows,
            'pagination'    => $pagination,
            'total'         => $total,
            'search'        => $search,
            'department'    => $department,
            'qualification' => $qualification,
            'status'        => $status,
            'departments'   => $this->teacher->departments(),
            'qualifications'=> $this->teacher->qualifications(),
            'sort'          => $sort,
            'dir'           => $dir,
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('teachers.manage');
        $this->render('teachers/create');
    }

    public function store(): void
    {
        CSRF::check();
        Auth::requirePermission('teachers.manage');

        $data = $this->sanitize($_POST);

        $validator = new Validator();
        if (!$validator->validate($data, [
            'first_name' => 'required|max:60',
            'email'      => 'email|max:120',
            'phone'      => 'phone',
            'dob'        => 'date',
            'salary'     => 'numeric',
            'status'     => 'in:active,inactive,left',
        ])) {
            Flash::set('error', $validator->errorsFlat());
            $this->redirect('teachers/create');
        }

        $data['employee_id'] = $this->teacher->nextEmployeeId();
        $data['status'] = $data['status'] ?: 'active';

        $photo = $this->uploadFile('photo', 'teachers', ALLOWED_IMAGE_TYPES);
        if ($photo !== false) {
            $data['photo'] = $photo;
        }
        $cv = $this->uploadFile('cv', 'teachers', ['pdf', 'doc', 'docx']);
        if ($cv !== false) {
            $data['cv'] = $cv;
        }

        $db = Database::getInstance();
        $db->beginTransaction();
        $accountSummary = null;
        try {
            $id = $this->teacher->create($data);

            // Provision (or reuse) the teacher's linked login account.
            $accountEmail = trim((string) ($_POST['account_email'] ?? ''));
            if ($accountEmail === '') {
                $accountEmail = trim((string) ($data['email'] ?? ''));
            }
            if ($accountEmail !== '') {
                $accountSummary = $this->teacher->provisionUser($id, [
                    'email'            => $accountEmail,
                    'username'         => trim((string) ($_POST['account_username'] ?? '')),
                    'password'         => (string) ($_POST['account_password'] ?? ''),
                    'default_password' => DEFAULT_TEACHER_PASSWORD,
                ], 'teacher');
            }

            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            Flash::set('error', 'Could not create the teacher record. Please try again.');
            $this->redirect('teachers/create');
            return;
        }

        $this->logActivity('create', 'teachers', 'Created teacher #' . $id . ($accountSummary && $accountSummary['created'] ? ' with new login account' : ($accountSummary && $accountSummary['status'] === 'linked' ? ' linked to existing login' : '')));

        $message = 'Teacher created successfully.';
        if ($accountSummary) {
            if ($accountSummary['status'] === 'created') {
                $message .= ' Login account created — username "' . $accountSummary['username'] . '", password "' . $accountSummary['password'] . '".';
            } elseif ($accountSummary['status'] === 'linked') {
                $message .= ' Linked to existing login "' . $accountSummary['username'] . '".';
            } elseif ($accountSummary['status'] === 'role-conflict') {
                $message .= ' The email belongs to a non-teacher login, so no account was linked.';
            }
        }
        Flash::set('success', $message);
        $this->redirect('teachers/show/' . $id);
    }

    /**
     * Idempotent repair: link every teacher without a user account to an
     * existing account by email, or provision a new login for them.
     */
    public function repairLinks(): void
    {
        Auth::requirePermission('teachers.manage');

        $stats = (new Teacher())->repairUnlinked('teacher', DEFAULT_TEACHER_PASSWORD);
        $total = $stats['linked'] + $stats['created'];
        $this->logActivity('repair-links', 'teachers', 'Repaired ' . $total . ' teacher login link(s)');

        $message = 'Teacher login links repaired: ' . $stats['linked'] . ' linked to existing accounts, ' . $stats['created'] . ' new accounts created.';
        if ($stats['skipped'] > 0) {
            $message .= ' ' . $stats['skipped'] . ' skipped (no email available).';
        }
        Flash::set('success', $message);
        $this->redirect('teachers/index');
    }

    public function show(int $id): void
    {
        Auth::requirePermission('teachers.manage');

        $teacher = $this->teacher->find($id);
        if (!$teacher) {
            $this->renderError('Teacher not found.');
            return;
        }

        $db = Database::getInstance();
        $teacher['subjects'] = $db->fetchAll(
            'SELECT su.*, c.name AS class_name FROM subjects su
             LEFT JOIN classes c ON c.id = su.class_id WHERE su.teacher_id = ?',
            [$id]
        );
        $teacher['classes'] = $db->fetchAll(
            'SELECT c.* FROM classes c WHERE c.class_teacher_id = ? ORDER BY c.numeric_name ASC',
            [$id]
        );
        $teacher['attendance'] = $db->fetchAll(
            'SELECT status, COUNT(*) AS total FROM teacher_attendance WHERE teacher_id = ? GROUP BY status',
            [$id]
        );

        $this->render('teachers/show', ['teacher' => $teacher]);
    }

    public function edit(int $id): void
    {
        Auth::requirePermission('teachers.manage');

        $teacher = $this->teacher->find($id);
        if (!$teacher) {
            $this->renderError('Teacher not found.');
            return;
        }
        $this->render('teachers/edit', ['teacher' => $teacher]);
    }

    public function update(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('teachers.manage');

        $teacher = $this->teacher->find($id);
        if (!$teacher) {
            $this->renderError('Teacher not found.');
            return;
        }

        $data = $this->sanitize($_POST);
        unset($data['employee_id']);

        $validator = new Validator();
        if (!$validator->validate($data, ['first_name' => 'required|max:60', 'email' => 'email', 'salary' => 'numeric', 'status' => 'in:active,inactive,left'])) {
            Flash::set('error', $validator->errorsFlat());
            $this->redirect('teachers/edit/' . $id);
        }

        $photo = $this->uploadFile('photo', 'teachers', ALLOWED_IMAGE_TYPES);
        if ($photo !== false) {
            $data['photo'] = $photo;
        }
        $cv = $this->uploadFile('cv', 'teachers', ['pdf', 'doc', 'docx']);
        if ($cv !== false) {
            $data['cv'] = $cv;
        }

        $this->teacher->update($id, $data);
        $this->logActivity('update', 'teachers', 'Updated teacher #' . $id);

        Flash::set('success', 'Teacher updated successfully.');
        $this->redirect('teachers/show/' . $id);
    }

    /**
     * Delete a teacher with a complete cascade cleanup, in a transaction.
     *
     * DEMO / PRACTICE MODE: every record tied to the selected teacher is
     * removed (profile, linked account, notifications, class / subject
     * assignments, payroll and results). For real-school production use this
     * should be replaced with an Inactive/Archived status so payroll, results,
     * attendance and other historical records are preserved.
     */
    public function destroy(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('teachers.manage');

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $teacher = $this->teacher->find($id);
            if (!$teacher) {
                $db->rollBack();
                Flash::set('error', 'Teacher not found.');
                $this->redirect('teachers/index');
                return;
            }

            $teacherUserId = (int) ($teacher['user_id'] ?? 0);

            // Results created by this teacher (a result belongs to the subject
            // this teacher teaches). Removed before the subject is unassigned.
            $db->delete(
                'results',
                'subject_id IN (SELECT id FROM subjects WHERE teacher_id = ?)',
                [$id]
            );

            // Academic updates authored by this teacher (table may not exist on
            // older installs, so never let it block the deletion).
            $academicUpdatesTable = (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name = 'academic_updates'"
            );
            if ($academicUpdatesTable > 0) {
                $db->delete('academic_updates', 'teacher_id = ?', [$id]);
            }

            // Teacher-class and teacher-subject assignments are removed; the
            // class / subject records themselves are never deleted.
            $db->update('classes', ['class_teacher_id' => null], 'class_teacher_id = ?', [$id]);
            $db->update('subjects', ['teacher_id' => null], 'teacher_id = ?', [$id]);
            $db->update('timetables', ['teacher_id' => null], 'teacher_id = ?', [$id]);

            // Payroll records for this teacher.
            $db->delete('salary', 'teacher_id = ?', [$id]);

            // The payrolls table only exists on installations with the payroll
            // module. Never let a missing optional table block deleting a
            // teacher: skip it when it is not present.
            $payrollsTable = (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name = 'payrolls'"
            );
            if ($payrollsTable > 0) {
                $db->delete('payrolls', 'employee_type = ? AND employee_id = ?', ['teacher', $id]);
            }

            // Teacher attendance.
            $db->delete('teacher_attendance', 'teacher_id = ?', [$id]);

            // Notifications and messages belonging to the teacher's login
            // account (also removed by FK cascade when the user is deleted, but
            // cleaned up explicitly here so they never survive the deletion).
            if ($teacherUserId > 0) {
                $db->delete('notifications', 'user_id = ?', [$teacherUserId]);
                $db->delete('messages', 'sender_id = ? OR receiver_id = ?', [$teacherUserId, $teacherUserId]);
            }

            // Linked teacher user account: only when it is not shared with
            // another profile. Notifications, password resets, remember tokens
            // and messages are removed together with it via FK cascade, and any
            // still-open session for the account is invalidated by Auth::check()
            // on the teacher's next request, so a deleted teacher can no longer
            // log in.
            if ($teacherUserId > 0) {
                $shared = (int) $db->fetchColumn(
                    'SELECT (SELECT COUNT(*) FROM students WHERE user_id = ?)
                          + (SELECT COUNT(*) FROM teachers WHERE user_id = ? AND id <> ?)
                          + (SELECT COUNT(*) FROM staff WHERE user_id = ?)
                          + (SELECT COUNT(*) FROM parents WHERE user_id = ?)',
                    [$teacherUserId, $teacherUserId, $id, $teacherUserId, $teacherUserId]
                );
                if ($shared === 0) {
                    $db->delete('users', 'id = ?', [$teacherUserId]);
                }
            }

            // Teacher profile.
            $this->teacher->delete($id);

            $db->commit();

            // Force logout if the deleted teacher happens to be the currently
            // authenticated user (normally impossible: teachers cannot delete
            // teachers, but keep the session consistent if it ever occurs).
            if ($teacherUserId > 0 && $teacherUserId === Auth::id()) {
                Auth::logout();
            }

            $this->logActivity('delete', 'teachers', 'Deleted teacher #' . $id . ' with full cascade cleanup (demo mode)');
            Flash::set('success', 'Teacher and all related records (account, notifications, assignments, payroll, results) were deleted.');
            $this->redirect('teachers/index');
        } catch (Throwable $e) {
            $db->rollBack();
            error_log('Teacher delete failed (teacher_id=' . $id . '): ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            Flash::set('error', 'Could not delete the teacher. Error: ' . $e->getMessage());
            $this->redirect('teachers/index');
        }
    }

    /**
     * Export teachers to CSV (respects active filters).
     */
    public function exportCsv(): void
    {
        Auth::requirePermission('teachers.manage');

        $rows = $this->filteredRows();
        $headers = ['Official ID', 'Full Name', 'Department', 'Qualification', 'Phone', 'Salary', 'Status'];
        $out = array_map(fn($t) => [
            $t['official_id'], trim($t['first_name'] . ' ' . $t['last_name']),
            $t['department'], $t['qualification'], $t['phone'], $t['salary'], $t['status'],
        ], $rows);

        Exporter::csv('teachers_' . date('Ymd'), $headers, $out);
    }

    /**
     * Export teachers to Excel (respects active filters).
     */
    public function exportExcel(): void
    {
        Auth::requirePermission('teachers.manage');

        $rows = $this->filteredRows();
        $headers = ['Official ID', 'Full Name', 'Department', 'Qualification', 'Phone', 'Salary', 'Status'];
        $out = array_map(fn($t) => [
            $t['official_id'], trim($t['first_name'] . ' ' . $t['last_name']),
            $t['department'], $t['qualification'], $t['phone'], $t['salary'], $t['status'],
        ], $rows);

        Exporter::excel('teachers_' . date('Ymd'), $headers, $out);
    }

    /**
     * Render a printable (print-optimized) list for "Export PDF".
     */
    public function exportPdf(): void
    {
        Auth::requirePermission('teachers.manage');

        $this->renderRaw('teachers/export_pdf', ['rows' => $this->filteredRows()]);
    }

    /**
     * Build the filtered row set shared by CSV / Excel / PDF exports.
     */
    protected function filteredRows(): array
    {
        return $this->teacher->listAll(
            trim($_GET['q'] ?? '') ?: null,
            trim($_GET['department'] ?? '') ?: null,
            trim($_GET['qualification'] ?? '') ?: null,
            trim($_GET['status'] ?? '') ?: null
        );
    }

    protected function sanitize(array $input): array
    {
        $fields = [
            'employee_id', 'first_name', 'last_name', 'gender', 'dob', 'department', 'teaching_level',
            'qualification', 'specialization', 'experience_years', 'phone', 'email',
            'address', 'salary', 'joining_date', 'status',
        ];
        $data = [];
        foreach ($fields as $f) {
            $data[$f] = trim((string) ($input[$f] ?? ''));
        }
        // Empty optional dates must be NULL, not '' (MySQL strict-mode DATE error).
        foreach (['dob', 'joining_date'] as $dateField) {
            $data[$dateField] = $data[$dateField] !== '' ? $data[$dateField] : null;
        }
        $data['gender'] = $data['gender'] !== '' ? $data['gender'] : null;
        return $data;
    }

    protected function uploadFile(string $field, string $subfolder, array $allowed): string|false
    {
        if (empty($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        $file = $_FILES[$field];
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            Flash::set('warning', 'Uploaded file exceeds the maximum size.');
            return false;
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            Flash::set('warning', 'Unsupported file type: ' . $ext);
            return false;
        }

        $dir = UPLOAD_PATH . '/' . $subfolder;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = $subfolder . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
            return false;
        }
        return 'uploads/' . $subfolder . '/' . $filename;
    }

    protected function renderError(string $message): void
    {
        http_response_code(404);
        $this->render('errors/404', ['message' => $message]);
    }
}
