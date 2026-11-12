<?php
/**
 * ============================================================
 *  Lang
 *  Moteur de traduction (i18n) de l'application.
 *
 *  Principe
 *  --------
 *  - Le texte source des vues est l'ANGLAIS. Il sert de clé.
 *  - Chaque langue est un dossier dans app/lang/<code>/ :
 *        meta.php      -> nom de la langue, mois, jours, formats…
 *        *.php         -> dictionnaires  ['Texte anglais' => 'Traduction']
 *  - Pour AJOUTER une langue : copier le dossier app/lang/fr vers
 *    app/lang/<nouveau_code>, modifier meta.php, traduire les
 *    dictionnaires. Elle apparaît ensuite automatiquement dans
 *    Paramètres > Langue et dans le sélecteur de langue.
 *
 *  Deux mécanismes complémentaires :
 *    1. t('Texte', ['nom' => 'valeur'])  : traduction explicite en PHP.
 *    2. Lang::filter()                   : filtre de sortie qui traduit les
 *       textes HTML, placeholder, title, alt, confirm()… de toutes les
 *       pages (donc aucune vue n'a besoin d'être modifiée).
 *
 *  Les clés contenant :nom (ex. 'Welcome back, :name') servent de
 *  modèles pour les textes qui contiennent des valeurs dynamiques.
 * ============================================================
 */

declare(strict_types=1);

class Lang
{
    /** Langue utilisée si rien d'autre n'est configuré. */
    public const FALLBACK = 'fr';

    private static string $locale = self::FALLBACK;
    private static array $meta = [];
    private static array $strings = [];
    /** Modèles avec :variables, retrouvés par leur texte exact (appels t('…:n…', [...]) ). */
    private static array $templates = [];
    /** @var array<int, array{0:string,1:string}> [regex, modèle] */
    private static array $patterns = [];
    private static ?array $available = null;
    private static array $missing = [];
    private static bool $shutdownRegistered = false;

    // ------------------------------------------------------------
    //  Initialisation
    // ------------------------------------------------------------

    /**
     * Détermine la langue active :
     *  1. choix de l'utilisateur (session)  2. langue de l'école (Paramètres)
     *  3. DEFAULT_LANG (config)             4. français
     */
    public static function init(): void
    {
        $available = self::available();
        $code = null;

        $choice = $_SESSION['lang'] ?? null;
        if (is_string($choice) && isset($available[$choice])) {
            $code = $choice;
        }

        if ($code === null) {
            try {
                $school = settings('language', '');
            } catch (Throwable $e) {
                $school = '';
            }
            if ($school !== '' && isset($available[$school])) {
                $code = $school;
            }
        }

        if ($code === null) {
            $default = defined('DEFAULT_LANG') ? (string) DEFAULT_LANG : self::FALLBACK;
            $code = isset($available[$default]) ? $default : (array_key_first($available) ?? 'en');
        }

        self::set($code);
    }

    /**
     * Liste des langues installées : code => meta (name, native, …).
     */
    public static function available(): array
    {
        if (self::$available !== null) {
            return self::$available;
        }
        $list = [];
        foreach (glob(APP_PATH . '/lang/*/meta.php') ?: [] as $file) {
            $code = basename(dirname($file));
            if (!preg_match('/^[a-z]{2,3}(_[A-Z]{2})?$/', $code)) {
                continue;
            }
            $meta = require $file;
            if (is_array($meta)) {
                $list[$code] = $meta;
            }
        }
        ksort($list);
        return self::$available = $list;
    }

    /**
     * Active une langue et charge ses dictionnaires.
     */
    public static function set(string $code): void
    {
        $available = self::available();
        if (!isset($available[$code])) {
            $code = isset($available[self::FALLBACK]) ? self::FALLBACK : (string) (array_key_first($available) ?? 'en');
        }

        self::$locale   = $code;
        self::$meta     = $available[$code] ?? [];
        self::$strings  = [];
        self::$patterns = [];
        self::$templates = [];

        foreach (glob(APP_PATH . '/lang/' . $code . '/*.php') ?: [] as $file) {
            if (basename($file) === 'meta.php') {
                continue;
            }
            $dict = require $file;
            if (is_array($dict)) {
                foreach ($dict as $key => $value) {
                    self::$strings[(string) $key] = (string) $value;
                }
            }
        }

        // Les clés avec :variable deviennent des modèles (expressions régulières).
        foreach (self::$strings as $key => $value) {
            if (preg_match('/:[a-z_]+/i', $key)) {
                self::$templates[$key] = $value;
                $regex = preg_quote($key, '~');
                // :n, :a, :b, :c, :y = nombres ; les autres = texte quelconque
                $regex = preg_replace_callback('/\\\\:([a-z_]+)/i', function (array $m): string {
                    $numeric = in_array($m[1], ['n', 'a', 'b', 'c', 'y'], true);
                    return '(?P<' . $m[1] . '>' . ($numeric ? '\\d+(?:[.,]\\d+)?' : '.+?') . ')';
                }, $regex);
                self::$patterns[] = ['~^' . $regex . '$~su', $value];
                unset(self::$strings[$key]);
            }
        }
    }

