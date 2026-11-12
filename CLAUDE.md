# CLAUDE.md — COCO SCHOOL (guide pour les assistants et développeurs)

## Architecture (règle absolue)
```
Web (contrôleurs + vues)  →  CORE (services)  →  Repositories  →  Base de données
```
* `core/` = **COCO SCHOOL CORE** : moteur métier PHP 8.1, namespace `CocoCore\`, autoload `core/Bootstrap/autoload.php`, aucune dépendance (ni Composer, ni session, ni vue).
* `web/` = application Web cliente du Core (`web/app`, `web/public`). Les contrôleurs restent légers.
* `database/` migrations versionnées (`migrations/`), données de démo (`seeds/`), `school_management.sql` (tout-en-un), scripts de sauvegarde.
* `plugins/` extensions CCPLG (modèles de bulletins signés) ; `server/` futur serveur LAN ; `shared/` `cloud/` `installers/` : réservés.
* `tests/` : `php tests/run.php` (sans PHPUnit). `tools/` : `audit.py`, `migrate.php`.

## Règles
1. **Aucune règle métier dans `web/`** : calculs, validations, autorisations → Core. Un nouveau besoin = un service du Core + test.
2. Le Core ne référence jamais `$_SESSION`, `$_POST`, `Database::getInstance()` (classe de `web/`), ni une vue.
3. Permissions appliquées côté Core (`AuthorizationService`). Requêtes SQL **paramétrées** uniquement.
4. Bulletin publié = immuable (nouvelle version pour toute correction) ; tout changement sensible est journalisé.
5. Migrations : ne jamais modifier une migration appliquée ; en ajouter une (`NNNN_nom.sql`).
6. Sauvegarde avant toute opération destructive (`database/scripts/backup.sh`).
7. Mettre à jour `PROJECT_STATUS.md`, `TASKS.md` et `documentation/DECISIONS.md` à chaque phase ; commits atomiques par phase.
8. Textes de l'interface : clé anglaise + traduction dans `web/app/lang/<code>/` (voir `web/app/lang/README.md`).

9. **Niveaux distincts** : toute règle qui dépend du niveau passe par `core/Levels` (`LevelRegistry`, `PromotionService`…), jamais par des `if` dans le Web.
10. **Licence** : tout service d'écriture appelle `LicenseGuardInterface::requireWrite()` ; toute création d'élève/inscription appelle `requireQuota()`. Ne jamais contourner `LicenseGate`.
11. Après une migration : `python3 tools/generate_entities.py && python3 tools/build_sql.py`, puis `sh tools/check_all.sh`.

## État / limites connues
Voir `PROJECT_STATUS.md` : le code n'a pas pu être exécuté dans l'environnement de rédaction (pas de PHP) — **lancer d'abord `php tests/run.php`**.
