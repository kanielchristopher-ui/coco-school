# 10 — Risques et incohérences détectés

1. **Règles métier dans les contrôleurs** : 183 appels directs à la base (`$db->…`) dans les contrôleurs (cible : 0, tout passe par le Core).
2. **Suppressions via GET** : 20 méthodes destroy/delete (confirmation JS seulement, pas de CSRF) : InventoryController::destroyItem, TeacherController::destroy, TransportController::destroyVehicle, TransportController::destroyRoute, TransportController::destroyDriver, GradeController::destroy, ClassController::destroy, ClassController::deleteSection, ClassController::deleteTimetable, PayrollController::destroy, FeeController::destroyType, ExamController::destroy ….
3. **CSRF** : contrôle présent dans `Controller` (méthode appelée pour les POST) ; les 20 suppressions GET de (2) échappent à ce contrôle.
4. **SQL construit par concaténation** : app/controllers/AttendanceController.php (2), app/controllers/ClassController.php (2), app/controllers/ExamController.php (3), app/controllers/FinanceController.php (8), app/controllers/GradeController.php (2), app/controllers/StaffController.php (2), app/controllers/StudentController.php (2), app/controllers/SubjectController.php (2), app/controllers/TeacherController.php (2), app/controllers/UserController.php (3) (vérifier que seules des valeurs entières/identifiants contrôlés sont concaténés).
5. **Routes sans garde directe détectée** (44 ; hors auth/site public) : garde possiblement indirecte (méthode `authorize()`, constructeur, helper privé) — **à vérifier manuellement** : bulletins/validateBulletin, bulletins/publish, bulletins/show, bulletins/printView, bulletins/extensions, bulletins/installExtension, bulletins/extensionStatus, bulletins/removeExtension, dashboard/index, dashboard/cashier, dashboard/discipline, finance/index, finance/fees, finance/feesStore, finance/feesDelete, finance/collections, finance/collectionsStore, finance/collectionsUpdate, finance/collectionsVoid, finance/collectionsDelete, finance/collectionsDeleteVoided, finance/receipts, finance/receipt, finance/payroll, finance/payrollStore ….
6. **Double système d'autorisation** : rôles (`requireRole`) et permissions (`requirePermission`) mélangés ; les permissions ne sont pas appliquées partout.
7. **Mono-établissement** : aucune colonne `school_id` sur les tables historiques (les tables bulletins utilisent `school_id = 1`).
8. **Pas de tests automatisés d'intégration** : seul un test unitaire du moteur de bulletins existe.
8b. **Deux systèmes de frais parallèles** : `fees`/`payments` (FeeController) et `student_fees`/`fee_collections` (FinanceController, utilisé par l'interface) — à unifier.
8c. **Identifiants de base de données dans le dépôt** (`web/app/config/database.php`) : à sortir du dépôt (variables d'environnement).
9. **Mots de passe de démonstration** dans le jeu de données (à supprimer en production).
10. **Version PHP/MySQL du serveur cible non vérifiée** ici.

## Corrections déjà apportées avant cet audit
- Constante DEMO_MODE manquante ; colonnes official_id ; tables finance ; formulaires Paramètres qui s'écrasaient ; doublons du module Bulletins supprimés.