# Maternel, primaire, secondaire : trois gestions distinctes

Chaque niveau est un **établissement** (`establishments.level`) avec son chef (Directeur au maternel et au primaire, **Préfet** au secondaire),
ses classes, son personnel, ses frais et ses règles. Un « complexe » peut regrouper les trois ; les droits d'un utilisateur sont limités aux
établissements auxquels il est rattaché (`user_establishments` ; `Actor::canAccessEstablishment`). Les administrateurs voient tout.
Code : `core/Levels/` (`LevelProfile` + un profil par niveau + règles propres).

| | Maternel | Primaire | Secondaire |
|---|---|---|---|
| Durée / classes | 3 ans : M1, M2, M3 (petite, moyenne, grande section) | 6 ans : P1 à P6 (degrés élémentaire, moyen, terminal) | 6 ans : EB7, EB8 (éducation de base) + H1 à H4 (humanités par option) |
| Âge d'entrée | 3 ans révolus | 6 ans révolus | à partir de 11 ans (EB7) |
| Évaluation | **Qualitative** par domaine (acquis / en cours / non acquis) — aucune note, aucun classement | Points par période et examen, 3 trimestres, classement | Points par période et examen, 2 semestres, maxima par cours (heures hebdomadaires), classement, conduite |
| Passage | Automatique par âge ; M3 → P1 à 6 ans révolus | ≥ 50 % : admis ; sinon redoublement | ≥ 50 % admis ; 40–49,99 % repêchage ; < 40 % redoublement |
| Examen national | aucun | **TENAFEP** (6ème) : 60 % école + 40 % test | **Exetat** (4ème humanités), mentions |
| Modules propres | développement, santé, personnes autorisées à récupérer l'enfant, cahier de liaison | TENAFEP, fiche de promotion | options/filières, conduite, repêchage, Exetat, horaires, paie |
| Chef d'établissement | Directeur | Directeur | Préfet (+ directeur des études, directeur de discipline) |
| Effectif conseillé / division (à valider) | 30 | 50 | 45 |

## Sources et valeurs à valider
Structure (maternel 3 ans non obligatoire ; primaire 6 ans, admission à 6 ans, TENAFEP ; secondaire en cycles, humanités par options, Exetat) :
documents publics (UNESCO/IIEP, presse congolaise, mémoires universitaires). **Les sources trouvées sur les règles fines sont parfois belges/québécoises ou contradictoires : les valeurs suivantes sont des PAR DÉFAUT à faire valider** avec l'inspection / l'école, et sont toutes modifiables :
pondération 60/40 du TENAFEP, bande de repêchage 40–50 %, mentions de l'Exetat, maxima selon les heures (`CourseMaxima`), seuil de « prêt pour le primaire » (60 % de domaines acquis, langage et psychomotricité requis), barèmes de conduite, réduction fratrie (10 % / 20 %), taux de pénalité de retard, effectifs maximums.

## Fonctionnalités ajoutées pour la gestion des écoles de RDC (suggestions)
Établissements et rattachement des utilisateurs ; inscriptions par année scolaire avec contrôle d'âge, de capacité et de licence ; carnet de santé et personnes autorisées
(maternel) ; cahier de liaison quotidien ; options/filières et maxima par cours (secondaire) ; inscriptions TENAFEP/Exetat avec numéro de candidat ; repêchage ; conduite ;
barèmes de frais par établissement, réduction fratrie, pénalité de retard, échéancier ; paie (taux configurables) ; détection des conflits d'horaire ; statistiques de résultats
(moyenne, médiane, écart-type, taux de réussite par mention et par sexe) ; classement avec ex æquo ; attestations numérotées ; licences.
