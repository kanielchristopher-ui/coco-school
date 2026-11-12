<?php
/**
 * ============================================================
 *  Front Controller
 *  Public entry point. Everything is bootstrapped by App.php,
 *  which loads configuration, helpers, the autoloader and
 *  dispatches the request through the Router.
 * ============================================================
 */

declare(strict_types=1);
// Affichage des erreurs PHP — à utiliser en développement
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require_once dirname(__DIR__) . '/app/core/App.php';




