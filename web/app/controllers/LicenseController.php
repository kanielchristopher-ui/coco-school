<?php
/**
 * ============================================================
 *  LicenseController — page « Licence » (administrateurs) :
 *  état, validité, niveaux couverts, plafonds d'élèves, installation d'une nouvelle clé.
 * ============================================================
 */

declare(strict_types=1);

use CocoCore\Exceptions\LicenseException;

class LicenseController extends Controller
{
    /** Réservé aux administrateurs ; accessible même quand la licence est verrouillée. */
    public function checkAuth(): bool
    {
        Auth::requireRole(['super_admin', 'admin']);
        return true;
    }

    public function index(): void
    {
        $db = Database::getInstance();
        $check = LicenseGate::check();
        $usage = [];
        foreach ($db->fetchAll("SELECT c.cycle, COUNT(s.id) AS total FROM classes c LEFT JOIN students s ON s.class_id = c.id AND s.status = 'active' GROUP BY c.cycle") as $r) {
            $usage[$r['cycle']] = (int) $r['total'];
        }
        $history = [];
        try {
            $history = $db->fetchAll('SELECT license_id, holder, valid_from, valid_until, status, installed_at FROM licenses ORDER BY id DESC LIMIT 10');
        } catch (Throwable $e) {
            // tables absentes : migration 0004 non appliquée
        }
        $this->render('license/index', ['check' => $check, 'usage' => $usage, 'history' => $history, 'technicalError' => LicenseGate::technicalError()]);
    }

    public function install(): void
    {
        CSRF::check();
        $token = trim((string) ($_POST['token'] ?? ''));
        if ($token === '') {
            Flash::set('error', 'Please paste a license key.');
            $this->redirect('license/index');
        }
        try {
            $check = LicenseGate::core()->license()->install($token, Auth::id());
            ActivityLog::log('license', 'license', 'License installed: ' . ($check->license?->id ?? ''));
            Flash::set('success', 'License installed successfully.');
        } catch (LicenseException $e) {
            Flash::set('error', t('Installation refused: :x', ['x' => $e->getMessage()]));
        } catch (Throwable $e) {
            Flash::set('error', t('Installation failed: :x', ['x' => $e->getMessage()]));
        }
        $this->redirect('license/index');
    }
}
