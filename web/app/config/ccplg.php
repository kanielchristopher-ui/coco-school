<?php
/**
 * Configuration des extensions CCPLG (modèles de bulletins).
 *
 * « trusted_public_key » : clé publique Ed25519 (base64) de l'éditeur de confiance.
 * Elle ne doit JAMAIS venir du paquet. La clé ci-dessous est une clé de DÉVELOPPEMENT :
 * en production, générez la vôtre avec  python3 tools/ccplg_build.py --keygen
 * et gardez la clé privée hors du dépôt.
 * L'installation exige l'extension PHP « sodium » (activée par défaut depuis PHP 7.2) et « zip ».
 */
return [
    'trusted_public_key' => 'O9Ob4mR+G4+KJm0Oh9ZWm/4uA29NaQpg8rLQcOfcnzY=',
    'extensions_dir'     => ROOT_PATH . '/storage/extensions',
    'max_upload_bytes'   => 5000000,
];
