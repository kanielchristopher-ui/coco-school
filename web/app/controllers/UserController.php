<?php
/**
 * ============================================================
 *  UserController
 *  System users, roles and RBAC permission management.
 * ============================================================
 */

declare(strict_types=1);

class UserController extends Controller
{
    /**
     * List system users.
     */
    public function index(): void
    {
        Auth::requirePermission('users.manage');

        $search = trim($_GET['q'] ?? '');
        $users = (new User())->listAll($search ?: null);

        $this->render('users/index', [
            'users'  => $users,
            'search' => $search,
        ]);
    }

    /**
     * Show the create-user form.
     */
    public function create(): void
    {
        Auth::requirePermission('users.manage');
        $this->render('users/create', [
            'roles'    => $this->allRoles(),
            'students' => $this->linkableStudents(),
            'teachers' => $this->linkableTeachers(),
            'linked'   => ['student_id' => 0, 'teacher_id' => 0],
        ]);
    }

    /**
     * Create a user account.
     */
    public function store(): void
    {
        CSRF::check();
        Auth::requirePermission('users.manage');

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $roleId = (int) ($_POST['role_id'] ?? 0);
        $status = trim($_POST['status'] ?? 'active');
        $password = (string) ($_POST['password'] ?? '');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $roleId < 1) {
            Flash::set('error', 'Name, a valid email and a role are required.');
            $this->redirect('users/create');
        }
        if (mb_strlen($password) < MIN_PASSWORD_LENGTH) {
            Flash::set('error', 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.');
            $this->redirect('users/create');
        }
        if ((new User())->firstWhere('email', $email)) {
            Flash::set('error', 'A user with that email already exists.');
            $this->redirect('users/create');
        }

        // Generate a unique username from the email prefix.
        $base = strtolower(strtok($email, '@'));
        $username = $base;
        $n = 1;
        while ((new User())->firstWhere('username', $username)) {
            $username = $base . ++$n;
        }

        $parts = preg_split('/\s+/', $name, 2);

        $id = (new User())->create([
            'role_id'    => $roleId,
            'username'   => $username,
            'email'      => $email,
            'password'   => password_hash($password, PASSWORD_DEFAULT),
            'first_name' => $parts[0] ?? $name,
            'last_name'  => $parts[1] ?? '',
            'phone'      => $phone,
            'status'     => in_array($status, ['active', 'inactive', 'suspended', 'pending'], true) ? $status : 'active',
        ]);

        $this->linkProfileForRole($roleId, $id, $_POST);

        $this->logActivity('create', 'users', 'Created user #' . $id . ' (' . $email . ')');
        Flash::set('success', 'User created successfully.');
        $this->redirect('users/index');
    }

    /**
     * Show the edit-user form.
     */
    public function edit(int $id): void
    {
        Auth::requirePermission('users.manage');

        $user = (new User())->find($id);
        if (!$user) {
            Flash::set('error', 'User not found.');
            $this->redirect('users/index');
        }

        $studentLink = Database::getInstance()->fetch('SELECT id FROM students WHERE user_id = ?', [$id]);
        $teacherLink = Database::getInstance()->fetch('SELECT id FROM teachers WHERE user_id = ?', [$id]);

        $this->render('users/edit', [
            'user'     => $user,
            'roles'    => $this->allRoles(),
            'students' => $this->linkableStudents($id),
            'teachers' => $this->linkableTeachers($id),
            'linked'   => [
                'student_id' => $studentLink ? (int) $studentLink['id'] : 0,
                'teacher_id' => $teacherLink ? (int) $teacherLink['id'] : 0,
            ],
        ]);
    }

