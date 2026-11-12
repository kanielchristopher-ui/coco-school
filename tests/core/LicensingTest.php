<?php
declare(strict_types=1);

use CocoCore\Exceptions\LicenseException;
use CocoCore\Licensing\LicenseCodec;
use CocoCore\Licensing\LicenseManager;
use CocoCore\Licensing\LicenseStatus;
use CocoCore\Licensing\LicenseVerifier;

return function (): void {
    if (!function_exists('sodium_crypto_sign_keypair')) {
        echo "  (ignoré : extension sodium absente)\n";
        return;
    }
    $kp = sodium_crypto_sign_keypair();
    $secret = sodium_crypto_sign_secretkey($kp);
    $pub = base64_encode(sodium_crypto_sign_publickey($kp));
    $otherSecret = sodium_crypto_sign_secretkey(sodium_crypto_sign_keypair());

    $payload = fn(array $over = []) => array_merge([
        'v' => 1, 'license_id' => 'L1', 'holder' => 'École test', 'issued_at' => '2026-09-01', 'valid_from' => '2026-09-01', 'valid_until' => '2027-08-31',
        'grace_days' => 30, 'edition' => 'standard', 'levels' => ['primaire'], 'modules' => ['*'], 'max_students' => ['primaire' => 2],
    ], $over);
    $at = fn(string $d) => new DateTimeImmutable($d . ' 10:00:00');
    $verifier = new LicenseVerifier([$pub]);
    $tok = LicenseCodec::encode($payload(), $secret);

    // --- vérification pure -----------------------------------------------------
    T::same('VALID', LicenseStatus::VALID, $verifier->verify($tok, $at('2026-11-08'))->status);
    T::same('jours restants', 296, $verifier->verify($tok, $at('2026-11-08'))->daysLeft);
    T::same('EXPIRING_SOON à 30 jours', LicenseStatus::EXPIRING_SOON, $verifier->verify($tok, $at('2027-08-01'))->status);
    T::same('dernier jour encore valide', LicenseStatus::EXPIRING_SOON, $verifier->verify($tok, $at('2027-08-31'))->status);
    T::same('GRACE le lendemain', LicenseStatus::GRACE, $verifier->verify($tok, $at('2027-09-01'))->status);
    T::same('GRACE au 30e jour', LicenseStatus::GRACE, $verifier->verify($tok, $at('2027-09-30'))->status);
    T::same('EXPIRED après la grâce', LicenseStatus::EXPIRED, $verifier->verify($tok, $at('2027-10-01'))->status);
    T::same('NOT_YET_VALID', LicenseStatus::NOT_YET_VALID, $verifier->verify($tok, $at('2026-08-31'))->status);
    T::same('TAMPERED (horloge reculée)', LicenseStatus::TAMPERED, $verifier->verify($tok, $at('2026-10-01'), $at('2026-11-01'))->status);
    T::same('tolérance d\'un jour', LicenseStatus::VALID, $verifier->verify($tok, $at('2026-10-31'), $at('2026-11-01'))->status);
    T::same('REVOKED', LicenseStatus::REVOKED, $verifier->verify($tok, $at('2026-11-08'), null, ['L1'])->status);
    T::same('signature d\'une autre clé', LicenseStatus::INVALID_SIGNATURE, $verifier->verify(LicenseCodec::encode($payload(), $otherSecret), $at('2026-11-08'))->status);
    T::same('charge utile falsifiée', LicenseStatus::INVALID_SIGNATURE, $verifier->verify(
        LicenseCodec::PREFIX . '.' . LicenseCodec::b64urlEncode(json_encode($payload(['valid_until' => '2099-12-31']))) . '.' . explode('.', $tok)[2],
        $at('2026-11-08')
    )->status);
    T::same('MALFORMED : texte quelconque', LicenseStatus::MALFORMED, $verifier->verify('nimporte quoi', $at('2026-11-08'))->status);
    T::same('MALFORMED : niveau inconnu', LicenseStatus::MALFORMED, $verifier->verify(LicenseCodec::encode($payload(['levels' => ['lycée']]), $secret), $at('2026-11-08'))->status);
    T::same('rotation : deux clés de confiance', LicenseStatus::VALID, (new LicenseVerifier([base64_encode(random_bytes(32)), $pub]))->verify($tok, $at('2026-11-08'))->status);
    T::check('lecture/écriture selon l\'état', LicenseStatus::GRACE->allowsWrite() && !LicenseStatus::EXPIRED->allowsWrite() && LicenseStatus::EXPIRED->allowsRead() && LicenseStatus::REVOKED->isLocked());

    // --- gestionnaire : essai -----------------------------------------------------
    $store = new FakeLicenseStore();
    $now = $at('2026-11-01');
    $mk = function () use (&$store, $verifier, &$now) {
        return new LicenseManager($store, $verifier, function () use (&$now) { return $now; });
    };
    $c = $mk()->check();
    T::same('essai : démarre', LicenseStatus::TRIAL, $c->status);
    T::same('essai : 30 jours', 30, $c->daysLeft);
    T::same('essai enregistré', '2026-11-01', $store->state['trial_started']);
    $now = $at('2026-11-11');
    T::same('essai : 20 jours', 20, $mk()->check()->daysLeft);
    $mk()->requireWrite('maternel', 'fees');
    T::check('essai : écriture permise', true);
    T::throws('essai : plafond de 30 élèves', LicenseException::class, function () use ($store, $mk) {
        $store->students['primaire'] = 30;
        $mk()->requireQuota('primaire');
    });
    $store->students = [];
    $now = $at('2026-12-15');
    T::same('essai terminé', LicenseStatus::TRIAL_EXPIRED, $mk()->check()->status);
    T::throws('essai terminé : lecture seule', LicenseException::class, fn() => $mk()->requireWrite());
    $now = $at('2026-12-01');
    T::same('essai : retour d\'horloge détecté', LicenseStatus::TAMPERED, $mk()->check()->status);

    // --- gestionnaire : licence installée ---------------------------------------------
    $store = new FakeLicenseStore();
    $now = $at('2026-11-08');
    $m = $mk();
    T::throws('installation : clé illisible refusée', LicenseException::class, fn() => $m->install('abc'));
    T::throws('installation : mauvaise signature refusée', LicenseException::class, fn() => $m->install(LicenseCodec::encode($payload(), $otherSecret)));
    T::throws('installation : clé expirée refusée', LicenseException::class, fn() => $m->install(LicenseCodec::encode($payload(['valid_until' => '2026-01-31', 'valid_from' => '2026-01-01', 'grace_days' => 0]), $secret)));
    $r = $m->install($tok, 1);
    T::same('installation : statut VALID', LicenseStatus::VALID, $r->status);
    T::same('installation : licence enregistrée', 'L1', $store->saved?->id);
    T::check('installation : événement', in_array('installed', $store->events, true) && in_array('install_refused', $store->events, true));

    $m = $mk();
    $m->requireWrite('primaire', 'fees');
    T::check('niveau couvert', true);
    T::throws('niveau non couvert', LicenseException::class, fn() => $mk()->requireWrite('maternel'));
    $store->token = LicenseCodec::encode($payload(['modules' => ['fees', 'bulletins']]), $secret);
    $m = $mk();
    $m->requireWrite('primaire', 'bulletins');
    T::check('module couvert', true);
    T::throws('module non couvert', LicenseException::class, fn() => $mk()->requireWrite('primaire', 'payroll'));
    T::same('modules=* couvre tout', null, (function () use (&$store, $payload, $secret, $mk) { $store->token = LicenseCodec::encode($payload(), $secret); $mk()->requireWrite('primaire', 'nimporte'); return null; })());

    $store->students['primaire'] = 1;
    $mk()->requireQuota('primaire');
    T::check('quota : 1 + 1 ≤ 2', true);
    $store->students['primaire'] = 2;
    T::throws('quota : 2 + 1 > 2', LicenseException::class, fn() => $mk()->requireQuota('primaire'));

    $now = $at('2027-09-10');
    $mk()->requireWrite('primaire');
    T::check('grâce : écriture encore permise', true);
    $now = $at('2027-10-05');
    T::same('expirée', LicenseStatus::EXPIRED, $mk()->check()->status);
    T::throws('expirée : lecture seule', LicenseException::class, fn() => $mk()->requireWrite());
    T::check('expirée : lecture permise', $mk()->check()->status->allowsRead());

    $now = $at('2026-11-08');
    $store->revoked = ['L1'];
    T::same('révoquée : verrouillée', LicenseStatus::REVOKED, $mk()->check()->status);
    T::check('révoquée : plus de lecture', !$mk()->check()->status->allowsRead());
};
