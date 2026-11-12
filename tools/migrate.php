<?php
/**
 * Migrations versionnées de la base (CLI).
 *   php tools/migrate.php status
 *   php tools/migrate.php migrate            applique les migrations en attente
 *   php tools/migrate.php baseline 0002      marque 0001..0002 comme DÉJÀ appliquées (base existante de l'ancienne application)
 * Sauvegardez AVANT : sh database/scripts/backup.sh
 */
declare(strict_types=1);

require __DIR__ . '/../core/Bootstrap/autoload.php';

use CocoCore\Database\Connection;
use CocoCore\Database\Migrator;

$cfg = require __DIR__ . '/../web/app/config/database.php';
$db = Connection::fromConfig(['host' => $cfg['host'], 'name' => $cfg['dbname'], 'user' => $cfg['username'], 'pass' => $cfg['password'], 'port' => $cfg['port'] ?? 3306]);
$m = new Migrator($db, __DIR__ . '/../database/migrations');

$cmd = $argv[1] ?? 'status';
if ($cmd === 'status') {
    foreach ($m->status() as $r) {
        printf("%s  %-8s %s\n", $r['version'], $r['status'], $r['name']);
    }
} elseif ($cmd === 'migrate') {
    $done = $m->migrate();
    echo $done ? "Appliquées : " . implode(', ', $done) . PHP_EOL : "Rien à appliquer.\n";
} elseif ($cmd === 'baseline') {
    $upTo = $argv[2] ?? '';
    if (!preg_match('/^\d{4}$/', $upTo)) {
        fwrite(STDERR, "Usage : php tools/migrate.php baseline 0002\n");
        exit(1);
    }
    $m->ensureTable();
    foreach (Migrator::discover(__DIR__ . '/../database/migrations') as $v => $file) {
        if ($v <= $upTo) {
            $db->run('INSERT IGNORE INTO schema_migrations (version, name, checksum) VALUES (?, ?, ?)', [$v, basename($file), hash_file('sha256', $file)]);
            echo "Marquée appliquée : " . basename($file) . PHP_EOL;
        }
    }
} else {
    fwrite(STDERR, "Commande inconnue : $cmd\n");
    exit(1);
}
