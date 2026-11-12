<?php
/**
 * ============================================================
 *  Base Controller
 *  All controllers extend this class. It provides shared
 *  helpers for rendering views, redirects and JSON responses.
 * ============================================================
 */

declare(strict_types=1);

class Controller
{
    protected View $view;

    public function __construct()
    {
        $this->view = new View();
    }

    /**
     * Render a view using the default application layout.
     */
    protected function render(string $view, array $data = []): void
    {
        $this->view->render($view, $data, 'layouts/app_layout');
    }

    /**
     * Render a view without a layout (e.g. auth pages, print pages).
     */
    protected function renderRaw(string $view, array $data = [], ?string $layout = null): void
    {
        $this->view->render($view, $data, $layout);
    }

    /**
     * Redirect to a named route.
     */
    protected function redirect(string $url): void
    {
        $url = (str_starts_with($url, 'http')) ? $url : BASE_URL . '/' . ltrim($url, '/');
        header('Location: ' . $url);
        exit;
    }

    /**
     * Redirect back to the previous page.
     */
    protected function redirectBack(string $fallback = 'dashboard/index'): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        $this->redirect($referer ? $referer : $fallback);
    }

    /**
     * Return a JSON response.
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Set a flash message.
     */
    protected function flash(string $type, string $message): void
    {
        Flash::set($type, $message);
    }

    /**
     * Middleware invoked by the Router before every action.
     * Override in controllers that must be publicly accessible.
     */
    public function checkAuth(): bool
    {
        Auth::requireLogin();
        CSRF::check();
        return true;
    }

    /**
     * Get the currently logged-in user id.
     */
    protected function userId(): int
    {
        return Auth::id();
    }

    /**
     * Log an activity entry.
     */
    protected function logActivity(string $action, string $module, string $description = ''): void
    {
        ActivityLog::log($action, $module, $description);
    }
}
