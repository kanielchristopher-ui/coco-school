<?php

// Application
define('APP_NAME', 'Système de Gestion Scolaire');
define('APP_VERSION', '1.0.0');
define('APP_DEBUG', true);

// URL
define('BASE_URL', 'http://localhost:8000');

// Regional settings (RDC)
// Africa/Kinshasa = UTC+1 (Kinshasa, Kongo-Central, Équateur...)
// Africa/Lubumbashi = UTC+2 (Haut-Katanga, Kivu, Ituri...)
define('APP_TIMEZONE', 'Africa/Kinshasa');

// Langue par défaut (code du dossier app/lang/<code>).
// Modifiable ensuite dans Paramètres > Langue.
define('DEFAULT_LANG', 'fr');

// Sessions
define('SESSION_NAME', 'SCHOOL_MANAGEMENT_SESSION');
define('SESSION_TIMEOUT', 1800);

// CSRF
define('CSRF_TOKEN_NAME', 'school_csrf_token');

// Pagination
define('PAGINATION_LIMIT', 10);

// Password security
define('MIN_PASSWORD_LENGTH', 8);

// Mode démo : true = sauvegarde/restauration de la base désactivées.
// (Cette constante manquait : SettingsController l'utilise.)
define('DEMO_MODE', false);

// Extensions de bulletins (.ccplg) : clé PUBLIQUE de confiance (Ed25519, base64).
// Générez la paire avec : php tools/ccplg_keygen.php  — gardez la clé privée hors du serveur.
// Vide = aucune extension ne peut être installée.
define('CCPLG_TRUSTED_PUBLIC_KEY', '');
define('CCPLG_APPLICATION', 'Coco School');

const MAX_UPLOAD_SIZE = 5*1024*1024;
define('ALLOWED_IMAGE_TYPES',['image/png','image/jpg','image/webp']);
