<?php
/**
 * ============================================================
 *  StudentController
 *  Full CRUD for student records including photo upload,
 *  ID card, exports (CSV / Excel) and search & pagination.
 * ============================================================
 */

declare(strict_types=1);

class StudentController extends Controller
{
    protected Student $student;
    protected ClassRoom $classRoom;

    public function __construct()
    {
        parent::__construct();
        $this->student = new Student();
        $this->classRoom = new ClassRoom();
    }

    /**
     * List students with search, filters, sorting and pagination.
     */
    public function index(): void
    {
        $isTeacher = Auth::role() === 'teacher';

        if ($isTeacher) {
            Auth::requirePermission('view_assigned_students');
            $scope = Auth::assignedClassIds();
        } else {
            Auth::requirePermission('students.manage');
            $scope = [];
        }

        $search    = trim($_GET['q'] ?? '');
        $classId   = (int) ($_GET['class_id'] ?? 0);
        $sectionId = (int) ($_GET['section_id'] ?? 0);
        $gender    = trim($_GET['gender'] ?? '');
        $status    = trim($_GET['status'] ?? '');
        $sort      = trim($_GET['sort'] ?? 'id');
        $dir       = strtolower(trim($_GET['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($isTeacher && $classId && !in_array($classId, $scope, true)) {
            $this->renderError('You can only view students in your assigned classes.');
            return;
        }

        $rows = $this->student->listAll(
            $search,
            $classId ?: null,
            $sectionId ?: null,
            $gender ?: null,
            $status ?: null,
            $sort,
            $dir,
            $isTeacher ? $scope : []
        );
        $total = count($rows);

        $page  = max(1, (int) ($_GET['page'] ?? 1));
        $limit = PAGINATION_LIMIT;
        $pagination = new Pagination($total, $page, $limit);
        $rows = array_slice($rows, $pagination->offset(), $limit);

        $classes = $isTeacher
            ? array_values(array_filter(
                $this->classRoom->all('name'),
                static fn(array $c): bool => in_array((int) $c['id'], $scope, true)
            ))
            : $this->classRoom->all('name');

        $this->render('students/index', [
            'rows'       => $rows,
            'classes'    => $classes,
            'sections'   => $classId ? $this->classRoom->sections($classId) : [],
            'pagination' => $pagination,
            'total'      => $total,
            'search'     => $search,
            'classId'    => $classId,
            'sectionId'  => $sectionId,
            'gender'     => $gender,
            'status'     => $status,
            'sort'       => $sort,
            'dir'        => $dir,
            'canManage'  => !$isTeacher,
        ]);
    }

    /**
     * Show the create student form.
     */
    public function create(): void
    {
        Auth::requirePermission('students.manage');
        $this->render('students/create', [
            'classes' => $this->classRoom->listAll(),
        ]);
    }

    /**
     * Store a new student.
     */
    public function store(): void
    {
        CSRF::check();
        Auth::requirePermission('students.manage');
        LicenseGate::guardStudentCreation((int) ($_POST['class_id'] ?? 0));   // plafond d'élèves du niveau (licence)

        $data = $this->sanitize($_POST);

        $validator = new Validator();
        $rules = [
            'first_name'  => 'required|max:60',
            'middle_name' => 'max:60',
            'last_name'   => 'max:60',
            'gender'      => 'in:male,female,other',
            'dob'         => 'date',
            'class_id'    => 'integer',
            'status'      => 'in:active,inactive,alumni,transferred',
            'guardian_name' => 'max:100',
            'guardian_phone' => 'phone',
            'email'       => 'email|max:120',
        ];

        if (!$validator->validate($data, $rules)) {
            Flash::set('error', $validator->errorsFlat());
            $this->redirect('students/create');
        }

        // Generate admission number (DB requires it; hidden from the UI).
        $data['admission_no'] = $this->student->nextAdmissionNo();
        $data['admission_date'] = $data['admission_date'] ?: date('Y-m-d');
        $data['status'] = $data['status'] ?: 'active';

        // Photo upload.
        $photo = $this->uploadImage('photo', 'students');
        if ($photo !== false) {
            $data['photo'] = $photo;
        }

        $db = Database::getInstance();
        $db->beginTransaction();
        $accountSummary = null;
        try {
            $id = $this->student->create($data);

            // Provision (or reuse) the student's linked login account.
            $accountEmail = trim((string) ($_POST['account_email'] ?? ''));
            if ($accountEmail === '') {
                $accountEmail = trim((string) ($data['email'] ?? ''));
            }
            if ($accountEmail !== '') {
                $accountSummary = $this->student->provisionUser($id, [
                    'email'            => $accountEmail,
                    'username'         => trim((string) ($_POST['account_username'] ?? '')),
                    'password'         => (string) ($_POST['account_password'] ?? ''),
                    'default_password' => DEFAULT_STUDENT_PASSWORD,
                ], 'student');
            }

            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            Flash::set('error', 'Could not create the student record. Please try again.');
            $this->redirect('students/create');
            return;
        }

        $this->logActivity('create', 'students', 'Created student #' . $id . ($accountSummary && $accountSummary['created'] ? ' with new login account' : ($accountSummary && $accountSummary['status'] === 'linked' ? ' linked to existing login' : '')));

        $message = 'Student created successfully.';
        if ($accountSummary) {
            if ($accountSummary['status'] === 'created') {
                $message .= ' Login account created — username "' . $accountSummary['username'] . '", password "' . $accountSummary['password'] . '".';
            } elseif ($accountSummary['status'] === 'linked') {
                $message .= ' Linked to existing login "' . $accountSummary['username'] . '".';
            } elseif ($accountSummary['status'] === 'role-conflict') {
                $message .= ' The email belongs to a non-student login, so no account was linked.';
            }
        }
        Flash::set('success', $message);
        $this->redirect('students/show/' . $id);
    }

    /**
     * Idempotent repair: link every student without a user account to an
     * existing account by email, or provision a new login for them.
     */
    public function repairLinks(): void
    {
        Auth::requirePermission('students.manage');

        $stats = (new Student())->repairUnlinked('student', DEFAULT_STUDENT_PASSWORD);
        $total = $stats['linked'] + $stats['created'];
        $this->logActivity('repair-links', 'students', 'Repaired ' . $total . ' student login link(s)');

        $message = 'Student login links repaired: ' . $stats['linked'] . ' linked to existing accounts, ' . $stats['created'] . ' new accounts created.';
        if ($stats['skipped'] > 0) {
            $message .= ' ' . $stats['skipped'] . ' skipped (no email available).';
        }
        Flash::set('success', $message);
        $this->redirect('students/index');
    }

    /**
     * Show a single student's profile.
     */
    public function show(int $id): void
    {
        $student = $this->student->findWithDetails($id);
        if (!$student) {
            $this->renderError('Student not found.');
            return;
        }

        if (Auth::role() === 'teacher') {
            Auth::requirePermission('view_assigned_students');
            if (!in_array((int) $student['class_id'], Auth::assignedClassIds(), true)) {
                $this->renderError('You can only view students in your assigned classes.');
                return;
            }
        } else {
            Auth::requirePermission('students.manage');
        }

        $db = Database::getInstance();
        $student['payments'] = (new Payment())->forStudent($id);
        $student['fees'] = $db->fetchAll(
            'SELECT f.*, ft.name AS fee_type_name FROM fees f
             LEFT JOIN fee_types ft ON ft.id = f.fee_type_id
             WHERE f.student_id = ? ORDER BY f.id DESC',
            [$id]
        );
        $student['attendance'] = $db->fetchAll(
            'SELECT status, COUNT(*) AS total FROM attendance WHERE student_id = ? GROUP BY status',
            [$id]
        );

        $this->render('students/show', ['student' => $student]);
    }

    /**
     * Show the edit form.
     */
    public function edit(int $id): void
    {
        Auth::requirePermission('students.manage');

        $student = $this->student->find($id);
        if (!$student) {
            $this->renderError('Student not found.');
            return;
        }

        $this->render('students/edit', [
            'student' => $student,
            'classes' => $this->classRoom->listAll(),
        ]);
    }

    /**
     * Update a student.
     */
    public function update(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('students.manage');

        $student = $this->student->find($id);
        if (!$student) {
            $this->renderError('Student not found.');
            return;
        }

        $data = $this->sanitize($_POST);

        $validator = new Validator();
        if (!$validator->validate($data, [
            'first_name' => 'required|max:60',
            'middle_name' => 'max:60',
            'gender'     => 'in:male,female,other',
            'dob'        => 'date',
            'email'      => 'email|max:120',
            'guardian_phone' => 'phone',
            'status'     => 'in:active,inactive,alumni,transferred',
        ])) {
            Flash::set('error', $validator->errorsFlat());
            $this->redirect('students/edit/' . $id);
        }

        // Remove keys that must not be updated through this form.
        unset($data['admission_no'], $data['roll_no']);

        $photo = $this->uploadImage('photo', 'students');
        if ($photo !== false) {
            $data['photo'] = $photo;
        }

        $this->student->update($id, $data);
        $this->logActivity('update', 'students', 'Updated student #' . $id);

        Flash::set('success', 'Student updated successfully.');
        $this->redirect('students/show/' . $id);
    }

    /**
     * Delete a student with a complete cascade cleanup, in a transaction.
     *
     * Student Management is the single place where a student's full data
     * removal is handled. Only records belonging to the selected student are
     * deleted; other students and unrelated users are never touched. The
     * linked user account is only removed when it is not shared with another
     * profile (teacher / staff / parent / other student).
     */
    public function destroy(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('students.manage');

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $student = $this->student->find($id);
            if (!$student) {
                $db->rollBack();
                Flash::set('error', 'Student not found.');
                $this->redirect('students/index');
                return;
            }

            $studentUserId = (int) ($student['user_id'] ?? 0);

            // Parent-student links.
            $db->delete('student_parents', 'student_id = ?', [$id]);

            // Legacy fee invoices, payments and discounts.
            $db->delete('payments', 'student_id = ?', [$id]);
            $db->delete('fees', 'student_id = ?', [$id]);
            $db->delete('fee_discounts', 'student_id = ?', [$id]);

            // Finance module records.
            $db->delete('student_fees', 'student_id = ?', [$id]);
            $db->delete('fee_collections', 'student_id = ?', [$id]);

            // Academic records.
            $db->delete('attendance', 'student_id = ?', [$id]);
            $db->delete('results', 'student_id = ?', [$id]);
            $db->delete('promotions', 'student_id = ?', [$id]);

            // Transport assignments.
            $db->delete('transport_assignments', 'student_id = ?', [$id]);

            // Linked student user account (notifications, password resets and
            // remember tokens are removed together with it via FK cascade).
            if ($studentUserId > 0) {
                $shared = (int) $db->fetchColumn(
                    'SELECT (SELECT COUNT(*) FROM students WHERE user_id = ? AND id <> ?)
                          + (SELECT COUNT(*) FROM teachers WHERE user_id = ?)
                          + (SELECT COUNT(*) FROM staff WHERE user_id = ?)
                          + (SELECT COUNT(*) FROM parents WHERE user_id = ?)',
                    [$studentUserId, $id, $studentUserId, $studentUserId, $studentUserId]
                );
                if ($shared === 0) {
                    $db->delete('users', 'id = ?', [$studentUserId]);
                }
            }

            // Student profile.
            $this->student->delete($id);

            $db->commit();
            $this->logActivity('delete', 'students', 'Deleted student #' . $id . ' with full cascade cleanup');
            Flash::set('success', 'Student and all related records (account, notifications, attendance, results, fees, payments, parent links) were deleted.');
            $this->redirect('students/index');
        } catch (Throwable $e) {
            $db->rollBack();
            Flash::set('error', 'Could not delete the student. Please try again.');
            $this->redirect('students/index');
        }
    }

    /**
     * Render the printable ID card for a student.
     */
    public function idCard(int $id): void
    {
        Auth::requirePermission('students.manage');

        $student = $this->student->findWithDetails($id);
        if (!$student) {
            $this->renderError('Student not found.');
            return;
        }

        $this->renderRaw('students/id_card', ['student' => $student]);
    }

    /**
     * Render printable ID cards for all filtered students (bulk print).
     */
    public function idCards(): void
    {
        Auth::requirePermission('students.manage');

        $rows = $this->filteredRows();
        $this->renderRaw('students/id_cards', ['rows' => $rows]);
    }

    /**
     * Export students to CSV (respects active filters).
     */
    public function exportCsv(): void
    {
        Auth::requirePermission('students.manage');

        $rows = $this->filteredRows();
        $headers = ['Official ID', 'Full Name', 'Class', 'Section', 'Gender', 'Phone', 'Status', 'DOB', 'Blood Group', 'City', 'State'];
        $out = array_map(fn($r) => [
            $r['official_id'],
            trim($r['first_name'] . ' ' . $r['middle_name'] . ' ' . $r['last_name']),
            $r['class_name'], $r['section_name'], $r['gender'], $r['phone'], $r['status'],
            $r['dob'], $r['blood_group'], $r['city'], $r['state'],
        ], $rows);

        Exporter::csv('students_' . date('Ymd'), $headers, $out);
    }

    /**
     * Export students to Excel (respects active filters).
     */
    public function exportExcel(): void
    {
        Auth::requirePermission('students.manage');

        $rows = $this->filteredRows();
        $headers = ['Official ID', 'Full Name', 'Class', 'Section', 'Gender', 'Phone', 'Status', 'DOB', 'Blood Group', 'City', 'State'];
        $out = array_map(fn($r) => [
            $r['official_id'],
            trim($r['first_name'] . ' ' . $r['middle_name'] . ' ' . $r['last_name']),
            $r['class_name'], $r['section_name'], $r['gender'], $r['phone'], $r['status'],
            $r['dob'], $r['blood_group'], $r['city'], $r['state'],
        ], $rows);

        Exporter::excel('students_' . date('Ymd'), $headers, $out);
    }

    /**
     * Render a printable (print-optimized) list for "Export PDF".
     */
    public function exportPdf(): void
    {
        Auth::requirePermission('students.manage');

        $rows = $this->filteredRows();
        $this->renderRaw('students/export_pdf', ['rows' => $rows]);
    }

    /**
     * Build the filtered row set shared by CSV / Excel / PDF exports.
     */
    protected function filteredRows(): array
    {
        return $this->student->listAll(
            trim($_GET['q'] ?? ''),
            (int) ($_GET['class_id'] ?? 0) ?: null,
            (int) ($_GET['section_id'] ?? 0) ?: null,
            trim($_GET['gender'] ?? '') ?: null,
            trim($_GET['status'] ?? '') ?: null
        );
    }

    /**
     * Sanitize incoming student form data.
     */
    protected function sanitize(array $input): array
    {
        $fields = [
            'first_name', 'middle_name', 'last_name', 'gender', 'dob',
            'birth_place', 'nationality', 'perm_number',
            'class_id', 'section_id', 'guardian_name', 'guardian_relation',
            'guardian_phone', 'guardian_email', 'address', 'city', 'state', 'zip',
            'phone', 'email', 'blood_group', 'medical_info', 'admission_date', 'status',
        ];

        $data = [];
        foreach ($fields as $f) {
            $data[$f] = trim((string) ($input[$f] ?? ''));
        }
        // Empty optional dates must be NULL, not '' (MySQL strict-mode DATE error).
        foreach (['dob', 'admission_date'] as $dateField) {
            $data[$dateField] = $data[$dateField] !== '' ? $data[$dateField] : null;
        }
        $data['gender'] = $data['gender'] !== '' ? $data['gender'] : null;
        $data['section_id'] = $data['section_id'] !== '' ? (int) $data['section_id'] : null;
        $data['class_id']   = $data['class_id'] !== '' ? (int) $data['class_id'] : null;

        return $data;
    }

    /**
     * Upload an image, returns the public path or false.
     */
    protected function uploadImage(string $field, string $subfolder): string|false
    {
        if (empty($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $file = $_FILES[$field];
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            Flash::set('warning', 'Uploaded image exceeds the maximum size.');
            return false;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_IMAGE_TYPES, true)) {
            Flash::set('warning', 'Unsupported image type: ' . $ext);
            return false;
        }

        $dir = UPLOAD_PATH . '/' . $subfolder;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = $subfolder . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return false;
        }

        return 'uploads/' . $subfolder . '/' . $filename;
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