    public static function locale(): string
    {
        return self::$locale;
    }

    public static function meta(string $key, mixed $default = null): mixed
    {
        return self::$meta[$key] ?? $default;
    }

    /** Direction du texte (ltr / rtl) pour les langues futures (arabe…). */
    public static function dir(): string
    {
        return (string) self::meta('dir', 'ltr');
    }

    // ------------------------------------------------------------
    //  Traduction
    // ------------------------------------------------------------

    /**
     * Traduit une chaîne. Les paramètres remplacent :nom dans le résultat.
     */
    public static function t(string $key, array $params = []): string
    {
        $text = self::$templates[$key] ?? self::lookup($key);
        if ($text === null) {
            self::logMissing($key);
            $text = $key;
        }
        foreach ($params as $name => $value) {
            $text = str_replace(':' . $name, (string) $value, $text);
        }
        return $text;
    }

    /**
     * Cherche la traduction exacte puis les modèles. null si inconnue.
     */
    private static function lookup(string $key): ?string
    {
        if (isset(self::$strings[$key])) {
            return self::$strings[$key];
        }
        foreach (self::$patterns as [$regex, $template]) {
            if (preg_match($regex, $key, $m)) {
                $out = $template;
                foreach ($m as $name => $value) {
                    if (is_string($name)) {
                        $inner = self::$strings[$value] ?? $value;
                        $out = str_replace(':' . $name, $inner, $out);
                    }
                }
                return $out;
            }
        }
        return null;
    }

    // ------------------------------------------------------------
    //  Filtre de sortie (traduction automatique du HTML)
    // ------------------------------------------------------------

    /**
     * Callback de ob_start() : traduit la page complète avant envoi.
     */
    public static function filter(string $buffer, int $phase = 0): string
    {
        if ($buffer === '' || (empty(self::$strings) && empty(self::$patterns))) {
            return $buffer;
        }
        // On ne touche pas au JSON, CSV, PDF, téléchargements…
        foreach (headers_list() as $header) {
            if (stripos($header, 'content-type:') === 0 && stripos($header, 'html') === false) {
                return $buffer;
            }
            if (stripos($header, 'content-disposition:') === 0 && stripos($header, 'attachment') !== false) {
                return $buffer;
            }
        }
        return self::translateHtml($buffer);
    }

    public static function translateHtml(string $html): string
    {
        $parts = preg_split(
            '~(<script\b[^>]*>.*?</script>|<style\b[^>]*>.*?</style>|<!--.*?-->|<[^>]+>)~is',
            $html,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );
        if ($parts === false) {
            return $html;
        }

        $out = '';
        foreach ($parts as $i => $part) {
            if ($part === '') {
                continue;
            }
            if ($i % 2 === 0) {
                $out .= self::translateText($part);
                continue;
            }
            // Élément balisé (tag, script, style, commentaire)
            if ($part[0] === '<' && isset($part[1]) && ($part[1] === '!' || stripos($part, '<script') === 0 || stripos($part, '<style') === 0)) {
                $out .= $part;
            } else {
                $out .= self::translateTag($part);
            }
        }
        return $out;
    }

