<?php
/**
 * ============================================================
 *  View
 *  Simple template engine. Extracts data into scope, applies
 *  XSS escaping helper and renders the requested view inside
 *  an optional layout.
 * ============================================================
 */

declare(strict_types=1);

class View
{
    /**
     * Render a view file, optionally inside a layout.
     *
     * @param string      $view   view path relative to VIEW_PATH (no extension)
     * @param array       $data   variables extracted into the view
     * @param string|null $layout layout path relative to VIEW_PATH (no extension)
     */
    public function render(string $view, array $data = [], ?string $layout = null): void
    {
        // Make the view helper available inside templates.
        $viewPath = $this->resolve($view);

        if (!is_file($viewPath)) {
            throw new RuntimeException('View not found: ' . $view);
        }

        // Render the view content into a buffer.
        ob_start();
        extract($data, EXTR_SKIP);
        include $viewPath;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutPath = $this->resolve($layout);
        if (!is_file($layoutPath)) {
            throw new RuntimeException('Layout not found: ' . $layout);
        }

        extract($data, EXTR_SKIP);
        include $layoutPath;
    }

    /**
     * Resolve a view path to an absolute file path.
     */
    protected function resolve(string $view): string
    {
        return VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';
    }
}
