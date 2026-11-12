# Changements de l'adaptation RDC

## Version 4 — fusion avec Coco School (bulletins)
* Moteur de **bulletins périodiques** (`BulletinEngine`, `BulletinsController`) et format d'extensions **CCPLG** (`app/coco`), adaptés à la structure de l'application.
* Tables ajoutées au SQL unique : `school_evaluation_schemes`, `student_marks`, `periodic_bulletins`, `bulletin_audit_log`, `school_extensions` (+ schémas 2026-2027 et notes d'exemple en 1ère Primaire).
* Nouveau rôle **Directeur d'école** ; menus Bulletins pour direction, enseignants, parents et élèves.
* Nettoyage : une seule implémentation du module (suppression des versions en double).

## Version 3 (maquettes reçues)
* Tableaux de bord : cartes colorées, courbes lissées animées, histogrammes groupés (plus de barres empilées), anneau et barres de progression.
* Connexion : grand panneau arrondi avec forme organique et illustration, bouton **Retour à l'accueil**.
* Accueil adapté de la vidéo : bandeau d'accueil animé, maquette mobile, chiffres clés, cycles, pré-inscription, témoignages, FAQ, pied de page.
* Modes **Jour / Nuit / Système** (mémorisé) sur l'application, la connexion et le site ; interface adaptée téléphone, tablette et ordinateur.

## Version 2
* **Hors ligne** : Bootstrap 5.3, Font Awesome, graphiques (`Chart`) et calendrier (`FullCalendar`) remplacés par des copies/outils locaux.
* **Nouveau design** : thème tableau de bord moderne (cartes arrondies, barre latérale, bandeau d'accueil, KPI, graphiques), clair et sombre.
* **Connexion unique** pour tous les rôles ; nouveaux rôles **préfet, directeur des études (proviseur), directeur de discipline, caissière** avec leurs tableaux de bord.
* **Module Discipline** (incidents, gravité, sanction, parents informés, clôture).
* Enseignants : niveau maternel / primaire / secondaire.
* **Un seul fichier SQL** : `database/school_management.sql` (tous les anciens fichiers fusionnés puis supprimés).


## Nouveau
* `app/core/Lang.php`, `app/lang/{fr,en}/`, `LangController` : système multilingue (voir `app/lang/README.md`).
* Paramètres : langue (liste automatique des langues installées), devise USD/CDF, position du symbole, fuseau RDC.
* Classes : champ **cycle** (maternel / primaire / secondaire).
* Élèves : lieu de naissance, nationalité, numéro PERM. Nom / Postnom / Prénom (colonnes first/middle/last_name).
* `database/sql/*.sql` régénérés : classes, divisions, ~380 cours, mentions, frais, examens, jours fériés RDC.

## Corrigé au passage (manques du projet d'origine)
* `DEMO_MODE` n'était pas défini dans `config.php` (erreur fatale sur sauvegarde/restauration).
* Colonnes `official_id` (élèves, enseignants, parents) et table `official_id_counters` absentes du SQL.
* Tables du module Finance (`finance.sql`) absentes de `database.sql` : intégrées.
* Paramètres : les deux formulaires s'écrasaient mutuellement ; seuls les champs envoyés sont maintenant enregistrés.
* Mode de paiement `mobile_money` ajouté à la table `payments`.

## Supprimé
* Références somaliennes (Wadani, Mogadiscio, téléphone/e-mail codés en dur du site public, migration `somali_result_format.sql`).
* `database/sql.zip` (ancienne copie du SQL).
