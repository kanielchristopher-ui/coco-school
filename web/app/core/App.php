<?php
declare(strict_types=1);

/**
 * ============================================================
 * SYSTÈME DE GESTION SCOLAIRE (RDC)
 * Application Bootstrap
 * ============================================================
 */

// ---------- Application paths ----------
define('ROOT_PATH', dirname(__DIR__, 2));

define('APP_PATH', ROOT_PATH . '/app');

define('CORE_PATH', APP_PATH . '/core');
define('CONFIG_PATH', APP_PATH . '/config');
define('CONTROLLER_PATH', APP_PATH . '/controllers');
define('MODEL_PATH', APP_PATH . '/models');
define('VIEW_PATH', APP_PATH . '/views');
define('PUBLIC_PATH', ROOT_PATH . '/public');
// ---------- Load configuration ----------
$config = require CONFIG_PATH . '/config.php';
$dbConfig = require CONFIG_PATH . '/database.php';

// ---------- Error reporting ----------
if (defined('APP_DEBUG') && APP_DEBUG === true) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// ---------- Timezone ----------
date_default_timezone_set(
    defined('APP_TIMEZONE') ? APP_TIMEZONE : 'UTC'
);

// ---------- COCO SCHOOL CORE (moteur métier indépendant : /core à la racine du dépôt) ----------
require ROOT_PATH . '/../core/Bootstrap/autoload.php';

// ---------- Class autoloader ----------
spl_autoload_register(function (string $class): void {

    $class = ltrim($class, '\\');

    $paths = [
        CORE_PATH . '/' . $class . '.php',
        CONTROLLER_PATH . '/' . $class . '.php',
        MODEL_PATH . '/' . $class . '.php',
    ];

    foreach ($paths as $file) {
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

// ---------- Load helpers ----------
$helperFile = CORE_PATH . '/Helper.php';

if (is_file($helperFile)) {
    require_once $helperFile;
}

// ---------- Start session ----------
Session::start();

// ---------- Internationalisation ----------
// Choisit la langue (utilisateur > Paramètres > DEFAULT_LANG) puis installe le
// filtre de sortie qui traduit toutes les pages (voir app/core/Lang.php).
Lang::init();
ob_start([Lang::class, 'filter']);

// ---------- Authentication ----------
Auth::loginFromRememberCookie();

// ---------- Initialize router ----------
$router = new Router();

// ---------- Dispatch request ----------
$router->dispatch();
