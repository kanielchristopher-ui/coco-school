<?php
/**
 * Lance tous les tests :   php tests/run.php [filtre]
 * Code de sortie 0 = tout passe.
 */
declare(strict_types=1);

error_reporting(E_ALL);
set_error_handler(function (int $no, string $str, string $file, int $line): bool {
    throw new ErrorException($str, 0, $no, $file, $line);
});

require __DIR__ . '/TestCase.php';
require __DIR__ . '/../core/Bootstrap/autoload.php';
require __DIR__ . '/core/Fakes.php';

$filter = $argv[1] ?? '';
foreach (glob(__DIR__ . '/core/*Test.php') as $file) {
    if ($filter !== '' && !str_contains($file, $filter)) {
        continue;
    }
    echo PHP_EOL . basename($file, '.php') . PHP_EOL;
    try {
        (require $file)();
    } catch (Throwable $e) {
        T::check('exécution du fichier', false, get_class($e) . ': ' . $e->getMessage());
    }
}
echo sprintf("\n%d réussis, %d échecs\n", T::$passed, T::$failed);
exit(T::$failed === 0 ? 0 : 1);