    /**
     * Update a user account.
     */
    public function update(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('users.manage');

        $user = (new User())->find($id);
        if (!$user) {
            Flash::set('error', 'User not found.');
            $this->redirect('users/index');
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $roleId = (int) ($_POST['role_id'] ?? 0);
        $status = trim($_POST['status'] ?? 'active');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $roleId < 1) {
            Flash::set('error', 'Name, a valid email and a role are required.');
            $this->redirect('users/edit/' . $id);
        }

        $duplicate = Database::getInstance()->fetch(
            'SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1',
            [$email, $id]
        );
        if ($duplicate) {
            Flash::set('error', 'Another user already has that email.');
            $this->redirect('users/edit/' . $id);
        }

        $parts = preg_split('/\s+/', $name, 2);

        $data = [
            'role_id'    => $roleId,
            'email'      => $email,
            'first_name' => $parts[0] ?? $name,
            'last_name'  => $parts[1] ?? '',
            'phone'      => $phone,
            'status'     => in_array($status, ['active', 'inactive', 'suspended', 'pending'], true) ? $status : 'active',
        ];

        $password = (string) ($_POST['password'] ?? '');
        if ($password !== '') {
            if (mb_strlen($password) < MIN_PASSWORD_LENGTH) {
                Flash::set('error', 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.');
                $this->redirect('users/edit/' . $id);
            }
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // Unlink any previous student/teacher profile before applying the new
        // role link. A student profile is kept when the role stays "student" and
        // no different profile was explicitly chosen, so a Student user can never
        // be left without a linked profile.
        $oldStudent = Database::getInstance()->fetch('SELECT id FROM students WHERE user_id = ?', [$id]);
        $oldTeacher = Database::getInstance()->fetch('SELECT id FROM teachers WHERE user_id = ?', [$id]);

        (new User())->update($id, $data);

        $roleSlug = Database::getInstance()->fetchColumn('SELECT slug FROM roles WHERE id = ?', [$roleId]);
        $newStudentId = (int) ($_POST['linked_student_id'] ?? 0);
        $newTeacherId = (int) ($_POST['linked_teacher_id'] ?? 0);

        if ($oldStudent) {
            $leavingStudent  = $roleSlug !== 'student';
            $switchingProfile = $newStudentId > 0 && (int) $oldStudent['id'] !== $newStudentId;
            if ($leavingStudent || $switchingProfile) {
                Database::getInstance()->update('students', ['user_id' => null], 'id = :pk', [':pk' => $oldStudent['id']]);
            }
        }
        if ($oldTeacher) {
            if ($roleSlug !== 'teacher' || (int) $oldTeacher['id'] !== $newTeacherId) {
                Database::getInstance()->update('teachers', ['user_id' => null], 'id = :pk', [':pk' => $oldTeacher['id']]);
            }
        }

        if ($roleSlug === 'student') {
            $this->linkProfileForRole($roleId, $id, $_POST);
        } elseif ($roleSlug === 'teacher' && $newTeacherId) {
            $this->linkProfileForRole($roleId, $id, $_POST);
        }

        $this->logActivity('update', 'users', 'Updated user #' . $id);
        Flash::set('success', 'User updated.');
        $this->redirect('users/index');
    }

    /**
     * Delete a user account (except your own).
     */
    public function delete(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('users.manage');

        if ($id === Auth::id()) {
            Flash::set('error', 'You cannot delete your own account.');
            $this->redirect('users/index');
        }

        (new User())->delete($id);
        $this->logActivity('delete', 'users', 'Deleted user #' . $id);
        Flash::set('success', 'User deleted.');
        $this->redirect('users/index');
    }

    /**
     * List roles with user / permission counts.
     */
    public function roles(): void
    {
        Auth::requirePermission('users.manage');

        $roles = Database::getInstance()->fetchAll(
            'SELECT r.*,
                    (SELECT COUNT(*) FROM users u WHERE u.role_id = r.id) AS user_count,
                    (SELECT COUNT(*) FROM role_permissions rp WHERE rp.role_id = r.id) AS permission_count
             FROM roles r ORDER BY r.name'
        );

        $this->render('users/roles', ['roles' => $roles]);
    }

    /**
     * Manage the permissions assigned to a role.
     */
    public function rolePermissions(int $id): void
    {
        Auth::requirePermission('users.manage');

        $role = Database::getInstance()->fetch('SELECT * FROM roles WHERE id = ?', [$id]);
        if (!$role) {
            Flash::set('error', 'Role not found.');
            $this->redirect('users/roles');
        }

        $permissions = Database::getInstance()->fetchAll('SELECT * FROM permissions ORDER BY module, name');
        $assigned = Database::getInstance()->fetchCol(
            'SELECT permission_id FROM role_permissions WHERE role_id = ?',
            [$id]
        );

        $grouped = [];
        foreach ($permissions as $p) {
            $module = $p['module'] ?: 'General';
            $grouped[$module][] = $p;
        }

        $this->render('users/role-permissions', [
            'role'     => $role,
            'grouped'  => $grouped,
            'assigned' => array_map('intval', $assigned),
        ]);
    }

    /**
     * Save the permission assignment for a role.
     */
    public function saveRolePermissions(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('users.manage');

        $role = Database::getInstance()->fetch('SELECT * FROM roles WHERE id = ?', [$id]);
        if (!$role) {
            Flash::set('error', 'Role not found.');
            $this->redirect('users/roles');
        }

        $permissionIds = array_map('intval', $_POST['permissions'] ?? []);

        Database::getInstance()->delete('role_permissions', 'role_id = ?', [$id]);
        foreach ($permissionIds as $permissionId) {
            if ($permissionId > 0) {
                Database::getInstance()->insert('role_permissions', [
                    'role_id'       => $id,
                    'permission_id' => $permissionId,
                ]);
            }
        }

        $this->logActivity('permissions', 'users', 'Updated permissions for role #' . $id);
        Flash::set('success', 'Role permissions updated.');
        $this->redirect('users/roles');
    }

    /**
     * All roles for dropdowns.
     */
    protected function allRoles(): array
    {
        return Database::getInstance()->fetchAll('SELECT * FROM roles ORDER BY name');
    }

    /**
     * Students available to link to a user account: currently unlinked, or
     * (when editing) the profile already linked to the given user.
     */
    protected function linkableStudents(int $exceptUserId = 0): array
    {
        return Database::getInstance()->fetchAll(
            'SELECT s.id, s.first_name, s.last_name, s.admission_no, c.name AS class_name
             FROM students s
             LEFT JOIN classes c ON c.id = s.class_id
             WHERE s.user_id IS NULL OR s.user_id = ?
             ORDER BY s.first_name, s.last_name',
            [$exceptUserId]
        );
    }

    /**
     * Teachers available to link to a user account: currently unlinked, or
     * (when editing) the profile already linked to the given user.
     */
    protected function linkableTeachers(int $exceptUserId = 0): array
    {
        return Database::getInstance()->fetchAll(
            'SELECT t.id, t.first_name, t.last_name, t.employee_id
             FROM teachers t
             WHERE t.user_id IS NULL OR t.user_id = ?
             ORDER BY t.first_name, t.last_name',
            [$exceptUserId]
        );
    }

    /**
     * Link a freshly created user to the student/teacher profile selected
     * in the form, based on the chosen role.
     */
    protected function linkProfileForRole(int $roleId, int $userId, array $input): void
    {
        $roleSlug = Database::getInstance()->fetchColumn('SELECT slug FROM roles WHERE id = ?', [$roleId]);

        if ($roleSlug === 'student') {
            $studentId = (int) ($input['linked_student_id'] ?? 0);
            $existing  = Database::getInstance()->fetch('SELECT id FROM students WHERE user_id = ? LIMIT 1', [$userId]);

            if ($studentId > 0) {
                // Explicit profile chosen: free any previous profile, then link it.
                if ($existing && (int) $existing['id'] !== $studentId) {
                    Database::getInstance()->update('students', ['user_id' => null], 'id = :pk', [':pk' => $existing['id']]);
                }
                Database::getInstance()->update('students', ['user_id' => $userId], 'id = :pk', [':pk' => $studentId]);
            } elseif (!$existing) {
                // No profile selected and none linked: create one automatically so
                // a Student user can never exist without a profile.
                (new Student())->ensureForUser($userId);
            }
        } elseif ($roleSlug === 'teacher') {
            $teacherId = (int) ($input['linked_teacher_id'] ?? 0);
            if ($teacherId > 0) {
                Database::getInstance()->update('teachers', ['user_id' => $userId], 'id = :pk', [':pk' => $teacherId]);
            }
        }
    }
}
