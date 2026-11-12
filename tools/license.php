<?php
/**
 * Outil de licences (PHP/libsodium) — équivalent de tools/license_tool.py.
 *   php tools/license.php keygen <dossier>
 *   php tools/license.php inspect <clé>
 *   php tools/license.php status                 (état de la licence de la base configurée)
 * L'émission (issue) se fait avec license_tool.py ; la clé privée reste chez l'éditeur.
 */
declare(strict_types=1);

require __DIR__ . '/../core/Bootstrap/autoload.php';

use CocoCore\Licensing\LicenseCodec;

$cmd = $argv[1] ?? '';
if ($cmd === 'keygen') {
    $dir = $argv[2] ?? '.';
    @mkdir($dir, 0700, true);
    $kp = sodium_crypto_sign_keypair();
    file_put_contents("$dir/private.key", base64_encode(sodium_crypto_sign_secretkey($kp)) . "\n");
    chmod("$dir/private.key", 0600);
    $pub = base64_encode(sodium_crypto_sign_publickey($kp));
    file_put_contents("$dir/public.key", $pub . "\n");
    echo "Clé publique (core/Licensing/trusted_keys.php) : $pub\n";
} elseif ($cmd === 'inspect') {
    echo json_encode(LicenseCodec::decode($argv[2] ?? '')['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} elseif ($cmd === 'status') {
    $cfg = require __DIR__ . '/../web/app/config/database.php';
    $core = CocoCore\Bootstrap\Kernel::boot(['host' => $cfg['host'], 'name' => $cfg['dbname'], 'user' => $cfg['username'], 'pass' => $cfg['password'], 'port' => $cfg['port'] ?? 3306]);
    $c = $core->license()->check();
    printf("%s (%s) — jours restants : %s\n%s\n", $c->status->value, $c->status->label(), $c->daysLeft ?? '-', $c->detail);
} else {
    fwrite(STDERR, "Usage : php tools/license.php keygen|inspect|status\n");
    exit(1);
}
