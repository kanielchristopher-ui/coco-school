# Installation, configuration, exploitation

## Prérequis
PHP ≥ 8.1 (extensions : pdo_mysql, mbstring, zip, sodium pour les extensions CCPLG), MySQL 8 / MariaDB 10.4+. **Aucun accès internet requis** (toutes les bibliothèques sont locales).

## Installation rapide (démo)
1. Importer `database/school_management.sql` (crée la base `school_management` + données de démonstration).
2. Éditer `web/app/config/database.php` et `web/app/config/config.php` (`BASE_URL`, `APP_DEBUG=false` en production).
3. `php -S localhost:8000 -t web/public` (ou pointer le serveur Web sur `web/public`).
4. Comptes de test : voir `README.md`.

## Installation production (migrations)
```
mysql -e "CREATE DATABASE school_management CHARACTER SET utf8mb4"
php tools/migrate.php migrate        # 0001 schéma, 0002 référence, 0003 audit
```
Pas de données de démonstration ; créer ensuite l'administrateur (changer tous les mots de passe de démo).

## Migrer une base existante (ancienne application)
```
sh database/scripts/backup.sh                      # TOUJOURS d'abord
php tools/migrate.php baseline 0002                # déclare 0001-0002 déjà appliquées
php tools/migrate.php migrate                      # applique 0003 (audit du Core)
```
Retour arrière : `sh database/scripts/restore.sh backups/<fichier>.sql`.

## Exploitation
* Sauvegarde : `DB_NAME=… DB_USER=… DB_PASS=… sh database/scripts/backup.sh` (planifier quotidiennement).
* Mise à jour : sauvegarde → déployer le code → `php tools/migrate.php migrate` → `php tests/run.php`.
* Journaux : table `core_audit_log` (Core), `activity_logs` (Web), `bulletin_audit_log`.
* Extensions de bulletins : `documentation/CCPLG_EXTENSIONS.md`.
* Audit du code : `python3 tools/audit.py` régénère `documentation/audit/`.

## Développement
Voir `CLAUDE.md`. Ajouter un service : interface dans `core/Contracts`, implémentation dans `core/Modules` + `core/Database`, test dans `tests/core`, puis appel depuis un contrôleur léger.
