<?php
/**
 * ============================================================
 *  Session
 *  Secure session handling with idle timeout and regeneration
 *  on privilege changes.
 * ============================================================
 */

declare(strict_types=1);

class Session
{
    /**
     * Start the session with hardened settings.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');

        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            ini_set('session.cookie_secure', '1');
        }

        session_name(SESSION_NAME);
        session_start();

        self::checkTimeout();
    }

    /**
     * Enforce idle session timeout.
     */
    public static function checkTimeout(): void
    {
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
            return;
        }

        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            // Expire the session (fresh ID, no user data) but keep it writable so
            // the flash message below is actually persisted for the login page.
            session_regenerate_id(true);
            $_SESSION = [];
            $_SESSION['last_activity'] = time();
            Flash::set('warning', 'Your session expired. Please log in again.');
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        // Refresh the activity timestamp on each request.
        $_SESSION['last_activity'] = time();
    }

    /**
     * Store a value in the session.
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a value from the session.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Remove a value from the session.
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Check whether a key exists.
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Regenerate the session id (call on login / logout).
     */
    public static function regenerate(bool $deleteOld = true): void
    {
        session_regenerate_id($deleteOld);
    }

    /**
     * Destroy the session completely.
     */
    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }
}
