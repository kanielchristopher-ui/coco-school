<?php
/**
 * ============================================================
 *  School Management System - Web Installer
 *  ============================================================
 *  1. Creates the database tables from database/sql/schema.sql
 *  2. Optionally imports sample/seed data from sample_data.sql
 *  3. Verifies the uploads directory is writable
 *
 *  SECURITY: delete this file (public/install.php) after a
 *  successful installation.
 * ============================================================
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$baseDir = dirname(__DIR__);
$config  = require $baseDir . '/app/config/database.php';

$schemaFile = dirname($baseDir) . '/database/school_management.sql';   // fichier unique : tables + données de test
$sampleFile = '';

$defaults = [
    'host'     => $config['host'] ?? '127.0.0.1',
    'port'     => $config['port'] ?? 3306,
    'dbname'   => $config['dbname'] ?? 'school_management',
    'username' => $config['username'] ?? 'root',
    'password' => $config['password'] ?? '',
];

$errors = [];
$log    = [];
$done   = false;
$installed = false;

$submitted = ($_SERVER['REQUEST_METHOD'] === 'POST');
if ($submitted) {
    $host     = trim($_POST['host'] ?? '');
    $port     = trim($_POST['port'] ?? '');
    $dbname   = trim($_POST['dbname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $withSeed = !empty($_POST['with_seed']);

    if ($host === '' || $dbname === '' || $username === '') {
        $errors[] = 'Host, database name and username are required.';
    }

    if (!$errors) {
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            if (!is_file($schemaFile)) {
                $errors[] = 'Missing database/school_management.sql';
            } else {
                $sql = file_get_contents($schemaFile);
                // La base est déjà créée/sélectionnée par l'installateur : on retire CREATE DATABASE / USE du fichier.
                $sql = preg_replace('/^\s*(CREATE DATABASE|USE)\b[^;]*;\s*$/mi', '', $sql);
                $pdo->exec($sql);
                $log[] = 'Imported ' . basename($schemaFile) . ' (' . count(explode(';', $sql)) . ' statements).';

                if ($withSeed && $sampleFile !== '' && is_file($sampleFile)) {
                    $pdo->exec(file_get_contents($sampleFile));
                    $log[] = 'Imported ' . basename($sampleFile) . ' (sample/seed data).';
                } else {
                    $log[] = 'Sample data is included in the single SQL file.';
                }

                $installed = true;
                $done = true;
            }
        } catch (PDOException $ex) {
            $errors[] = 'Database error: ' . $ex->getMessage();
        } catch (Throwable $ex) {
            $errors[] = 'Unexpected error: ' . $ex->getMessage();
        }
    }

    if (!$errors) {
        $installed = true;
        $done = true;
    }
}

function baseUrlGuess(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443;
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    return $scheme . '://' . $host . $dir;
}

$uploads = __DIR__ . '/uploads';
$uploadsWritable = is_dir($uploads) && is_writable($uploads);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install - School Management System</title>
    <link rel="stylesheet" href="assets/vendor/bootstrap/bootstrap.min.css">
    <style>
        body { background: #f1f5f9; min-height: 100vh; display: flex; align-items: center; }
        .card { max-width: 640px; margin: 24px auto; border-radius: 14px; }
        pre { background: #1e293b; color: #cbd5e1; padding: 12px; border-radius: 8px; max-height: 260px; overflow: auto; }
    </style>
</head>
<body>
<div class="container">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h1 class="h4 mb-1">School Management System</h1>
            <p class="text-muted">Web installer</p>

            <?php if ($done && $installed): ?>
                <div class="alert alert-success">Installation completed successfully.</div>
                <?php if (!$uploadsWritable): ?>
                    <div class="alert alert-warning">The <code>public/uploads</code> directory is not writable. Set write permission on it so photo uploads work.</div>
                <?php endif; ?>
                <ul class="list-group list-group-flush mb-3">
                    <?php foreach ($log as $line): ?>
                        <li class="list-group-item"><?= htmlspecialchars($line) ?></li>
                    <?php endforeach; ?>
                </ul>
                <p>Your application base URL is most likely:</p>
                <pre><?= htmlspecialchars(baseUrlGuess()) ?></pre>
                <p>Update <code>BASE_URL</code> in <code>app/config/config.php</code> if needed, then log in with <strong>admin / password</strong>.</p>
                <p class="text-danger small mb-3">Security: delete <code>public/install.php</code> before going live.</p>
                <a class="btn btn-primary" href="./">Go to application</a>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?= nl2br(htmlspecialchars(implode("\n", $errors))) ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($log)): ?>
                    <div class="alert alert-info">
                        <?= nl2br(htmlspecialchars(implode("\n", $log))) ?>
                    </div>
                <?php endif; ?>

                <form method="post" autocomplete="off">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Database host</label>
                            <input class="form-control" name="host" value="<?= htmlspecialchars($defaults['host']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Port</label>
                            <input class="form-control" name="port" value="<?= htmlspecialchars((string) $defaults['port']) ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Database name</label>
                            <input class="form-control" name="dbname" value="<?= htmlspecialchars($defaults['dbname']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input class="form-control" name="username" value="<?= htmlspecialchars($defaults['username']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input class="form-control" name="password" type="password" value="" placeholder="(leave blank if none)">
                        </div>
                    </div>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="with_seed" id="with_seed" checked>
                        <label class="form-check-label" for="with_seed">
                            Import sample data (demo users, classes, fees, etc.)
                        </label>
                    </div>

                    <div class="mt-3 alert alert-warning small">
                        This will <strong>drop</strong> any existing tables with the same names in the selected database.
                        Make sure you are not pointing at a database you care about.
                    </div>

                    <button class="btn btn-primary mt-3" type="submit">Install now</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
