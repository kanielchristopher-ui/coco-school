<?php
/**
 * Teacher model.
 */
class Teacher extends Model
{
    protected string $table = 'teachers';

    /**
     * Create a teacher record. The official ID (TCH-0001...) is generated
     * automatically unless one was explicitly provided.
     */
    public function create(array $data): int
    {
        if (!isset($data['official_id']) || $data['official_id'] === '') {
            $data['official_id'] = OfficialId::next('ENS');
        }
        return parent::create($data);
    }

    /**
     * List teachers with filters and sorting.
     *
     * @param string|null $search        free-text (name / department / qualification)
     * @param string|null $department    department filter
     * @param string|null $qualification qualification filter
     * @param string|null $status        status filter (active / inactive / left)
     * @param string      $sort          sort column key (id, name, department, qualification, phone, salary, status)
     * @param string      $dir           sort direction (asc / desc)
     */
    public function listAll(
        ?string $search = null,
        ?string $department = null,
        ?string $qualification = null,
        ?string $status = null,
        string $sort = 'id',
        string $dir = 'asc'
    ): array {
        $sql = 'SELECT * FROM teachers WHERE 1 = 1';
        $params = [];

        if ($search) {
            $sql .= ' AND (first_name LIKE ? OR last_name LIKE ? OR department LIKE ? OR qualification LIKE ?)';
            $like = '%' . $search . '%';
            $params = array_merge($params, [$like, $like, $like, $like]);
        }
        if ($department) {
            $sql .= ' AND department = ?';
            $params[] = $department;
        }
        if ($qualification) {
            $sql .= ' AND qualification = ?';
            $params[] = $qualification;
        }
        if ($status) {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }

        $sortColumns = [
            'id'            => 'id',
            'name'          => 'first_name',
            'department'    => 'department',
            'qualification' => 'qualification',
            'phone'         => 'phone',
            'salary'        => 'salary',
            'status'        => 'status',
        ];
        $column = $sortColumns[$sort] ?? 'id';
        $direction = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';

        $sql .= ' ORDER BY ' . $column . ' ' . $direction . ', id ASC';
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Provision (or reuse) the linked Teacher user account for a teacher
     * profile and set teachers.user_id.
     *
     * Returns a summary array:
     *   ['user_id' => int, 'created' => bool, 'username' => string,
     *    'password' => string, 'status' => 'created'|'linked'|'role-conflict'|'no-email']
     *
     * - 'created'       a brand-new Teacher user was provisioned
     * - 'linked'        an existing Teacher user with the same email was reused
     * - 'role-conflict' the email already belongs to a non-teacher user
     * - 'no-email'      no usable email was available
     */
    public function provisionUser(int $teacherId, array $account = [], string $roleSlug = 'teacher'): array
    {
        $profile = $this->find($teacherId);
        if (!$profile) {
            return ['user_id' => 0, 'created' => false, 'username' => '', 'password' => '', 'status' => 'no-email'];
        }

        $email = trim((string) ($account['email'] ?? ''));
        if ($email === '') {
            $email = trim((string) ($profile['email'] ?? ''));
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['user_id' => 0, 'created' => false, 'username' => '', 'password' => '', 'status' => 'no-email'];
        }

        $existing = $this->db->fetch(
            'SELECT u.*, r.slug AS role_slug FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE u.email = ? LIMIT 1',
            [$email]
        );
        if ($existing) {
            if ((string) ($existing['role_slug'] ?? '') !== $roleSlug) {
                return ['user_id' => 0, 'created' => false, 'username' => '', 'password' => '', 'status' => 'role-conflict'];
            }
            $userId = (int) $existing['id'];
            // Free any other teacher linked to the same account, then link this one.
            $this->db->update('teachers', ['user_id' => null], 'user_id = ? AND id <> ?', [$userId, $teacherId]);
            $this->db->update('teachers', ['user_id' => $userId], 'id = :pk', [':pk' => $teacherId]);
            return ['user_id' => $userId, 'created' => false, 'username' => (string) $existing['username'], 'password' => '', 'status' => 'linked'];
        }

        $roleId = (int) $this->db->fetchColumn('SELECT id FROM roles WHERE slug = ?', [$roleSlug]);
        if ($roleId < 1) {
            return ['user_id' => 0, 'created' => false, 'username' => '', 'password' => '', 'status' => 'no-email'];
        }

        $username = trim((string) ($account['username'] ?? ''));
        if ($username === '') {
            $username = strtolower((string) strtok($email, '@'));
        }

        $password = (string) ($account['password'] ?? '');
        if ($password === '') {
            $password = (string) ($account['default_password'] ?? DEFAULT_TEACHER_PASSWORD);
        }

        $user = new User();
        $username = $user->uniqueUsername($username);
        $userId = $user->create([
            'role_id'    => $roleId,
            'username'   => $username,
            'email'      => $email,
            'password'   => password_hash($password, PASSWORD_DEFAULT),
            'first_name' => (string) ($profile['first_name'] ?? ''),
            'last_name'  => trim((string) ($profile['last_name'] ?? '')),
            'status'     => 'active',
        ]);

        $this->db->update('teachers', ['user_id' => $userId], 'id = :pk', [':pk' => $teacherId]);
        return ['user_id' => $userId, 'created' => true, 'username' => $username, 'password' => $password, 'status' => 'created'];
    }

    /**
     * Link every unlinked teacher profile to a user account: reuse an existing
     * account by email, or provision a new one. Idempotent. Returns
     * ['linked' => int, 'created' => int, 'skipped' => int].
     */
    public function repairUnlinked(string $roleSlug = 'teacher', string $defaultPassword = DEFAULT_TEACHER_PASSWORD): array
    {
        $stats = ['linked' => 0, 'created' => 0, 'skipped' => 0];
        $rows = $this->db->fetchAll('SELECT id, email FROM teachers WHERE user_id IS NULL ORDER BY id');
        foreach ($rows as $row) {
            $result = $this->provisionUser((int) $row['id'], [
                'email'            => (string) ($row['email'] ?? ''),
                'username'         => '',
                'password'         => '',
                'default_password' => $defaultPassword,
            ], $roleSlug);
            if ($result['user_id'] > 0) {
                $stats[$result['created'] ? 'created' : 'linked']++;
            } else {
                $stats['skipped']++;
            }
        }
        return $stats;
    }

    /**
     * Generate the next employee id.
     */
    public function nextEmployeeId(): string
    {
        $last = $this->db->fetch(
            'SELECT employee_id FROM teachers ORDER BY employee_id DESC LIMIT 1'
        );
        $next = 1;
        if ($last) {
            $num = (int) substr($last['employee_id'], 4);
            $next = $num + 1;
        }
        return 'TCH-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * All distinct departments.
     */
    public function departments(): array
    {
        return $this->db->fetchCol('SELECT DISTINCT department FROM teachers WHERE department IS NOT NULL AND department != ""');
    }

    /**
     * All distinct qualifications.
     */
    public function qualifications(): array
    {
        return $this->db->fetchCol('SELECT DISTINCT qualification FROM teachers WHERE qualification IS NOT NULL AND qualification != ""');
    }
}
