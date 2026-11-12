# 09 — Dépendances techniques

- **PHP minimal déduit du code : 8.1** (array_is_list, match, types union).
  *La version réellement installée sur le serveur cible n'a pas pu être vérifiée dans cet environnement (aucun PHP disponible) : à confirmer avec `php -v`.*
- Base : MySQL 8 / MariaDB 10.4+ (ENUM, ON DUPLICATE KEY, FIELD(), InnoDB, FK).
- Composer : **aucune** dépendance (aucun composer.json).

## Fonctionnalités PHP utilisées (nombre de fichiers)
- JSON : 15
- PDO MySQL : 3
- ZipArchive (zip) : 4
- array_is_list (PHP 8.1) : 1
- match : 6
- mb_* (mbstring) : 12
- password_hash : 5
- sodium_* : 4
- str_contains/starts/ends_with (PHP 8.0) : 10
- union types (PHP 8.0) : 5

## Bibliothèques front (locales, dans web/public/assets/vendor)

- bootstrap
- fontawesome
- web/public/assets/js/local-ui.js (graphiques + calendrier maison)