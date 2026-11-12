# Journal des décisions techniques

| # | Décision | Raison |
|---|---|---|
| D1 | Le dépôt de départ = l'application actuelle (`legacy/baseline`) | « application existante » = référence fonctionnelle |
| D2 | PHP ≥ 8.1, sans Composer | déduit du code (array_is_list, match) ; pas de dépendance à justifier |
| D3 | Core en namespace `CocoCore\`, autoload maison PSR-4 | indépendance totale vis-à-vis du Web |
| D4 | Schéma cible = schéma actuel (+ `core_audit_log`) | pas de refonte destructive ; migrations additives |
| D5 | Migration 0001 sans `DROP`, `CREATE TABLE IF NOT EXISTS` | jamais de suppression sans validation ; permet d'adopter une base existante (`migrate.php baseline`) |
| D6 | Données de démonstration séparées (`seeds/`) ; fichier unique `school_management.sql` conservé | demande de l'utilisateur d'un seul fichier d'installation |
| D7 | Encaissement porté sur `student_fees`/`fee_collections` | c'est le module Finance réellement utilisé par l'interface |
| D8 | Bulletin publié immuable, versions + instantané JSON | exigence « pas de modification silencieuse » |
| D9 | Hors-ligne : journal d'opérations (voir SYNCHRONISATION_LAN.md) | conflits traçables |
| D10 | Tests sans PHPUnit (`tests/run.php`) | zéro dépendance ; PHPUnit pourra les remplacer |
| D11 | Suppression de l'ancien module de bulletins dupliqué (3 implémentations) | cohérence |

| D12 | Un modèle de domaine par table, générés depuis les migrations | « le Core contient tous les modèles » sans dérive manuelle ; `check_all.sh` détecte un oubli de régénération |
| D13 | Trois niveaux = trois établissements (`establishments`), règles dans `core/Levels/*` | gestions distinctes demandées ; extensible par profil |
| D14 | Licence : Ed25519 hors ligne, essai 30 j, grâce, lecture seule après expiration, verrouillage si falsifiée | fonctionne sans Internet (LAN) ; sodium requis |
| D15 | Absence de tables de licence (base non migrée) : le Web ne bloque pas (message d'alerte) ; absence de sodium : blocage | ne pas casser une installation existante ; ne jamais accepter une clé non vérifiable |
| D16 | Valeurs réglementaires incertaines = par défaut explicites + documentées (NIVEAUX.md) | ne pas présenter comme officiel ce qui n'a pas été confirmé |
| D17 | Correctif schéma : `results.exam_id` nullable + `results.result_date` | le module Résultats écrivait des colonnes absentes (trouvé par check_schema.py) |

## Questions ouvertes (validation métier requise)
* Barème des mentions officiel de l'école/de la province (seuils actuels : E ≥ 90, TB ≥ 80, B ≥ 70, AB ≥ 60, S ≥ 50).
* Maxima par cours/période (valeurs par défaut : période 10, examen 20, modifiables par schéma).
* Fusion des deux systèmes de frais : quel historique conserver ?
* Multi-établissement : une base par école (LAN) ou une base partagée avec `school_id` ?
