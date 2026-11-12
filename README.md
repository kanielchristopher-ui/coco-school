# COCO SCHOOL — gestion scolaire RDC, construite autour de COCO SCHOOL CORE

```
core/         moteur métier indépendant (PHP 8.1) : sécurité, auth, notation, bulletins, finance, base, plugins
web/          application Web cliente du Core (maternel, primaire, secondaire ; FR/EN ; 100 % hors ligne)
database/     migrations versionnées, données de démo, fichier SQL unique, sauvegarde/restauration
plugins/      extensions CCPLG (modèles de bulletins signés)
tests/        php tests/run.php          tools/  audit.py, migrate.php
documentation/  audit complet, architecture, décisions, API, synchronisation LAN, guides
```

**Démarrer** : `documentation/INSTALLATION.md` · **État réel du projet** : `PROJECT_STATUS.md` · **À faire** : `TASKS.md` · **Règles de développement** : `CLAUDE.md`.

> Le code a été écrit sans pouvoir exécuter PHP : lancez d'abord `php tests/run.php`.

---

# Application Web (rappel des fonctionnalités)

# Système de Gestion Scolaire — RDC

Application web complète de gestion d'école (**PHP 8 natif en MVC, sans framework**, **MySQL**, **Bootstrap 5**),
adaptée au système éducatif de la **République démocratique du Congo** :
**maternel, primaire et secondaire** (Éducation de base + Humanités).

> Le code, la logique métier et la structure d'origine sont conservés. L'adaptation porte sur les
> données (classes, cours, mentions, frais, identifiants), les libellés et le **système multilingue**.

## Ce qui est adapté à la RDC

| Domaine | Adaptation |
|---|---|
| Cycles | Maternel (1ère–3ème), Primaire (1ère–6ème), Secondaire : 7ème–8ème Éducation de base (CTEB) + Humanités (1ère–4ème) avec options (Biologie-Chimie, Math-Physique, Pédagogie générale, Commerciale et gestion, Littéraire, Électricité…) |
| Classes | Champ **Cycle** sur chaque classe ; les *sections* deviennent des **divisions** (A, B, C…) |
| Élèves | **Nom / Postnom / Prénom**, lieu de naissance, nationalité, **numéro PERM**, province, commune |
| Évaluations | Périodes, examens de semestre/trimestre, TENAFEP blanc, Examen d'État blanc |
| Mentions | Excellent, Très bien, Bien, Assez bien, Satisfaisant, Échec — **seuil de réussite 50 %** (modifiable dans « Barème des notes ») |
| Année scolaire | Septembre → juillet (ex. 2026 - 2027) |
| Frais | Minerval, frais d'inscription, frais d'examen, carte & bulletin, tenue ; devise **USD ou CDF (FC)** ; paiement **Mobile Money** |
| Comptes de test | `prefet`, `proviseur`, `discipline`, `cashier` (mot de passe `password`) |
| Régional | Fuseau Africa/Kinshasa ou Africa/Lubumbashi, téléphones +243, jours fériés RDC |
| Identifiants | ELV-0001 (élève), ENS-0001 (enseignant), PAR-0001 (parent) |

## Fonctionnement 100 % hors ligne

Bootstrap, Font Awesome, graphiques et calendrier sont fournis **en local** (`public/assets/vendor`,
`public/assets/js/local-ui.js`) ; polices = polices du système. Aucune requête vers internet.

## Une seule connexion, plusieurs rôles

Une page de connexion unique pour tous. Chaque compte arrive sur son espace :

| Rôle | Accès principal |
|---|---|
| Super admin / Administrateur | tout le système |
| **Préfet** (chef d'établissement) | tout sauf utilisateurs et paramètres techniques ; vérifie et publie les résultats |
| **Directeur des études** (proviseur) | classes, cours, examens, barème, vérification/publication des résultats, passages de classe |
| **Directeur de discipline** | présences élèves/enseignants, **incidents et sanctions** (nouveau module), calendrier |
| **Caissière** | frais, perception, reçus, dépenses, paie, rapports financiers |
| Enseignant (maternelle, primaire, secondaire) | ses classes, cours, appel, saisie des points ; niveau défini sur la fiche |
| Élève / Parent | ses résultats, présences, frais, enfants |

## Bulletins périodiques (fusion avec Coco School)

Module **Bulletins** : saisie des points par période et examen → génération → validation → publication.

* Schémas RDC : **maternel/primaire = 3 trimestres** (2 périodes + examen + total), **secondaire = 2 semestres** + total général.
  Maxima et pondérations explicites et modifiables ; chaque modification crée une **nouvelle version**.
* Enseignant : saisit les points de **ses** cours. Directeur d'école (maternel/primaire) ou préfet (secondaire) : valide et publie ;
  le directeur des études (proviseur) peut valider au secondaire. Nouveau rôle **Directeur d'école** (`director`).
* Parents et élèves ne voient que les bulletins **publiés** qui leur appartiennent. Un bulletin publié ne change jamais
  silencieusement : instantané enregistré, corrections = nouvelle version, journal d'audit complet.
* Modèles de bulletin : modèle « Classique » intégré + extensions **.ccplg** signées (HTML/CSS déclaratifs, rien d'exécuté). Voir `docs/CCPLG_EXTENSIONS.md`.
* Impression / PDF depuis le navigateur. Tests : `php tests/bulletins/BulletinEngineTest.php`.

## Langues

* Français par défaut. **Paramètres → Langue** : choisir la langue du système (Français, English, …).
* Un sélecteur **FR | EN** est aussi disponible sur la page de connexion, le site public et la barre du haut
  (choix personnel pour la session).
* **Ajouter une langue** (lingala, swahili…) : copier `app/lang/fr/` vers `app/lang/<code>/`, modifier
  `meta.php`, traduire les fichiers `strings_*.php`. Elle apparaît automatiquement dans les menus.
  Voir `app/lang/README.md`.

## Installation

Voir **[INSTALL.md](INSTALL.md)**. Résumé : importer le fichier unique `database/school_management.sql` (création de la base + tables + données de test), régler
`app/config/database.php` et `BASE_URL` dans `app/config/config.php`, puis `php -S localhost:8000 -t public`.

## Comptes de test (à changer immédiatement)

| Rôle | Identifiant | Mot de passe |
|---|---|---|
| Administrateur | `admin@school.com` | `Admin123` |
| Enseignant | `teacher@school.com` | `Teacher123` |
| Élève | `student@school.com` | `Student123` |

Les comptes `admin`, `teacher1`, `student1`, `parent1` ont le mot de passe `password`.
En production : `APP_DEBUG` à `false`, et ne laissez que `public/` accessible depuis le web.

## Structure

```
app/config    configuration         app/lang     langues (fr, en, …)
app/core      noyau (Router, Auth, Lang, Helper…)
app/controllers · app/models · app/views
database      school_management.sql (fichier SQL unique)
public        point d'entrée, css/js/images, uploads
```
