# Extensions CCPLG (modèles de bulletins)

Un fichier `.ccplg` est une archive ZIP : `plugin.json` (manifeste), les fichiers déclarés (HTML/CSS/images)
avec leur SHA-256, et `signature.ccsig` (signature Ed25519 du manifeste).

**Sécurité** : aucun PHP, JavaScript ni SQL n'est jamais exécuté. Les modèles sont des HTML/CSS déclaratifs
rendus avec échappement des variables (`SafeTemplateRenderer`). Parents et élèves ne peuvent rien installer.

## Utiliser les extensions
1. Générer une paire de clés : `python3 tools/ccplg_build.py --keygen` (ou `php tools/ccplg_keygen.php`).
2. Copier la clé **publique** dans `app/config/ccplg.php` (`trusted_public_key`). Garder la clé privée hors du serveur.
   (La clé fournie dans le dépôt est une clé de développement dont la clé privée n'existe pas : elle ne permet rien.)
3. Construire et signer un paquet : voir `python3 tools/ccplg_build.py --help` ou `php tools/ccplg_pack.php`.
4. Menu **Modèles de bulletins** : le directeur (maternel/primaire) ou le préfet (secondaire) installe le paquet.

Prérequis PHP : extensions `zip` et `sodium`.
