<?php
/**
 * ============================================================
 *  Auth
 *  Handles login, logout, hashed passwords, remember-me
 *  cookies and role-based access control (RBAC).
 * ============================================================
 */

declare(strict_types=1);

class Auth
{
    /**
     * Request-level cache of the current session's account-existence check.
     * Prevents repeating the user lookup on every Auth::check() call.
     */
    private static ?bool $sessionValid = null;

    /**
     * Attempt to authenticate a user with credentials.
     */
    public static function attempt(string $identifier, string $password, bool $remember = false): bool
    {
        $user = Database::getInstance()->fetch(
            'SELECT u.*, r.name AS role_name, r.slug AS role_slug
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE (u.username = ? OR u.email = ?) AND u.status = "active"
             LIMIT 1',
            [$identifier, $identifier]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        self::loginUser($user, $remember);
        return true;
    }

    /**
     * Set up the logged-in session for a user array.
     */
    public static function loginUser(array $user, bool $remember = false): void
    {
        Session::regenerate();

        $_SESSION['user_id']     = (int) $user['id'];
        $_SESSION['role_id']     = (int) $user['role_id'];
        $_SESSION['role_slug']   = $user['role_slug'];
        $_SESSION['user_name']   = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $_SESSION['user_email']  = $user['email'] ?? '';
        $_SESSION['user_photo']  = $user['photo'] ?? null;
        $_SESSION['logged_in']   = true;

        // Update last login timestamp.
        Database::getInstance()->update(
            'users',
            ['last_login' => date('Y-m-d H:i:s')],
            'id = :pk',
            [':pk' => $user['id']]
        );

        if ($remember) {
            self::createRememberToken((int) $user['id']);
        }
    }

    /**
     * Check if a user is authenticated.
     *
     * The account backing the session is validated against the database so a
     * session for a deleted user (any role - student, teacher, parent, staff)
     * is invalidated immediately: the remember token is removed, the session
     * is cleared and the user is forced back to the login page instead of
     * keeping a dead account logged in.
     */
    public static function check(): bool
    {
        $userId = Session::get('user_id');
        if (Session::get('logged_in', false) !== true || $userId === null) {
            return false;
        }

        if (self::$sessionValid === null) {
            self::$sessionValid = (bool) Database::getInstance()->fetch(
                'SELECT id FROM users WHERE id = ? LIMIT 1',
                [(int) $userId]
            );

            if (self::$sessionValid === false) {
                // The account no longer exists. Clear the remember-me token,
                // then start a fresh (empty) session so the flash message below
                // survives, mirroring the Session::checkTimeout() behaviour.
                self::deleteRememberToken();
                Session::regenerate(true);
                $_SESSION = [];
                $_SESSION['last_activity'] = time();
                Flash::set('info', 'Your account is no longer available. Please contact the administrator.');
            }
        }

        return self::$sessionValid;
    }

    /**
     * Get the current user id (0 if not authenticated).
     */
    public static function id(): int
    {
        return (int) Session::get('user_id', 0);
    }

    /**
     * Get the current user's role slug.
     */
    public static function role(): string
    {
        return (string) Session::get('role_slug', '');
    }

    /**
     * Get the current user's role id.
     */
    public static function roleId(): int
    {
        return (int) Session::get('role_id', 0);
    }

    /**
     * Get the current user's full name.
     */
    public static function name(): string
    {
        return (string) Session::get('user_name', 'User');
    }

    /**
     * Get the current user's email.
     */
    public static function email(): string
    {
        return (string) Session::get('user_email', '');
    }

    /**
     * The landing route for the currently logged-in user, based on role.
     * Teachers and students land on the (role-aware) dashboard, parents on
     * their portal, everyone else on the admin dashboard.
     */
    public static function home(): string
    {
        return match (self::role()) {
            'parent'  => 'parent/dashboard',
            'teacher' => 'dashboard/index',
            'student' => 'dashboard/index',
            default   => 'dashboard/index',
        };
    }

    /**
     * Check whether the current user has a given permission slug.
     */
    public static function can(string $permissionSlug): bool
    {
        if (!self::check()) {
            return false;
        }

        // Super Admin bypasses all permission checks.
        if (self::role() === 'super_admin') {
            return true;
        }

        $db = Database::getInstance();
        return $db->count(
            'SELECT COUNT(*)
             FROM role_permissions rp
             INNER JOIN permissions p ON p.id = rp.permission_id
             WHERE rp.role_id = ? AND p.slug = ?',
            [self::roleId(), $permissionSlug]
        ) > 0;
    }

    /**
     * Role-based gate: restrict access to a set of allowed roles.
     */
    public static function gate(array $allowedRoles): bool
    {
        if (self::role() === 'super_admin') {
            return true;
        }
        return in_array(self::role(), $allowedRoles, true);
    }

    /**
     * Teachers.id of the currently logged-in user's linked teacher profile
     * (0 when no profile is linked or the user is not a teacher).
     */
    public static function teacherProfileId(): int
    {
        $row = Database::getInstance()->fetch('SELECT id FROM teachers WHERE user_id = ? LIMIT 1', [self::id()]);
        return $row ? (int) $row['id'] : 0;
    }

    /**
     * Students.id of the currently logged-in user's linked student profile
     * (0 when no profile is linked or the user is not a student).
     */
    public static function studentProfileId(): int
    {
        $row = Database::getInstance()->fetch('SELECT id FROM students WHERE user_id = ? LIMIT 1', [self::id()]);
        return $row ? (int) $row['id'] : 0;
    }

    /**
     * Class ids assigned to the currently logged-in teacher: classes where
     * they are the class teacher or where they teach a subject.
     */
    public static function assignedClassIds(): array
    {
        $teacherId = self::teacherProfileId();
        if ($teacherId < 1) {
            return [];
        }
        return array_map('intval', Database::getInstance()->fetchCol(
            'SELECT DISTINCT c.id
             FROM classes c
             LEFT JOIN subjects su ON su.class_id = c.id AND su.teacher_id = ?
             WHERE c.class_teacher_id = ? OR su.teacher_id = ?',
            [$teacherId, $teacherId, $teacherId]
        ));
    }

    /**
     * Subject ids assigned to the currently logged-in teacher.
     */
    public static function assignedSubjectIds(): array
    {
        $teacherId = self::teacherProfileId();
        if ($teacherId < 1) {
            return [];
        }
        return array_map('intval', Database::getInstance()->fetchCol(
            'SELECT id FROM subjects WHERE teacher_id = ?',
            [$teacherId]
        ));
    }

    /**
     * Require one of the given roles; show 403 otherwise.
     * Super Admin always passes.
     */
    public static function requireRole(array $allowedRoles): void
    {
        if (self::role() === 'super_admin' || in_array(self::role(), $allowedRoles, true)) {
            return;
        }
        http_response_code(403);
        $view = new View();
        $view->render('errors/403', ['message' => 'You do not have permission to access this page.'], 'layouts/app_layout');
        exit;
    }

    /**
     * Require authentication; redirect to login otherwise.
     */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            Flash::set('warning', 'Please log in to continue.');
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
    }

