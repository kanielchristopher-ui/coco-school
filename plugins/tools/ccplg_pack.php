<?php
/**
 * Construit et signe une extension .ccplg à partir d'un dossier.
 *   php tools/ccplg_pack.php <dossier> <sortie.ccplg> <cle_privee_base64>
 *
 * Le dossier contient (exemple) :
 *   plugin.json      { "plugin_id": "...", "name": "...", "version": "1.0.0" }   (sans "files")
 *   config/periods.json        barème d'évaluation (facultatif)
 *   templates/bulletin.html    modèle avec {{variables}} (facultatif)
 *   templates/bulletin.css     styles (facultatif)
 *   assets/...                 images (png, jpg, svg, webp)
 * Interdits : .php .js .sh .sql (refusés à l'installation).
 */
if ($argc < 4) {
    fwrite(STDERR, "Usage : php tools/ccplg_pack.php <dossier> <sortie.ccplg> <cle_privee_base64>\n");
    exit(1);
}
[$_, $dir, $out, $secretB64] = $argv;
$dir = rtrim($dir, '/\\');
$meta = json_decode((string) file_get_contents($dir . '/plugin.json'), true);
if (!is_array($meta)) {
    fwrite(STDERR, "plugin.json invalide.\n");
    exit(1);
}
$files = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
    if (!$f->isFile() || $f->isLink()) {
        continue;
    }
    $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($dir) + 1));
    if ($rel === 'plugin.json' || $rel === 'signature.ccsig') {
        continue;
    }
    $files[$rel] = ['sha256' => hash_file('sha256', $f->getPathname())];
}
ksort($files);
$manifest = ['format' => 'CCPLG', 'format_version' => 1, 'application' => 'Coco School'] + $meta + ['files' => $files];
$manifest['files'] = $files;
$json = json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
$sig = base64_encode(sodium_crypto_sign_detached($json, base64_decode($secretB64, true)));

$zip = new ZipArchive();
$zip->open($out, ZipArchive::CREATE | ZipArchive::OVERWRITE);
$zip->addFromString('plugin.json', $json);
$zip->addFromString('signature.ccsig', $sig);
foreach (array_keys($files) as $rel) {
    $zip->addFile($dir . '/' . $rel, $rel);
}
$zip->close();
echo "Extension créée : $out (" . count($files) . " fichier(s))\n";
