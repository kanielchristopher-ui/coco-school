# Architecture cible

```
coco-school/
├── core/            COCO SCHOOL CORE (PHP 8.1, namespace CocoCore\)
│   ├── Bootstrap/   autoload PSR-4, Kernel (assemblage des services)
│   ├── Contracts/   interfaces (dépôts, transactions, événements, audit)
│   ├── Exceptions/  CoreException, Validation, BusinessRule, NotFound, PermissionDenied
│   ├── Events/      DomainEvent, EventDispatcher
│   ├── Security/    Actor, AuthorizationService, PasswordHasher, SchoolRolePolicy
│   ├── Modules/     Auth, Grading (barème, schéma, calcul), Bulletins (workflow), Finance (encaissement)
│   ├── Database/    Connection (PDO + transactions), Migrator, dépôts PDO, audit
│   └── Plugins/     CCPLG (paquets signés), SafeTemplateRenderer
├── web/             application Web (contrôleurs légers, vues, langues FR/EN)
├── database/        migrations/ (versionnées) · seeds/ · scripts/ (sauvegarde) · school_management.sql
├── plugins/         extensions CCPLG + outils de signature
├── server/          (futur) serveur LAN — API REST
├── shared/ cloud/ installers/   réservés
├── tests/           tests du Core (php tests/run.php)
├── tools/           audit.py, migrate.php
└── documentation/   audit, architecture, décisions, API, sync, guides
```

## Flux
`Web (contrôleur) → Core (service) → Repository (interface) → Base`. Le Web convertit session/formulaire en `Actor` + paramètres simples, appelle un service, affiche le résultat ou l'exception (`ValidationException::errors()`, `BusinessRuleException`, `PermissionDeniedException`).

## Principes
* **Indépendance** : le Core n'utilise ni session ni vue ; il se teste avec des doublons en mémoire (`tests/core/Fakes.php`).
* **Contrats stables** : chaque accès aux données passe par une interface (`Contracts/`) ; l'implémentation PDO est remplaçable (SQLite local pour un poste hors-ligne, API distante pour un client natif).
* **Règles centralisées** : autorisation (`AuthorizationService`), validations et calculs dans les services.
* **Transactions** : `TransactionInterface::transaction()` entoure toute écriture multi-étapes (reçu + statut + audit).
* **Événements** : `payment.recorded` … (écouteurs pour notifications, synchronisation).
* **Extensibilité** : plugins CCPLG déclaratifs ; aucun code tiers exécuté.

## Modules du Core (état)
| Module | Service | État |
|---|---|---|
| Auth | `AuthService::attempt` | ✅ |
| Sécurité | `AuthorizationService`, `SchoolRolePolicy` | ✅ |
| Notation | `EvaluationScheme`, `ReportCardCalculator`, `GradeScale` | ✅ |
| Bulletins | `BulletinWorkflow` (+ moteur Web `BulletinEngine` pour l'instantané) | 🟡 |
| Finance | `PaymentService::collect` | 🟡 (annulation, modification de reçu : à faire) |
| Plugins | CCPLG | ✅ |
| Autres domaines | élèves, classes, périodes… | ⬜ (TASKS.md) |

## Ajouts : modèles, niveaux, licences
* `core/Domain/` : **un modèle par table** (73), générés depuis les migrations (`python3 tools/generate_entities.py`) — typage, champs obligatoires, énumérations, longueurs, immuabilité ; `EntityRegistry` (table → classe) ; `core/Repositories/EntityRepository` : dépôt générique (liste blanche de colonnes, requêtes paramétrées).
* `core/Levels/` : `SchoolLevel`, `LevelProfile` (Maternel/Primaire/Secondaire), `ClassLadder`, `PromotionService` + règles propres à chaque niveau (`Maternel/`, `Primaire/`, `Secondaire/`). Voir NIVEAUX.md.
* `core/Licensing/` : licence signée, vérification hors ligne, essai, grâce, quotas. Voir LICENCES.md.
* `core/Modules/` : Ranking, Statistics, Attendance, Finance (+ FeePlan, Payroll), Timetable, Enrollment, Identity, Documents.
* Séparation par établissement : `Actor::establishmentIds` ; `EnrollmentService` refuse l'accès hors établissement.

## Multi-plateforme
Un client natif (Android, iOS, Windows…) consommera l'API REST du futur `server/` (voir API_REST.md) qui appelle le même Core : aucune règle métier à réécrire.
