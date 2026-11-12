<?php
/**
 * ============================================================
 *  AuthController
 *  Handles login, logout, forgot & reset password flows.
 * ============================================================
 */

declare(strict_types=1);

class AuthController extends Controller
{
    /**
     * Auth pages are public - skip the login middleware.
     */
    public function checkAuth(): bool
    {
        return true;
    }

    /**
     * Show the login form.
     */
    public function login(): void
    {
        if (Auth::check()) {
            $this->redirect(Auth::home());
        }
        $this->renderRaw('auth/login', [], 'layouts/auth_layout');
    }

    /**
     * Process the login submission.
     */
    public function authenticate(): void
    {
        CSRF::check();

        $username = trim($_POST['username'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $remember = isset($_POST['remember']) && $_POST['remember'] === '1';
        $portal   = ''; // connexion unique : tous les rôles utilisent la même page

        if ($username === '' || $password === '') {
            Flash::set('error', 'Username/email and password are required.');
            $this->redirect($this->loginUrl($portal));
        }

        // Status-specific message when the account exists but is not active.
        // (Auth::attempt only accepts active accounts, so handle the other
        // statuses here so the user sees a meaningful error.)
        $account = Database::getInstance()->fetch(
            'SELECT status FROM users WHERE username = ? OR email = ? LIMIT 1',
            [$username, $username]
        );
        if ($account) {
            if ($account['status'] === 'inactive') {
                Flash::set('error', 'Your account is currently inactive. Please contact the school administrator to activate your account.');
                $this->redirect($this->loginUrl($portal));
            }
            if ($account['status'] === 'suspended') {
                Flash::set('error', 'Your account has been suspended due to administrative reasons. Please contact the school administration for further information.');
                $this->redirect($this->loginUrl($portal));
            }
            if ($account['status'] === 'pending') {
                Flash::set('error', 'Your account is pending administrator approval. You will be able to log in once your account has been approved.');
                $this->redirect($this->loginUrl($portal));
            }
        }

        if (Auth::attempt($username, $password, $remember)) {
            CSRF::regenerate();

            ActivityLog::log('login', 'auth', 'User logged in');
            Flash::set('success', 'Welcome back, ' . Auth::name() . '!');
            $this->redirect(Auth::home());
        }

        Flash::set('error', 'Invalid email or password. Please check your credentials and try again.');
        $this->redirect($this->loginUrl($portal));
    }

    /**
     * Public website portal login links (auth/login?portal=...) and the
     * hidden portal field on the login form map to the roles allowed to use
     * that portal. No portal ('' or an unknown slug) means the normal,
     * unrestricted system login.
     */
    private function portalAllowsRole(string $portal, string $role): bool
    {
        if ($portal === '') {
            return true;
        }

        $allowed = [
            'student' => ['student'],
            'teacher' => ['teacher'],
            'parent'  => ['parent'],
            'staff'   => ['admin', 'super_admin'],
        ];

        if (!isset($allowed[$portal])) {
            return true;
        }

        return in_array($role, $allowed[$portal], true);
    }

    /**
     * Auth login redirect that keeps the selected portal in the URL so the
     * form and its hidden portal field survive failed attempts.
     */
    private function loginUrl(string $portal): string
    {
        return 'auth/login' . ($portal !== '' ? '?portal=' . urlencode($portal) : '');
    }

    /**
     * Show the "forgot password" form.
     */
    public function forgotPassword(): void
    {
        $this->renderRaw('auth/forgot-password', [], 'layouts/auth_layout');
    }

    /**
     * Send a password reset link.
     */
    public function sendResetLink(): void
    {
        CSRF::check();

        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::set('error', 'Please enter a valid email address.');
            $this->redirect('auth/forgot-password');
        }

        $user = (new User())->firstWhere('email', $email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600);

            Database::getInstance()->insert('password_resets', [
                'user_id'    => $user['id'],
                'email'      => $email,
                'token'      => hash('sha256', $token),
                'expires_at' => $expires,
            ]);

            // In a real deployment an email would be sent. For this system we
            // surface the reset link on screen (demo mode) and log it.
            $link = BASE_URL . '/auth/reset-password?token=' . $token;
            Flash::set('success', 'A reset link has been generated: ' . $link);
            ActivityLog::log('password_reset_request', 'auth', 'Reset link requested for ' . $email);
        } else {
            Flash::set('warning', 'If that email exists, a reset link has been sent.');
        }

        $this->redirect('auth/forgot-password');
    }

    /**
     * Show the reset password form.
     */
    public function resetPassword(): void
    {
        $token = (string) ($_GET['token'] ?? '');
        if ($token === '') {
            Flash::set('error', 'Missing reset token.');
            $this->redirect('auth/forgot-password');
        }
        $this->renderRaw('auth/reset-password', ['token' => $token], 'layouts/auth_layout');
    }

    /**
     * Process the password reset.
     */
    public function processReset(): void
    {
        CSRF::check();

        $token = (string) ($_POST['token'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirmation'] ?? '');

        if (mb_strlen($password) < MIN_PASSWORD_LENGTH) {
            Flash::set('error', 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.');
            $this->redirect('auth/reset-password?token=' . urlencode($token));
        }

        if ($password !== $confirm) {
            Flash::set('error', 'Passwords do not match.');
            $this->redirect('auth/reset-password?token=' . urlencode($token));
        }

        $record = Database::getInstance()->fetch(
            'SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW() LIMIT 1',
            [hash('sha256', $token)]
        );

        if (!$record) {
            Flash::set('error', 'This reset link is invalid or has expired.');
            $this->redirect('auth/forgot-password');
        }

        $user = (new User())->find((int) $record['user_id']);
        if ($user) {
            Database::getInstance()->update('users', ['password' => password_hash($password, PASSWORD_DEFAULT)], 'id = :pk', [':pk' => $user['id']]);
        }

        Database::getInstance()->update('password_resets', ['used' => 1], 'id = :pk', [':pk' => $record['id']]);

        ActivityLog::log('password_reset', 'auth', 'Password reset for user #' . ($user['id'] ?? '?'));
        Flash::set('success', 'Your password has been reset. Please log in.');
        $this->redirect('auth/login');
    }

    /**
     * Log the user out.
     */
    public function logout(): void
    {
        Auth::logout();
        Flash::set('info', 'You have been logged out.');
        $this->redirect('auth/login');
    }
}
