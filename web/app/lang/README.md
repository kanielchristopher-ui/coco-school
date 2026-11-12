# Langues / Languages

Chaque langue est un dossier : `app/lang/<code>/`

    fr/meta.php        nom, séparateurs de nombres, mois, jours, formats de date
    fr/strings_*.php   dictionnaires  'Texte anglais' => 'Traduction'

Le texte anglais du code sert de clé. Les clés avec `:nom` sont des modèles
(`'Welcome back, :name!' => 'Bon retour, :name !'`).

## Ajouter une langue (ex. lingala `ln`, swahili `sw`)

1. Copier `fr/` en `ln/`.
2. Modifier `ln/meta.php` (`name`, `native`, mois, jours, séparateurs).
3. Traduire les valeurs des fichiers `strings_*.php`.
4. C'est tout : la langue apparaît dans **Paramètres > Langue** et dans le
   sélecteur de langue (page de connexion, site public, barre du haut).

Aide : en mode debug (`APP_DEBUG = true`), les textes non traduits sont notés dans
`storage/translations/missing_<code>.json`.
