<?php
/**
 * ============================================================
 *  LicenseGate — pont entre l'application Web et le Core (CocoCore\Licensing).
 *  - Lit l'état de la licence (clé signée installée, ou essai de 30 jours).
 *  - Verrouille les écrans si la licence est invalide, passe en lecture seule si elle a expiré.
 *  - Vérifie le plafond d'élèves lors des créations.
 *  Aucune règle de licence ici : tout est dans core/Licensing.
 * ============================================================
 */

declare(strict_types=1);

use CocoCore\Bootstrap\Kernel;
use CocoCore\Exceptions\LicenseException;
use CocoCore\Licensing\LicenseCheck;

final class LicenseGate
{
    /** Contrôleurs toujours accessibles (connexion, langue, page de licence, site public). */
    private const EXEMPT = ['Auth', 'Lang', 'License', 'Website'];

    private static ?Kernel $core = null;
    private static ?LicenseCheck $check = null;
    private static bool $checked = false;
    private static ?string $technicalError = null;

    public static function core(): Kernel
    {
        if (self::$core === null) {
            $cfg = require CONFIG_PATH . '/database.php';
            self::$core = Kernel::boot([
                'host' => $cfg['host'], 'name' => $cfg['dbname'], 'user' => $cfg['username'],
                'pass' => $cfg['password'], 'port' => (int) ($cfg['port'] ?? 3306),
            ]);
            $override = CONFIG_PATH . '/license.php';
            if (is_file($override)) {
                $conf = require $override;
                if (!empty($conf['trusted_keys'])) {
                    self::$core->withTrustedKeys((array) $conf['trusted_keys']);
                }
            }
        }
        return self::$core;
    }

    /**
     * État de la licence ; null si le module de licence est indisponible (tables absentes : exécutez les migrations).
     * L'absence de l'extension sodium est BLOQUANTE (la signature ne peut pas être vérifiée).
     */
    public static function check(): ?LicenseCheck
    {
        if (self::$checked) {
            return self::$check;
        }
        self::$checked = true;
        try {
            self::$check = self::core()->license()->check();
        } catch (LicenseException $e) {
            self::$technicalError = $e->getMessage();
            if ($e->reason === 'sodium') {
                // sans libsodium la signature ne peut pas être vérifiée : on verrouille (la page Licence explique pourquoi)
                self::$check = new LicenseCheck(\CocoCore\Licensing\LicenseStatus::MALFORMED, null, null, null, $e->getMessage());
            }
        } catch (Throwable $e) {
            self::$technicalError = $e->getMessage();   // base sans migration 0004 : on ne bloque pas une installation existante
        }
        return self::$check;
    }

    public static function technicalError(): ?string
    {
        return self::$technicalError;
    }

    /** Appelé par le routeur avant chaque action. */
    public static function enforce(string $controller, string $method): void
    {
        if (in_array(preg_replace('/Controller$/', '', $controller), self::EXEMPT, true)) {
            return;
        }
        $check = self::check();
        if ($check === null) {
            return;
        }
        if ($check->status->isLocked()) {
            self::lockPage($check);
        }
        if (!$check->status->allowsWrite()) {
            $isWrite = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' || preg_match('/^(destroy|delete|remove|void|toggle|reset)/i', $method) === 1;
            if ($isWrite) {
                Flash::set('error', t($check->status->label()) . ' — ' . t('Read-only mode: contact your provider to renew the license.'));
                $back = $_SERVER['HTTP_REFERER'] ?? '';
                header('Location: ' . ($back !== '' ? $back : url('dashboard/index')));
                exit;
            }
        }
    }

    /** Vérifie le plafond d'élèves du niveau de la classe visée. Redirige avec un message si la licence l'interdit. */
    public static function guardStudentCreation(int $classId, string $redirect = 'students/create'): void
    {
        $check = self::check();
        if ($check === null || $classId <= 0) {
            return;
        }
        $row = Database::getInstance()->fetch('SELECT cycle FROM classes WHERE id = ?', [$classId]);
        if (!$row) {
            return;
        }
        try {
            self::core()->license()->requireQuota((string) $row['cycle']);
        } catch (LicenseException $e) {
            Flash::set('error', $e->getMessage());
            header('Location: ' . url($redirect));
            exit;
        }
    }

    private static function lockPage(?LicenseCheck $check, string $message = ''): never
    {
        http_response_code(403);
        (new View())->render('license/locked', ['check' => $check, 'message' => $message], 'layouts/auth_layout');
        exit;
    }
}
