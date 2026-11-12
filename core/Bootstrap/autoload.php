<?php
/**
 * Autoloader PSR-4 de COCO SCHOOL CORE (aucune dépendance Composer).
 * CocoCore\Foo\Bar  ->  core/Foo/Bar.php
 */
declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'CocoCore\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $file = dirname(__DIR__) . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});
