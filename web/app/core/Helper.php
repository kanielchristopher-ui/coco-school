<?php
/**
 * ============================================================
 *  Helper
 *  Global helper functions used throughout the application.
 * ============================================================
 */

declare(strict_types=1);

if (!function_exists('e')) {
    /**
     * Escape output for safe HTML rendering (XSS protection).
     */
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    /**
     * Build an application URL.
     */
    function url(string $path = ''): string
    {
        return BASE_URL . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Build an asset URL.
     */
    function asset(string $path = ''): string
    {
        return BASE_URL . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('old')) {
    /**
     * Retrieve the previous input value (after a failed submission).
     */
    function old(string $key, mixed $default = ''): mixed
    {
        return Session::get('old_input', [])[$key] ?? $default;
    }
}

if (!function_exists('settings')) {
    /**
     * Get a setting value by key from the settings table.
     */
    function settings(string $key, string $default = ''): string
    {
        static $cache = null;

        if ($cache === null) {
            $rows = Database::getInstance()->fetchAll('SELECT setting_key, setting_value FROM settings');
            $cache = [];
            foreach ($rows as $row) {
                $cache[$row['setting_key']] = $row['setting_value'];
            }
        }

        return $cache[$key] ?? $default;
    }
}

if (!function_exists('currency')) {
    /**
     * Format a number as currency using the configured symbol.
     * Position of the symbol (before/after) comes from the setting
     * "currency_position" (default: after in French, before otherwise).
     */
    function currency(float $amount): string
    {
        $symbol   = settings('currency_symbol', '$');
        $decimals = (int) settings('currency_decimals', '2');
        $position = settings('currency_position', '');
        if ($position !== 'before' && $position !== 'after') {
            $position = Lang::locale() === 'en' ? 'before' : 'after';
        }
        $number = Lang::number($amount, $decimals);
        return $position === 'before' ? $symbol . $number : $number . "\u{00A0}" . $symbol;
    }
}

if (!function_exists('flash')) {
    /**
     * Display queued flash messages.
     */
    function flash(): void
    {
        Flash::display();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Output the CSRF hidden field.
     */
    function csrf_field(): string
    {
        return CSRF::field();
    }
}

if (!function_exists('auth')) {
    /**
     * Get the current Auth user id.
     */
    function auth(): int
    {
        return Auth::id();
    }
}

if (!function_exists('can')) {
    /**
     * Shorthand for permission checks in views.
     */
    function can(string $permission): bool
    {
        return Auth::can($permission);
    }
}

if (!function_exists('avatar')) {
    /**
     * Render a user avatar image or a placeholder initial.
     */
    function avatar(?string $photo, string $name, int $size = 40): string
    {
        if ($photo && is_file(ROOT_PATH . '/public/' . ltrim($photo, '/'))) {
            return '<img src="' . BASE_URL . '/' . e(ltrim($photo, '/')) . '" alt="' . e($name) . '" class="rounded-circle" width="' . $size . '" height="' . $size . '" style="object-fit:cover;">';
        }
        $initial = mb_strtoupper(mb_substr($name, 0, 1));
        return '<div class="avatar-circle bg-primary text-white d-inline-flex align-items-center justify-content-center rounded-circle" style="width:' . $size . 'px;height:' . $size . 'px;">' . e($initial) . '</div>';
    }
}

if (!function_exists('plural')) {
    /**
     * Return the singular or plural form of a word based on a count.
     */
    function plural(int $count, string $singular, string $plural): string
    {
        return $count === 1 ? $singular : $plural;
    }
}

if (!function_exists('studentName')) {
    /**
     * Build a student's full (three-part) name.
     */
    function studentName(array $student, string $default = '—'): string
    {
        $name = trim(implode(' ', array_filter([
            $student['first_name'] ?? '',
            $student['middle_name'] ?? '',
            $student['last_name'] ?? '',
        ])));
        return $name !== '' ? $name : $default;
    }
}

if (!function_exists('formatDate')) {
    /**
     * Format a database date for display.
     */
    function formatDate(?string $date, string $format = 'M d, Y'): string
    {
        if (!$date) {
            return '—';
        }
        $ts = strtotime($date);
        return $ts ? Lang::date($format, $ts) : '—';
    }
}

if (!function_exists('timeAgo')) {
    /**
     * Human-readable relative time.
     */
    function timeAgo(string $datetime): string
    {
        $now = new DateTime();
        $then = new DateTime($datetime);
        $diff = $now->diff($then);

        if ($diff->y > 0) return t(':n year(s) ago', ['n' => $diff->y]);
        if ($diff->m > 0) return t(':n month(s) ago', ['n' => $diff->m]);
        if ($diff->d > 0) return t(':n day(s) ago', ['n' => $diff->d]);
        if ($diff->h > 0) return t(':n hour(s) ago', ['n' => $diff->h]);
        if ($diff->i > 0) return t(':n minute(s) ago', ['n' => $diff->i]);
        return t('just now');
    }
}

if (!function_exists('gradeFor')) {
    /**
     * Return the grade for a percentage based on the stored grades
     * scale (falls back to the configured scale).
     */
    function gradeFor(float $percentage): array
    {
        static $bands = null;

        if ($bands === null) {
            try {
                $bands = Database::getInstance()->fetchAll(
                    'SELECT grade, min_percent, point, remarks FROM grades WHERE is_active = 1 ORDER BY min_percent DESC'
                );
            } catch (Throwable $e) {
                $bands = [];
            }
        }

        if (!empty($bands)) {
            foreach ($bands as $item) {
                if ($percentage >= (float) $item['min_percent']) {
                    return [
                        'grade'  => $item['grade'],
                        'point'  => (float) $item['point'],
                        'remarks'=> $item['remarks'] ?? '',
                    ];
                }
            }
            return ['grade' => 'Éch', 'point' => 0.0, 'remarks' => 'Fail'];
        }

        global $config;
        foreach (($config['grades'] ?? []) as $item) {
            if ($percentage >= $item['min']) {
                return $item;
            }
        }
        return ['grade' => 'Éch', 'point' => 0.0];
    }
}

if (!function_exists('statusBadge')) {
    /**
     * Render a Bootstrap badge for a status string.
     */
    function statusBadge(string $status): string
    {
        $map = [
            'active'     => 'success',
            'inactive'   => 'secondary',
            'suspended'  => 'danger',
            'paid'       => 'success',
            'pending'    => 'warning',
            'partial'    => 'info',
            'overdue'    => 'danger',
            'cancelled'  => 'secondary',
            'present'    => 'success',
            'absent'     => 'danger',
            'late'       => 'warning',
            'leave'      => 'info',
            'issued'     => 'warning',
            'returned'   => 'success',
            'lost'       => 'danger',
            'available'  => 'success',
            'full'       => 'danger',
            'maintenance'=> 'secondary',
            'published'  => 'success',
            'draft'      => 'secondary',
            'open'       => 'warning',
            'validated'  => 'info',
            'superseded' => 'secondary',
            'resolved'   => 'success',
            'submitted'  => 'primary',
            'approved'   => 'info',
            'returned'   => 'warning',
            'locked'     => 'danger',
            'completed'  => 'info',
            'upcoming'   => 'secondary',
            'active'     => 'success',
            'alumni'     => 'secondary',
            'transferred'=> 'info',
            'left'       => 'secondary',
            'occupied'   => 'danger',
            'in_stock'   => 'success',
            'low_stock'  => 'warning',
            'out_of_stock' => 'danger',
            'active2'    => 'success',
        ];
        $color = $map[$status] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . e(ucfirst(str_replace('_', ' ', $status))) . '</span>';
    }
}

// ============================================================
//  Internationalisation (voir app/core/Lang.php)
// ============================================================

if (!function_exists('t')) {
    /**
     * Translate a string (English source text is the key).
     * Example: t('Welcome back, :name', ['name' => $user]).
     */
    function t(string $key, array $params = []): string
    {
        return Lang::t($key, $params);
    }
}

if (!function_exists('lang')) {
    /**
     * Current language code (fr, en, …).
     */
    function lang(): string
    {
        return Lang::locale();
    }
}

if (!function_exists('ldate')) {
    /**
     * Localized date() (month/day names and display formats follow the language).
     */
    function ldate(string $format, ?int $timestamp = null): string
    {
        return Lang::date($format, $timestamp);
    }
}

if (!function_exists('nf')) {
    /**
     * Localized number_format() (decimal and thousands separators follow the language).
     */
    function nf(float|int|string|null $value, int $decimals = 0): string
    {
        return Lang::number($value, $decimals);
    }
}

if (!function_exists('lang_switcher')) {
    /**
     * Language switcher.
     *  - style "dropdown" : Bootstrap dropdown (application topbar)
     *  - style "inline"   : compact links FR | EN (login page, public website)
     * Renders nothing when only one language is installed.
     */
    function lang_switcher(string $style = 'inline', string $extraClass = ''): string
    {
        $languages = Lang::available();
        if (count($languages) < 2) {
            return '';
        }
        $current = Lang::locale();

        if ($style === 'dropdown') {
            $html  = '<div class="dropdown ' . e($extraClass) . '">';
            $html .= '<button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" title="Language"><i class="fas fa-language me-1"></i>' . e(strtoupper($current)) . '</button>';
            $html .= '<ul class="dropdown-menu dropdown-menu-end">';
            foreach ($languages as $code => $info) {
                $label  = $info['native'] ?? $info['name'] ?? $code;
                $active = $code === $current ? ' active' : '';
                $html  .= '<li><a class="dropdown-item' . $active . '" href="' . e(url('lang/change/' . $code)) . '">' . e($label) . '</a></li>';
            }
            return $html . '</ul></div>';
        }

        $links = [];
        foreach ($languages as $code => $info) {
            $label = $info['native'] ?? $info['name'] ?? $code;
            $style = $code === $current ? 'font-weight:700;text-decoration:underline;' : 'opacity:.8;';
            $links[] = '<a href="' . e(url('lang/change/' . $code)) . '" title="' . e($label) . '" style="color:inherit;' . $style . '">' . e(strtoupper($code)) . '</a>';
        }
        return '<span class="lang-switcher ' . e($extraClass) . '" style="font-size:.85rem;">' . implode(' | ', $links) . '</span>';
    }
}

if (!function_exists('role_label')) {
    /**
     * Human label of a role slug (translated by the dictionary).
     */
    function role_label(string $slug): string
    {
        $map = [
            'super_admin' => 'Super Admin',
            'admin'       => 'Admin',
            'prefet'      => 'Prefect (School Head)',
            'proviseur'   => 'Director of Studies',
            'discipline'  => 'Discipline Director',
            'cashier'     => 'Cashier',
            'teacher'     => 'Teacher',
            'student'     => 'Student',
            'parent'      => 'Parent',
        ];
        return t($map[$slug] ?? ucfirst(str_replace('_', ' ', $slug)));
    }
}

if (!function_exists('theme_switcher')) {
    /**
     * Segmented control: Light / System / Dark (see public/assets/js/theme.js).
     */
    function theme_switcher(string $extraClass = ''): string
    {
        $items = ['light' => ['fa-sun', 'Light mode'], 'system' => ['fa-desktop', 'System mode'], 'dark' => ['fa-moon', 'Dark mode']];
        $html = '<div class="theme-switch ' . e($extraClass) . '" role="group" aria-label="' . e(t('Theme')) . '">';
        foreach ($items as $mode => [$icon, $label]) {
            $html .= '<button type="button" data-mode="' . $mode . '" title="' . e(t($label)) . '" aria-label="' . e(t($label)) . '"><i class="fas ' . $icon . '"></i></button>';
        }
        return $html . '</div>';
    }
}
