# PROJECT_STATUS — COCO SCHOOL

*Branche : `feature/core-rebuild` (référence de l'ancienne application : `legacy/baseline`).*

## Avertissement de vérification
Ce dépôt a été rédigé dans un environnement **sans PHP ni MySQL** : le code PHP et les tests n'ont **pas été exécutés**, et les migrations n'ont pas été jouées sur une vraie base. Seuls ont été vérifiés : équilibre syntaxique des fichiers PHP/JS, cohérence des instructions SQL (guillemets, nombre de colonnes), logique des calculs par recoupement avec l'ancien code. **Première action recommandée : `php -v`, `php tests/run.php`, puis `php tools/migrate.php status`.**

## Avancement par étape (spécification)
| Étape | État | Détail |
|---|---|---|
| 0 Préparation | ✅ fait | branche dédiée, arborescence cible, CLAUDE.md, ce fichier, TASKS.md ; sauvegarde = branche `legacy/baseline` + `backup.sh` |
| 1 Audit | ✅ fait (automatisé) | `documentation/audit/` : 220 routes, 107 formulaires, 51 tables, 33 FK, rôles/permissions, plugins, dépendances, risques — régénérable : `python3 tools/audit.py` |
| 2 Règles métier | 🟡 partiel | documentées pour bulletins, finance (encaissement), authentification : `documentation/REGLES_METIER.md` ; autres modules à documenter |
| 3 Architecture | ✅ fait | `documentation/ARCHITECTURE.md`, `DECISIONS.md`, `API_REST.md`, `SYNCHRONISATION_LAN.md` |
| 4 Core | 🟡 en cours (étendu) | **Fait** : Kernel, exceptions, événements, sécurité (Actor avec portée par établissement, autorisations, politique bulletins), Auth, Notation, Bulletins (workflow), Finance (encaissement, barèmes, fratrie, pénalités, échéancier, paie), **73 modèles de domaine générés (un par table) + dépôt générique**, **licences** (signature, validité, grâce, essai, quotas), **trois profils de niveau distincts** (maternel / primaire / secondaire) avec règles propres (promotion, TENAFEP, Exetat, évaluation qualitative, personnes autorisées, conduite, maxima), inscriptions, classement, statistiques, assiduité, horaires, attestations, base (Connection, Migrator, dépôts PDO), plugins CCPLG. **Reste** : services d'écriture pour la plupart des modèles (aujourd'hui accessibles via le dépôt générique, sans règles métier dédiées), notifications, rapports, raccordement complet du Web (voir TASKS.md) |
| 5 Migration BD | 🟡 partiel | migrations versionnées 0001-0003 + `tools/migrate.php` + `baseline` pour base existante ; **non testée sur une vraie base** ; pas de script de transformation (le schéma cible = schéma actuel) |
| 6 Web | 🟡 partiel | application existante déplacée dans `web/` et rebranchée sur le Core pour : calcul des bulletins, CCPLG, politique de rôles. **Les autres modules passent encore par les contrôleurs/modèles historiques** (181 accès BD directs dans les contrôleurs) |
| 7 Tests | 🟡 partiel | `tests/core` (~450 vérifications) : notation, bulletins, finance, sécurité, auth, plateforme, **licences, modèles + dépôt (SQLite), niveaux, services, inscriptions** — **écrits mais NON exécutés (pas de PHP ici)**. **Exécutés** : `tools/check_schema.py` (clés étrangères, INSERT, colonnes utilisées par le code → a trouvé et corrigé un vrai bug `results.result_date`), `tools/test_license_tool.py` (format et 14 états de licence), vérifications syntaxiques PHP |
| 8 Serveur LAN | 🟡 spécifié | contrat REST + stratégie hors-ligne/sync documentés ; **aucun serveur implémenté** (`server/`) |
| 9 Livraison | 🟡 partiel | guides installation/migration/exploitation écrits ; pas d'installateur ; pas de rapport de validation (tests non exécutés) |

## Vérifications réellement exécutées (Python, dans cet environnement)
`python3 tools/check_schema.py` → OK (73 tables, 46 FK, 65 INSERT) · `python3 tools/test_license_tool.py` → 0 échec · équilibre syntaxique de 396 fichiers PHP · imports et espaces de noms du Core résolus. **À exécuter chez vous : `sh tools/check_all.sh`** (ajoute `php tests/run.php`).

## Ce que le projet n'est PAS encore
* Une reconstruction complète : seuls 4 domaines sont dans le Core (authentification, notation/bulletins, finance/encaissement, plugins).
* Prouvé en exécution (voir avertissement).
