<?php
/**
 * ============================================================
 *  SettingsController
 *  School information, logo upload, database backup & restore,
 *  and general preferences.
 * ============================================================
 */

declare(strict_types=1);

class SettingsController extends Controller
{
    protected Setting $setting;

    public function __construct()
    {
        parent::__construct();
        $this->setting = new Setting();
    }

    public function index(): void
    {
        Auth::requirePermission('settings.manage');

        $keys = [
            'school_name', 'school_short_name', 'school_address', 'school_phone',
            'school_email', 'school_website', 'school_motto', 'principal_name',
            'academic_year', 'school_logo', 'school_favicon', 'currency',
            'currency_symbol', 'currency_position', 'timezone', 'language', 'academic_session_id',
            'fee_due_days', 'attendance_cutoff_time', 'enable_transport_module',
        ];

        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->setting->get($key);
        }

        $this->render('settings/index', [
            'values'   => $values,
            'sessions' => Database::getInstance()->fetchAll('SELECT * FROM academic_sessions ORDER BY name DESC'),
        ]);
    }

    /**
     * Save general settings.
     */
    public function save(): void
    {
        CSRF::check();
        Auth::requirePermission('settings.manage');

        $fields = [
            'school_name', 'school_short_name', 'school_address', 'school_phone',
            'school_email', 'school_website', 'school_motto', 'principal_name',
            'academic_year', 'currency', 'currency_symbol', 'currency_position',
            'timezone', 'language', 'academic_session_id', 'fee_due_days',
            'attendance_cutoff_time',
        ];

        foreach ($fields as $field) {
            // Deux formulaires (Général / Préférences) postent vers cette action :
            // on ne touche qu'aux champs réellement envoyés (sinon l'autre
            // formulaire effaçait les réglages qu'il ne contenait pas).
            if (!array_key_exists($field, $_POST)) {
                continue;
            }
            $value = trim((string) $_POST[$field]);

            // La langue doit être l'une des langues installées (app/lang/<code>).
            if ($field === 'language') {
                if (!isset(Lang::available()[$value])) {
                    continue;
                }
                // L'administrateur voit tout de suite le changement.
                $_SESSION['lang'] = $value;
            }
            if ($field === 'currency_position' && !in_array($value, ['before', 'after'], true)) {
                $value = 'after';
            }
            $this->setting->set($field, $value);
        }

        if (array_key_exists('enable_transport_module', $_POST)) {
            $this->setting->set('enable_transport_module', isset($_POST['enable_transport_module']) ? '1' : '0');
        }

        $logo = $this->uploadLogo();
        if ($logo !== null) {
            $this->setting->set('school_logo', $logo);
        }

        $favicon = $this->uploadFavicon();
        if ($favicon !== null) {
            $this->setting->set('school_favicon', $favicon);
        }

        $this->logActivity('update', 'settings', 'Updated school settings');
        Flash::set('success', 'Settings saved successfully.');
        $this->redirect('settings/index');
    }

    /**
     * Backup the database to a SQL file download.
     */
    public function backup(): void
    {
        Auth::requirePermission('settings.manage');

        if (DEMO_MODE) {
            Flash::set('error', 'Database backup is disabled in demo mode.');
            $this->redirect('settings/index');
        }

        global $dbConfig;

        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $dbConfig['host'], $dbConfig['port'], $dbConfig['dbname']),
                $dbConfig['username'],
                $dbConfig['password']
            );
        } catch (PDOException $e) {
            Flash::set('error', 'Backup failed: ' . $e->getMessage());
            $this->redirect('settings/index');
        }

        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        $sql = "-- School Management System Database Backup\n-- Date: " . date('Y-m-d H:i:s') . "\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $sql .= "-- Table: $table\nDROP TABLE IF EXISTS `$table`;\n";
            $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
            $sql .= $create['Create Table'] . ";\n\n";

            $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $cols = array_map(fn($c) => '`' . str_replace('`', '``', $c) . '`', array_keys($row));
                $vals = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), array_values($row));
                $sql .= "INSERT INTO `$table` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ");\n";
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="backup_' . date('Ymd_His') . '.sql"');
        echo $sql;
        exit;
    }

    /**
     * Restore the database from an uploaded SQL file.
     */
    public function restore(): void
    {
        CSRF::check();
        Auth::requirePermission('settings.manage');

        if (DEMO_MODE) {
            Flash::set('error', 'Database restore is disabled in demo mode.');
            $this->redirect('settings/index');
        }

        if (empty($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
            Flash::set('error', 'Please select a valid SQL backup file.');
            $this->redirect('settings/index');
        }

        $content = file_get_contents($_FILES['backup_file']['tmp_name']);
        if ($content === false || trim($content) === '') {
            Flash::set('error', 'Backup file is empty or unreadable.');
            $this->redirect('settings/index');
        }

        global $dbConfig;
        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $dbConfig['host'], $dbConfig['port'], $dbConfig['dbname']),
                $dbConfig['username'],
                $dbConfig['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $pdo->exec($content);
        } catch (Throwable $e) {
            Flash::set('error', 'Restore failed: ' . $e->getMessage());
            $this->redirect('settings/index');
        }

        $this->logActivity('restore', 'settings', 'Database restored from backup');
        Flash::set('success', 'Database restored successfully.');
        $this->redirect('settings/index');
    }

    /**
     * Upload the school logo.
     */
    protected function uploadLogo(): ?string
    {
        if (empty($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $file = $_FILES['logo'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_IMAGE_TYPES, true) || $file['size'] > MAX_UPLOAD_SIZE) {
            Flash::set('warning', 'Invalid logo file.');
            return null;
        }

        $dir = UPLOAD_PATH . '/settings';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = 'logo_' . date('Ymd_His') . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
            return null;
        }
        return 'uploads/settings/' . $filename;
    }

    /**
     * Upload the school favicon.
     */
    protected function uploadFavicon(): ?string
    {
        if (empty($_FILES['favicon']) || $_FILES['favicon']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $file = $_FILES['favicon'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['png', 'gif', 'ico'];
        if (!in_array($ext, $allowed, true) || $file['size'] > MAX_UPLOAD_SIZE) {
            Flash::set('warning', 'Invalid favicon file (png, gif or ico allowed).');
            return null;
        }

        $dir = UPLOAD_PATH . '/settings';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = 'favicon_' . date('Ymd_His') . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
            return null;
        }
        return 'uploads/settings/' . $filename;
    }
}
