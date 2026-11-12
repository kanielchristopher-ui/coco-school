<?php
/**
 * Génère une paire de clés Ed25519 pour signer des extensions .ccplg.
 *   php tools/ccplg_keygen.php
 * - La clé PUBLIQUE se copie dans app/config/ccplg.php (trusted_public_key).
 * - La clé PRIVÉE reste chez l'éditeur de l'extension : ne la mettez jamais sur le serveur de l'école.
 */
if (!function_exists('sodium_crypto_sign_keypair')) {
    fwrite(STDERR, "L'extension PHP sodium est requise.\n");
    exit(1);
}
$pair = sodium_crypto_sign_keypair();
echo "Clé publique (config.php)  : " . base64_encode(sodium_crypto_sign_publickey($pair)) . PHP_EOL;
echo "Clé privée (SECRÈTE)       : " . base64_encode(sodium_crypto_sign_secretkey($pair)) . PHP_EOL;