    private static function translateText(string $text): string
    {
        if (!preg_match('/\S/', $text)) {
            return $text;
        }
        if (!preg_match('/^(\s*)(.*?)(\s*)$/s', $text, $m)) {
            return $text;
        }
        $decoded = html_entity_decode($m[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $key = trim((string) preg_replace('/\s+/u', ' ', $decoded));
        if ($key === '') {
            return $text;
        }
        $tr = self::lookup($key);
        if ($tr === null) {
            self::logMissing($key, true);
            return $text;
        }
        return $m[1] . htmlspecialchars($tr, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8') . $m[3];
    }

    private static function translateTag(string $tag): string
    {
        if (strpos($tag, '=') === false) {
            return $tag;
        }

        // Attributs visibles : placeholder, title, alt, aria-label…
        $tag = (string) preg_replace_callback(
            '/(?<![\w-])(placeholder|title|alt|aria-label|label|data-confirm|data-title|data-bs-title|data-bs-original-title|data-message)=(["\'])(.*?)\2/s',
            function (array $m): string {
                return $m[1] . '=' . $m[2] . self::translateAttr($m[3]) . $m[2];
            },
            $tag
        );

        // Boutons <input type="submit" value="Save">
        if (preg_match('/<input\b[^>]*\btype=["\'](submit|button|reset)["\']/i', $tag)) {
            $tag = (string) preg_replace_callback(
                '/(?<![\w-])value=(["\'])(.*?)\1/s',
                function (array $m): string {
                    return 'value=' . $m[1] . self::translateAttr($m[2]) . $m[1];
                },
                $tag
            );
        }

        // confirm('Message ?') dans onclick / onsubmit
        if (stripos($tag, 'confirm(') !== false) {
            $tag = (string) preg_replace_callback(
                '/confirm\(\s*\'((?:[^\'\\\\]|\\\\.)*)\'/s',
                function (array $m): string {
                    $plain = stripslashes($m[1]);
                    $tr = self::lookup(trim((string) preg_replace('/\s+/u', ' ', $plain)));
                    if ($tr === null) {
                        self::logMissing($plain, true);
                        return $m[0];
                    }
                    return "confirm('" . addcslashes(htmlspecialchars($tr, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8'), "'\\") . "'";
                },
                $tag
            );
        }

        return $tag;
    }

    private static function translateAttr(string $value): string
    {
        if (trim($value) === '') {
            return $value;
        }
        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $key = trim((string) preg_replace('/\s+/u', ' ', $decoded));
        $tr = self::lookup($key);
        if ($tr === null) {
            self::logMissing($key, true);
            return $value;
        }
        return htmlspecialchars($tr, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Textes utilisés par le JavaScript (confirmations, libellés de graphiques).
     * Exposés dans window.I18N ; JS appelle tr('texte anglais').
     */
    public static function jsStrings(): array
    {
        $keys = [
            'Are you sure you want to delete this record? This action cannot be undone.',
            'Yes, delete', 'Permanently delete student', 'Yes, delete student',
            'This will remove the student profile, their login account and notifications, attendance records, exam results, student fees and payments, fee collections, and parent-student links. This action cannot be undone.',
            'Permanently delete teacher', 'Yes, delete teacher',
            'This will remove the teacher profile, their login account and notifications, class and subject assignments, payroll records, teacher attendance, and results they created. This action cannot be undone.',
        ];
        $out = [];
        foreach ($keys as $key) {
            if (isset(self::$strings[$key])) {
                $out[$key] = self::$strings[$key];
            }
        }
        return $out;
    }

    // ------------------------------------------------------------
    //  Dates, nombres
    // ------------------------------------------------------------

    /**
     * Équivalent de date() avec noms de mois/jours traduits et formats
     * d'affichage adaptés à la langue (voir 'formats' dans meta.php).
     */
    public static function date(string $format, ?int $timestamp = null): string
    {
        $timestamp ??= time();
        $formats = self::meta('formats', []);
        if (is_array($formats) && isset($formats[$format])) {
            $format = (string) $formats[$format];
        }

        $months      = self::meta('months');
        $monthsShort = self::meta('months_short');
        $days        = self::meta('days');
        $daysShort   = self::meta('days_short');

        if (!$months && !$monthsShort && !$days && !$daysShort) {
            return date($format, $timestamp);
        }

        $out = '';
        $len = strlen($format);
        for ($i = 0; $i < $len; $i++) {
            $c = $format[$i];
            if ($c === '\\') {
                $out .= $c . ($format[$i + 1] ?? '');
                $i++;
                continue;
            }
            $word = null;
            if ($c === 'F' && is_array($months)) {
                $word = $months[(int) date('n', $timestamp) - 1] ?? null;
            } elseif ($c === 'M' && is_array($monthsShort)) {
                $word = $monthsShort[(int) date('n', $timestamp) - 1] ?? null;
            } elseif ($c === 'l' && is_array($days)) {
                $word = $days[(int) date('w', $timestamp)] ?? null;
            } elseif ($c === 'D' && is_array($daysShort)) {
                $word = $daysShort[(int) date('w', $timestamp)] ?? null;
            }
            $out .= $word !== null ? (string) preg_replace('/(.)/su', '\\\\$1', $word) : $c;
        }
        return date($out, $timestamp);
    }

    /**
     * number_format() avec les séparateurs de la langue active.
     */
    public static function number(float|int|string|null $value, int $decimals = 0): string
    {
        return number_format(
            (float) $value,
            $decimals,
            (string) self::meta('decimal', '.'),
            (string) self::meta('thousands', ',')
        );
    }

    // ------------------------------------------------------------
    //  Aide à la traduction : journal des textes non traduits
    // ------------------------------------------------------------

    private static function logMissing(string $text, bool $fromHtml = false): void
    {
        if (!defined('APP_DEBUG') || APP_DEBUG !== true || self::$locale === 'en') {
            return;
        }
        // On ignore ce qui ne ressemble pas à une phrase (nombres, symboles, noms propres isolés…).
        if (!preg_match('/^[A-Z][A-Za-z][A-Za-z0-9 ,.\'’&\/()%:?!-]{1,150}$/', $text)) {
            return;
        }
        self::$missing[$text] = true;
        if (!self::$shutdownRegistered) {
            self::$shutdownRegistered = true;
            register_shutdown_function([self::class, 'flushMissing']);
        }
    }

    public static function flushMissing(): void
    {
        if (empty(self::$missing)) {
            return;
        }
        $dir = ROOT_PATH . '/storage/translations';
        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            return;
        }
        $file = $dir . '/missing_' . self::$locale . '.json';
        $known = [];
        if (is_file($file)) {
            $known = json_decode((string) file_get_contents($file), true) ?: [];
        }
        $merged = array_values(array_unique(array_merge($known, array_keys(self::$missing))));
        sort($merged);
        @file_put_contents($file, json_encode($merged, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    }
}
