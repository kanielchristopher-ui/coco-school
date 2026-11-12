<?php
/**
 * Student model.
 */
class Student extends Model
{
    protected string $table = 'students';

    /**
     * Create a student record. The official ID (STD-0001...) is generated
     * automatically unless one was explicitly provided.
     */
    public function create(array $data): int
    {
        if (!isset($data['official_id']) || $data['official_id'] === '') {
            $data['official_id'] = OfficialId::next('ELV');
        }
        return parent::create($data);
    }

    /**
     * List students with class/section names, filters and sorting.
     *
     * @param string|null $search    free-text (name)
     * @param int|null    $classId   class filter
     * @param int|null    $sectionId section filter
     * @param string|null $gender    gender filter (male / female / other)
     * @param string|null $status    status filter (active / inactive / ...)
     * @param string      $sort      sort column key (id, name, class, gender, phone, status, dob, city, state)
     * @param string      $dir       sort direction (asc / desc)
     * @param int[]       $classScope restrict results to these class ids (teachers)
     */
    public function listAll(
        ?string $search = null,
        ?int $classId = null,
        ?int $sectionId = null,
        ?string $gender = null,
        ?string $status = null,
        string $sort = 'id',
        string $dir = 'desc',
        array $classScope = []
    ): array {
        $sql = 'SELECT s.*, c.name AS class_name, sec.name AS section_name
                FROM students s
                LEFT JOIN classes c ON c.id = s.class_id
                LEFT JOIN sections sec ON sec.id = s.section_id
                WHERE 1 = 1';
        $params = [];

        if ($classScope !== []) {
            $in = implode(',', array_fill(0, count($classScope), '?'));
            $sql .= ' AND s.class_id IN (' . $in . ')';
            $params = array_merge($params, array_values($classScope));
        }
        if ($search) {
            $sql .= ' AND (s.first_name LIKE ? OR s.middle_name LIKE ? OR s.last_name LIKE ?)';
            $like = '%' . $search . '%';
            $params = array_merge($params, [$like, $like, $like]);
        }
        if ($classId) {
            $sql .= ' AND s.class_id = ?';
            $params[] = $classId;
        }
        if ($sectionId) {
            $sql .= ' AND s.section_id = ?';
            $params[] = $sectionId;
        }
        if ($gender) {
            $sql .= ' AND s.gender = ?';
            $params[] = $gender;
        }
        if ($status) {
            $sql .= ' AND s.status = ?';
            $params[] = $status;
        }

        $sortColumns = [
            'id'     => 's.id',
            'name'   => 's.first_name',
            'class'  => 'c.name',
            'gender' => 's.gender',
            'phone'  => 's.phone',
            'status' => 's.status',
            'dob'    => 's.dob',
            'city'   => 's.city',
            'state'  => 's.state',
        ];
        $column = $sortColumns[$sort] ?? 's.id';
        $direction = strtolower($dir) === 'asc' ? 'ASC' : 'DESC';

        $sql .= ' ORDER BY ' . $column . ' ' . $direction . ', s.id DESC';
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Find a student with class/section names.
     */
    public function findWithDetails(int $id): ?array
    {
        return $this->db->fetch(
            'SELECT s.*, c.name AS class_name, sec.name AS section_name, c.id AS class_id
             FROM students s
             LEFT JOIN classes c ON c.id = s.class_id
             LEFT JOIN sections sec ON sec.id = s.section_id
             WHERE s.id = ?',
            [$id]
        );
    }

    /**
     * Ensure a student profile exists for a user account. Returns the id of the
     * already-linked profile. When none is linked, an existing unlinked profile
     * that clearly matches the user (same email, or same phone + full name) is
     * linked instead of creating a duplicate. Otherwise a fresh profile is
     * created from the user's own account details, so every Student user has a
     * profile. Returns 0 only when the user does not exist.
     */
    public function ensureForUser(int $userId): int
    {
        $existing = $this->db->fetch('SELECT id FROM students WHERE user_id = ? LIMIT 1', [$userId]);
        if ($existing) {
            return (int) $existing['id'];
        }

        $user = $this->db->fetch(
            'SELECT username, first_name, last_name, email, phone FROM users WHERE id = ? LIMIT 1',
            [$userId]
        );
        if (!$user) {
            return 0;
        }

        $firstName = trim((string) ($user['first_name'] ?? ''));
        $lastName  = trim((string) ($user['last_name'] ?? ''));
        $email     = trim((string) ($user['email'] ?? ''));
        $phone     = trim((string) ($user['phone'] ?? ''));

        $profile = null;
        if ($email !== '') {
            $profile = $this->db->fetch(
                'SELECT id FROM students WHERE user_id IS NULL AND email = ? LIMIT 1',
                [$email]
            );
        }
        if (!$profile && $phone !== '' && $firstName !== '' && $lastName !== '') {
            $profile = $this->db->fetch(
                'SELECT id FROM students WHERE user_id IS NULL AND phone = ? AND first_name = ? AND last_name = ? LIMIT 1',
                [$phone, $firstName, $lastName]
            );
        }
        if ($profile) {
            $this->db->update('students', ['user_id' => $userId], 'id = :pk', [':pk' => $profile['id']]);
            return (int) $profile['id'];
        }

        if ($firstName === '') {
            $parts = preg_split('/[._\-\s]+/', trim((string) ($user['username'] ?? '')), 2);
            $firstName = ucfirst((string) ($parts[0] ?? 'Student'));
            $lastName  = ucfirst((string) ($parts[1] ?? ''));
        }

        return $this->create([
            'user_id'        => $userId,
            'admission_no'   => $this->nextAdmissionNo(),
            'first_name'     => $firstName,
            'last_name'      => $lastName,
            'gender'         => null,
            'email'          => $email !== '' ? $email : null,
            'phone'          => $phone !== '' ? $phone : null,
            'status'         => 'active',
            'admission_date' => date('Y-m-d'),
        ]);
    }

    /**
     * Provision (or reuse) the linked Student user account for a student
     * profile and set students.user_id.
     *
     * Returns a summary array:
     *   ['user_id' => int, 'created' => bool, 'username' => string,
     *    'password' => string, 'status' => 'created'|'linked'|'role-conflict'|'no-email']
     *
     * - 'created'      a brand-new Student user was provisioned
     * - 'linked'       an existing Student user with the same email was reused
     * - 'role-conflict' the email already belongs to a non-student user
     * - 'no-email'     no usable email was available
     */
    public function provisionUser(int $studentId, array $account = [], string $roleSlug = 'student'): array
    {
        $profile = $this->find($studentId);
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
            // Free any other student linked to the same account, then link this one.
            $this->db->update('students', ['user_id' => null], 'user_id = ? AND id <> ?', [$userId, $studentId]);
            $this->db->update('students', ['user_id' => $userId], 'id = :pk', [':pk' => $studentId]);
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
            $password = (string) ($account['default_password'] ?? DEFAULT_STUDENT_PASSWORD);
        }

        $user = new User();
        $username = $user->uniqueUsername($username);
        $userId = $user->create([
            'role_id'    => $roleId,
            'username'   => $username,
            'email'      => $email,
            'password'   => password_hash($password, PASSWORD_DEFAULT),
            'first_name' => (string) ($profile['first_name'] ?? ''),
            'last_name'  => trim(trim((string) ($profile['middle_name'] ?? '')) . ' ' . trim((string) ($profile['last_name'] ?? ''))),
            'status'     => 'active',
        ]);

        $this->db->update('students', ['user_id' => $userId], 'id = :pk', [':pk' => $studentId]);
        return ['user_id' => $userId, 'created' => true, 'username' => $username, 'password' => $password, 'status' => 'created'];
    }

