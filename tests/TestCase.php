<?php
declare(strict_types=1);

/** Mini cadre de test sans dépendance (PHPUnit non requis). */
final class T
{
    public static int $passed = 0;
    public static int $failed = 0;

    public static function check(string $name, bool $ok, string $detail = ''): void
    {
        if ($ok) {
            self::$passed++;
            echo "  ok    $name\n";
        } else {
            self::$failed++;
            echo "  FAIL  $name" . ($detail !== '' ? " — $detail" : '') . "\n";
        }
    }

    public static function same(string $name, mixed $expected, mixed $actual): void
    {
        self::check($name, $expected === $actual, 'attendu ' . var_export($expected, true) . ', obtenu ' . var_export($actual, true));
    }

    /** Vérifie qu'une exception de la classe donnée est levée. */
    public static function throws(string $name, string $class, callable $fn): void
    {
        try {
            $fn();
            self::check($name, false, "aucune exception $class");
        } catch (\Throwable $e) {
            self::check($name, $e instanceof $class, 'levée : ' . get_class($e) . ' — ' . $e->getMessage());
        }
    }
}
