# Architecture CCPLG et bulletins périodiques

## Périmètre
Le dépôt source est encore quasi vide (README seulement). Cette branche pose une base indépendante à intégrer au futur Core. Les bulletins annuels officiels existants ne sont pas reconstruits ici.

## Format CCPLG
Un fichier `.ccplg` est une archive ZIP avec manifeste JSON, configurations et templates. Le manifeste déclare chaque fichier avec son SHA-256. La signature détachée `signature.ccsig` authentifie les octets exacts de `manifest.json`. Le validateur reçoit la clé publique de confiance depuis la configuration serveur/école; la clé ne doit jamais provenir du paquet lui-même.

Structure type :
```
plugin.json
config/periods.json
config/calculations.json
templates/bulletin.html
templates/bulletin.css
assets/...
signature.ccsig
```
Le format est propriétaire par convention et par schéma, pas par le suffixe seul. Le contenu reste inspectable; la signature prouve l'origine et l'intégrité, pas le secret du code.

## Sécurité
- Refuser chemins absolus, `..`, liens symboliques, doublons, fichiers non déclarés, tailles excessives et extensions exécutables.
- N'exécuter aucun PHP, JS, shell ou SQL fourni par une extension.
- Les templates sont déclaratifs; les variables doivent être échappées et limitées à une liste blanche.
- Extraction vers un répertoire temporaire dédié, puis déplacement atomique.
- Vérifier les permissions côté serveur à chaque action; ne pas se fier à l'interface.

## Évaluations
Les schémas sont attachés à un établissement, un niveau et une année scolaire. Les modifications créent une nouvelle version; les anciens résultats et bulletins restent liés à la version utilisée.
Les maxima et règles de calcul doivent être explicitement configurés. Les totaux ne sont pas calculés par hypothèse d'égalité des périodes.

### RDC primaire / maternelle
3 trimestres. Chaque trimestre contient deux périodes et un examen, puis un total trimestriel :
- T1 : P1, P2, examen, total
- T2 : P3, P4, examen, total
- T3 : P5, P6, examen, total

### RDC secondaire
- S1 : P1, P2, examen, total semestre
- S2 : P3, P4, examen, total semestre
- Total général

Ces configurations sont des modèles de départ, modifiables par établissement.

## Rôles et visibilité
- Directeur : maternelle/primaire, installe et configure les extensions et modèles de son établissement.
- Préfet : secondaire, mêmes droits dans son établissement.
- Enseignant : saisit/corrige les notes dans les limites de ses affectations.
- Parent : consulte les bulletins publiés de ses enfants liés.
- Élève : consulte ses propres bulletins publiés.
- Aucun parent/élève ne charge ou ne crée une extension. La création de modèles est réservée aux responsables habilités.
Les bulletins sont visibles aux familles seulement après validation/publication.

## Claude / assistants de code
Lire `CLAUDE.md` puis seulement les fichiers nécessaires. Les extensions, règles et exemples sont JSON concis; les prompts ne doivent pas inclure tout le dépôt. Préférer des tâches bornées et des tests ciblés.