    /**
     * Require a specific permission; show 403 otherwise.
     */
    public static function requirePermission(string $permissionSlug): void
    {
        if (!self::can($permissionSlug)) {
            http_response_code(403);
            $view = new View();
            $view->render('errors/403', ['message' => 'You do not have permission to access this page.'], 'layouts/app_layout');
            exit;
        }
    }

    /**
     * Require any one of the given permissions; show 403 otherwise.
     * Used where a route is shared by a role using a granular permission
     * (e.g. teacher: manage_own_exams) and management roles using the
     * classic module permission (e.g. admin: exams.manage).
     * Super Admin always passes.
     */
    public static function requireAny(array $permissionSlugs): void
    {
        foreach ($permissionSlugs as $slug) {
            if (self::can($slug)) {
                return;
            }
        }
        http_response_code(403);
        $view = new View();
        $view->render('errors/403', ['message' => 'You do not have permission to access this page.'], 'layouts/app_layout');
        exit;
    }

    /**
     * Log the current user out.
     */
    public static function logout(): void
    {
        self::deleteRememberToken();
        Session::destroy();
    }

    /**
     * Create a remember-me token (selector + hashed verifier cookie).
     */
    protected static function createRememberToken(int $userId): void
    {
        $selector = bin2hex(random_bytes(12));
        $token    = bin2hex(random_bytes(32));
        $hash     = hash('sha256', $token);
        $expires  = date('Y-m-d H:i:s', time() + (REMEMBER_DAYS * 86400));

        Database::getInstance()->delete('remember_tokens', 'user_id = ?', [$userId]);
        Database::getInstance()->insert('remember_tokens', [
            'user_id'    => $userId,
            'selector'   => $selector,
            'token_hash' => $hash,
            'expires_at' => $expires,
        ]);

        $cookie = $selector . ':' . $token;
        setcookie('remember_me', $cookie, time() + (REMEMBER_DAYS * 86400), '/', '', !empty($_SERVER['HTTPS']), true);
    }

    /**
     * Try to log a user in from the remember-me cookie.
     */
    public static function loginFromRememberCookie(): void
    {
        if (self::check()) {
            return;
        }

        $cookie = $_COOKIE['remember_me'] ?? '';
        if ($cookie === '' || !str_contains($cookie, ':')) {
            return;
        }

        [$selector, $token] = explode(':', $cookie, 2);

        $record = Database::getInstance()->fetch(
            'SELECT * FROM remember_tokens WHERE selector = ? AND expires_at > NOW() LIMIT 1',
            [$selector]
        );

        if (!$record || !hash_equals($record['token_hash'], hash('sha256', $token))) {
            self::deleteRememberToken();
            return;
        }

        $user = Database::getInstance()->fetch(
            'SELECT u.*, r.name AS role_name, r.slug AS role_slug
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.id = ? AND u.status = "active" LIMIT 1',
            [$record['user_id']]
        );

        if ($user) {
            self::loginUser($user, true);
        }
    }

    /**
     * Remove the current remember-me cookie and record.
     */
    protected static function deleteRememberToken(): void
    {
        if (isset($_COOKIE['remember_me'])) {
            setcookie('remember_me', '', time() - 3600, '/');
        }
        // Best-effort: delete records for the current user.
        $userId = self::id();
        if ($userId > 0) {
            Database::getInstance()->delete('remember_tokens', 'user_id = ?', [$userId]);
        }
    }
}
