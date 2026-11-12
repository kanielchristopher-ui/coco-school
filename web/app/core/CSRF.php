<?php
/**
 * ============================================================
 *  CSRF
 *  Generates and validates CSRF tokens for every POST request.
 * ============================================================
 */

declare(strict_types=1);

class CSRF
{
    /**
     * Generate (or reuse) the CSRF token for the session.
     */
    public static function token(): string
    {
        if (!Session::has(CSRF_TOKEN_NAME)) {
            Session::set(CSRF_TOKEN_NAME, bin2hex(random_bytes(32)));
        }
        return Session::get(CSRF_TOKEN_NAME);
    }

    /**
     * Output a hidden input field with the CSRF token.
     */
    public static function field(): string
    {
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . self::token() . '">';
    }

    /**
     * Validate a submitted CSRF token.
     */
    public static function validate(?string $token = null): bool
    {
        $submitted = $token ?? ($_POST[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        $expected  = Session::get(CSRF_TOKEN_NAME);

        if (!is_string($submitted) || !is_string($expected)) {
            return false;
        }

        return hash_equals($expected, $submitted);
    }

    /**
     * Check the CSRF token on POST requests; abort on failure.
     */
    public static function check(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !self::validate()) {
            Flash::set('error', 'Invalid security token. Please try again.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/dashboard/index'));
            exit;
        }
    }

    /**
     * Re-issue a fresh token (after login, for example).
     */
    public static function regenerate(): void
    {
        Session::set(CSRF_TOKEN_NAME, bin2hex(random_bytes(32)));
    }
}
