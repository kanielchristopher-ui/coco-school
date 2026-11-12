<?php
/**
 * ============================================================
 *  Router
 *  Parses the request URL into Controller / Method / Params
 *  and dispatches to the matching controller action.
 *
 *  URL pattern : index.php?url=controller/method/param1/param2
 *  Also works with clean URLs when mod_rewrite is enabled.
 * ============================================================
 */

// NOTE: no strict_types here on purpose. URL segments arrive as strings
// and are passed to typed controller methods (e.g. show(int $id)); the
// weak-mode call lets PHP coerce "1" -> 1. Controllers keep strict_types.

class Router
{
    /**
     * Default controller and method.
     */
    protected string $defaultController = 'DashboardController';
    protected string $defaultMethod     = 'index';

    /**
     * Dispatch the incoming request.
     */
    public function dispatch(): void
    {
        $url = $_GET['url'] ?? '';

        // Clean-URL fallback: when mod_rewrite forwards a request to index.php
        // the URL path is NOT available as ?url=, so derive it from the request
        // URI and strip the directory the app is served from (usually .../public).
        if ($url === '') {
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

            $prefixes = [];
            $scriptDir = rtrim((string) dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
            if ($scriptDir !== '' && $scriptDir !== '/' && $scriptDir !== '.') {
                $prefixes[] = $scriptDir;
            }
            $basePath = rtrim((string) parse_url(BASE_URL, PHP_URL_PATH), '/');
            if ($basePath !== '' && $basePath !== '/') {
                $prefixes[] = $basePath;
            }

            foreach ($prefixes as $prefix) {
                if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                    $path = substr($path, strlen($prefix));
                    break;
                }
            }

            $url = trim($path, '/');
        }

        // Strip any leading slash and split.
        $url = trim($url, '/');
        $parts = $url === '' ? [] : explode('/', $url);

        // Resolve controller name. URLs use plural segments (students, classes,
        // messages...) while the controller files on disk are named in the
        // singular (StudentController, ClassController, MessageController...).
        $segment = $parts[0] ?? '';

        // ---- Public website routes ----
        // The school's public website lives inside the same application as the
        // management system. These segments are handled by the public
        // WebsiteController before the standard segment->controller resolution,
        // so guests never hit the login middleware on the website pages.
        //
        // The bare /teachers segment is the public staff showcase, while
        // teachers/index and every other teachers/... management route keep
        // resolving to TeacherController (the sidebar and all management links
        // use the explicit sub-paths, so management is fully preserved).
        $publicRoutes = [
            ''           => ['WebsiteController', 'index'],
            'home'       => ['WebsiteController', 'home'],
            'about'      => ['WebsiteController', 'about'],
            'academics'  => ['WebsiteController', 'academics'],
            'admissions' => ['WebsiteController', 'admissions'],
            'teachers'   => ['WebsiteController', 'teachers'],
            'news'       => ['WebsiteController', 'news'],
            'contact'    => ['WebsiteController', 'contact'],
            'contactSubmit' => ['WebsiteController', 'contactSubmit'],
            'portal'     => ['WebsiteController', 'portal'],
        ];
        if (isset($publicRoutes[$segment]) && ($segment !== 'teachers' || !isset($parts[1]))) {
            [$publicController, $publicMethod] = $publicRoutes[$segment];
            $publicFile = CONTROLLER_PATH . '/' . $publicController . '.php';
            if (is_file($publicFile)) {
                $controller = new $publicController();
                if (method_exists($controller, $publicMethod)) {
                    $params = array_slice($parts, 1);
                    if (method_exists($controller, 'checkAuth') && !$controller->checkAuth()) {
                        return;
                    }
                    call_user_func_array([$controller, $publicMethod], $params);
                    return;
                }
            }
        }

        $controllerName = $segment !== ''
            ? ucfirst(strtolower($segment)) . 'Controller'
            : $this->defaultController;

        $controllerFile = CONTROLLER_PATH . '/' . $controllerName . '.php';

        if (!is_file($controllerFile) && $segment !== '' && str_ends_with($segment, 's')) {
            // Try each candidate singular form until a controller file matches
            // (classes -> class, messages -> message, categories -> category).
            $candidates = [substr($segment, 0, -1)];
            if (str_ends_with($segment, 'ies')) {
                $candidates[] = substr($segment, 0, -3) . 'y';
            }
            if (str_ends_with($segment, 'es')) {
                $candidates[] = substr($segment, 0, -2);
            }

            foreach ($candidates as $candidate) {
                $name = ucfirst(strtolower($candidate)) . 'Controller';
                if (is_file(CONTROLLER_PATH . '/' . $name . '.php')) {
                    $controllerName = $name;
                    $controllerFile = CONTROLLER_PATH . '/' . $name . '.php';
                    break;
                }
            }
        }

        // 404 if the controller file does not exist.
        if (!is_file($controllerFile)) {
            $this->notFound('Controller "' . $controllerName . '" not found.');
            return;
        }

        $controller = new $controllerName();

        // Resolve the method name. Links use kebab-case (export-csv, my-results,
        // id-card, role-permissions) while controller methods are camelCase.
        $method = $parts[1] ?? $this->defaultMethod;
        if (!method_exists($controller, $method)) {
            $camel = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $method))));
            if (method_exists($controller, $camel)) {
                $method = $camel;
            }
        }

        if (!method_exists($controller, $method)) {
            $this->notFound('Method "' . $method . '" not found on ' . $controllerName . '.');
            return;
        }

        // The remaining URL segments become method parameters.
        $params = array_slice($parts, 2);

        // Run any controller middleware (auth / RBAC).
        if (method_exists($controller, 'checkAuth') && !$controller->checkAuth()) {
            return;
        }

        // Licence : verrouillage si invalide, lecture seule si expirée (voir core/LicenseGate.php).
        LicenseGate::enforce($controllerName, $method);

        // Call the controller method with parameters.
        call_user_func_array([$controller, $method], $params);
    }

    /**
     * Render a 404 error page.
     */
    protected function notFound(string $message = 'Page not found.'): void
    {
        http_response_code(404);
        $view = new View();
        $view->render('errors/404', ['message' => $message], 'layouts/auth_layout');
        exit;
    }
}
