<?php
declare(strict_types=1);

namespace CocoCore\Licensing;

use CocoCore\Contracts\LicenseGuardInterface;
use CocoCore\Contracts\LicenseStoreInterface;
use CocoCore\Exceptions\LicenseException;

/**
 * Cycle de vie de la licence :
 *   - aucune clé installée  -> période d'ESSAI (30 jours, plafond d'élèves réduit), puis lecture seule ;
 *   - clé installée         -> validité, grâce, expiration (lecture seule), révocation, anti-retour d'horloge.
 * Applique la licence : écriture (requireWrite), niveaux et modules couverts, plafond d'élèves par niveau.
 */
final class LicenseManager implements LicenseGuardInterface
{
    public const TRIAL_DAYS = 30;
    public const TRIAL_MAX_STUDENTS = 30;

    private ?LicenseCheck $cache = null;
    /** @var callable():\DateTimeImmutable */
    private $clock;

    public function __construct(
        private LicenseStoreInterface $store,
        private LicenseVerifier $verifier,
        ?callable $clock = null
    ) {
        $this->clock = $clock ?? static fn() => new \DateTimeImmutable('now');
    }

    public function check(bool $refresh = false): LicenseCheck
    {
        if ($this->cache !== null && !$refresh) {
            return $this->cache;
        }
        $now = ($this->clock)();
        $lastSeenRaw = $this->store->state('last_seen');
        $lastSeen = $lastSeenRaw ? new \DateTimeImmutable($lastSeenRaw) : null;

        $token = $this->store->activeToken();
        $check = $token === null
            ? $this->trial($now, $lastSeen)
            : $this->verifier->verify($token, $now, $lastSeen, $this->store->revokedIds());

        if ($check->status !== LicenseStatus::TAMPERED && ($lastSeen === null || $now > $lastSeen)) {
            $this->store->setState('last_seen', $now->format('Y-m-d H:i:s'));
        }
        if ($this->store->state('last_status') !== $check->status->value) {
            $this->store->setState('last_status', $check->status->value);
            $this->store->event('status_' . $check->status->value, ['license' => $check->license?->id, 'detail' => $check->detail]);
        }
        return $this->cache = $check;
    }

    private function trial(\DateTimeImmutable $now, ?\DateTimeImmutable $lastSeen): LicenseCheck
    {
        $startRaw = $this->store->state('trial_started');
        if ($startRaw === null) {
            $this->store->setState('trial_started', $now->format('Y-m-d'));
            $startRaw = $now->format('Y-m-d');
        }
        $start = new \DateTimeImmutable($startRaw);
        if ($lastSeen !== null && $now < $lastSeen->modify('-1 day')) {
            return new LicenseCheck(LicenseStatus::TAMPERED, null, null, null, 'L\'horloge du système est antérieure à la dernière utilisation enregistrée.');
        }
        $elapsed = (int) $start->setTime(0, 0)->diff($now->setTime(0, 0))->format('%r%a');
        $left = self::TRIAL_DAYS - $elapsed;
        return $left >= 0
            ? new LicenseCheck(LicenseStatus::TRIAL, null, $left, null, 'Essai : ' . $left . ' jour(s) restant(s).')
            : new LicenseCheck(LicenseStatus::TRIAL_EXPIRED, null, $left, null, 'La période d\'essai est terminée.');
    }

    /**
     * Installe une nouvelle clé. Refuse une clé illisible, mal signée, révoquée ou déjà expirée (hors grâce).
     * @throws LicenseException
     */
    public function install(string $token, ?int $userId = null): LicenseCheck
    {
        $now = ($this->clock)();
        $check = $this->verifier->verify($token, $now, null, $this->store->revokedIds());
        if (in_array($check->status, [LicenseStatus::MALFORMED, LicenseStatus::INVALID_SIGNATURE, LicenseStatus::REVOKED, LicenseStatus::EXPIRED], true)) {
            $this->store->event('install_refused', ['status' => $check->status->value]);
            throw new LicenseException($check->detail !== '' ? $check->detail : $check->status->label(), $check->status->value);
        }
        $this->store->saveLicense(trim($token), $check->license, $userId);
        $this->store->event('installed', ['license' => $check->license?->id, 'holder' => $check->license?->holder]);
        $this->cache = null;
        $this->store->setState('last_status', '');
        return $this->check(true);
    }

    public function requireWrite(?string $level = null, ?string $module = null): void
    {
        $check = $this->check();
        if (!$check->status->allowsWrite()) {
            throw new LicenseException($check->status->label() . ($check->detail !== '' ? ' — ' . $check->detail : ''), $check->status->value);
        }
        $license = $check->license;
        if ($license === null) {
            return;     // essai : tous les niveaux et modules
        }
        if ($level !== null && !$license->allowsLevel($level)) {
            throw new LicenseException('Votre licence ne couvre pas le niveau « ' . $level . ' ».', 'level_not_licensed');
        }
        if ($module !== null && !$license->allowsModule($module)) {
            throw new LicenseException('Votre licence ne couvre pas le module « ' . $module . ' ».', 'module_not_licensed');
        }
    }

    public function requireQuota(string $level, int $adding = 1): void
    {
        $this->requireWrite($level);
        $check = $this->check();
        $limit = $check->license === null ? self::TRIAL_MAX_STUDENTS : $check->license->studentLimit($level);
        if ($limit > 0 && $this->store->countStudents($level) + $adding > $limit) {
            throw new LicenseException(sprintf('Plafond de %d élèves atteint pour le niveau « %s ». Contactez votre fournisseur pour étendre la licence.', $limit, $level), 'quota');
        }
    }
}
