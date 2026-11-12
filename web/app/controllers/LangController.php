<?php
/**
 * ============================================================
 *  LangController
 *  Permet à chaque utilisateur (même non connecté) de changer
 *  la langue d'affichage pour sa session :  /lang/change/fr
 *  La langue par défaut de l'école se règle dans Paramètres.
 * ============================================================
 */

declare(strict_types=1);

class LangController extends Controller
{
    /** Page publique : pas besoin d'être connecté. */
    public function checkAuth(): bool
    {
        return true;
    }

    public function change(string $code = ''): void
    {
        $available = Lang::available();
        if (isset($available[$code])) {
            $_SESSION['lang'] = $code;
        }

        // Retour à la page précédente (uniquement sur ce site).
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $base    = (string) parse_url(BASE_URL, PHP_URL_HOST);
        $host    = (string) parse_url($referer, PHP_URL_HOST);
        if ($referer !== '' && $host === $base) {
            header('Location: ' . $referer);
            exit;
        }
        $this->redirect('');
    }
}
