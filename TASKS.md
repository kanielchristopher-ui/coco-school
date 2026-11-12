# TASKS

Légende : ✅ fait · 🟡 partiel · ⬜ à faire

## P0 — Valider ce qui existe
- ⬜ `php -v` (≥ 8.1) ; `php tests/run.php` ; corriger les échecs
- ⬜ `sh database/scripts/backup.sh` puis `php tools/migrate.php status|migrate` sur une base de test ; comparer volumes avant/après
- ⬜ Parcours manuel : connexion → bulletins (saisie, génération, validation, publication, impression) ; encaissement
- ⬜ Vérifier manuellement les 42 routes « sans garde directe » (documentation/audit/10_risques.md)

## P1 — Raccorder le Web au Core (supprimer les règles métier des contrôleurs)
- ⬜ `AuthController` → `Kernel::auth()->attempt()` (la session Web ne garde que l'Actor)
- ⬜ `FinanceController::collectionsStore` → `PaymentService::collect()` (+ annulation/modification de reçu dans le Core)
- ⬜ `BulletinsController` : validation/publication via `BulletinWorkflow` (transitions + audit dans le Core)
- ⬜ Remplacer les `requireRole([...])` par `AuthorizationService` + permissions (une seule logique)
- ⬜ Suppressions en POST + CSRF (20 méthodes GET destructives)

## P1bis — Licences et niveaux (nouveau)
- ⬜ Remplacer la clé de développement (`core/Licensing/trusted_keys.php`) par la vôtre ; émettre les vraies licences
- ⬜ Vérifier que l'extension `sodium` est active sur le serveur cible
- ⬜ Écrans Web par niveau : développement/santé/personnes autorisées/cahier de liaison (maternel), TENAFEP (primaire), options/conduite/repêchage/Exetat (secondaire) — les services Core existent, les écrans restent à créer
- ⬜ Rattacher les utilisateurs aux établissements (`user_establishments`) dans l'écran Utilisateurs ; filtrer les listes par établissement
- ⬜ Valider avec l'inspection les valeurs par défaut listées dans `documentation/NIVEAUX.md`
- ⬜ Services d'écriture avec règles pour : établissements, enseignants/personnel, parents, matières, présences, discipline, transport, inventaire, notifications (aujourd'hui : dépôt générique)

## P2 — Modules du Core restants
- ⬜ Établissements (`school_id` partout, multi-établissement) · ⬜ Utilisateurs/rôles
- ⬜ Élèves, parents, enseignants · ⬜ Classes, matières, inscriptions, passages de classe
- ⬜ Périodes scolaires (années, trimestres/semestres dans la base)
- ⬜ Notes d'examens (module Examens/Résultats historique) — fusionner avec le moteur de bulletins
- ⬜ Unifier les **deux systèmes de frais** (`fees/payments` et `student_fees/fee_collections`)
- ⬜ Paie, inventaire, transport, discipline, présences
- ⬜ Documents (PDF), notifications, rapports/statistiques

## P3 — API et LAN
- ⬜ `server/` : API REST (contrat : documentation/API_REST.md), authentification des appareils
- ⬜ Synchronisation (journal d'opérations, résolution des conflits)
- ⬜ Installateurs et mises à jour (`installers/`)

## P4 — Qualité
- ⬜ Tests d'intégration sur base de test (MySQL), tests fonctionnels Web, tests de performance et de sécurité
- ⬜ Rapport de validation final