    /**
     * Link every unlinked student profile to a user account: reuse an existing
     * account by email, or provision a new one. Idempotent. Returns
     * ['linked' => int, 'created' => int, 'skipped' => int].
     */
    public function repairUnlinked(string $roleSlug = 'student', string $defaultPassword = DEFAULT_STUDENT_PASSWORD): array
    {
        $stats = ['linked' => 0, 'created' => 0, 'skipped' => 0];
        $rows = $this->db->fetchAll('SELECT id, email FROM students WHERE user_id IS NULL ORDER BY id');
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
     * Generate the next admission number.
     */
    public function nextAdmissionNo(): string    {
        $year = date('Y');
        $prefix = 'ADM-' . $year . '-';
        $last = $this->db->fetch(
            "SELECT admission_no FROM students WHERE admission_no LIKE ? ORDER BY admission_no DESC LIMIT 1",
            [$prefix . '%']
        );
        $next = 1;
        if ($last) {
            $num = (int) substr($last['admission_no'], strlen($prefix));
            $next = $num + 1;
        }
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Students belonging to a parent account (via student_parents link).
     */
    public function forParent(int $parentUserId): array
    {
        return $this->db->fetchAll(
            'SELECT s.*, c.name AS class_name, sec.name AS section_name
             FROM students s
             INNER JOIN student_parents sp ON sp.student_id = s.id
             INNER JOIN parents p ON p.id = sp.parent_id
             LEFT JOIN classes c ON c.id = s.class_id
             LEFT JOIN sections sec ON sec.id = s.section_id
             WHERE p.user_id = ?',
            [$parentUserId]
        );
    }

    /**
     * Students of a given teacher (via class teacher or subject assignment).
     */
    public function forTeacher(int $teacherId): array
    {
        return $this->db->fetchAll(
            'SELECT s.*, c.name AS class_name, sec.name AS section_name
             FROM students s
             LEFT JOIN classes c ON c.id = s.class_id
             LEFT JOIN sections sec ON sec.id = s.section_id
             WHERE s.class_id IN (
                SELECT c2.id FROM classes c2 WHERE c2.class_teacher_id = ?
                UNION
                SELECT DISTINCT sub.class_id FROM subjects sub WHERE sub.teacher_id = ?
             )',
            [$teacherId, $teacherId]
        );
    }

    /**
     * Check whether a student is linked to a parent user account.
     */
    public function isLinkedToParent(int $studentId, int $parentUserId): bool
    {
        return $this->db->count(
            'SELECT COUNT(*)
             FROM student_parents sp
             INNER JOIN parents p ON p.id = sp.parent_id
             WHERE sp.student_id = ? AND p.user_id = ?',
            [$studentId, $parentUserId]
        ) > 0;
    }
}