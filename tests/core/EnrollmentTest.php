<?php
declare(strict_types=1);

use CocoCore\Events\EventDispatcher;
use CocoCore\Exceptions\BusinessRuleException;
use CocoCore\Exceptions\LicenseException;
use CocoCore\Exceptions\NotFoundException;
use CocoCore\Exceptions\PermissionDeniedException;
use CocoCore\Licensing\NullLicenseGuard;
use CocoCore\Modules\Enrollment\EnrollmentService;
use CocoCore\Security\Actor;
use CocoCore\Security\AuthorizationService;

return function (): void {
    $store = new FakeEnrollmentStore();
    $store->classes = [
        1 => ['id' => 1, 'name' => '1ère Maternelle', 'cycle' => 'maternel', 'establishment_id' => 1, 'section_capacity' => null],
        2 => ['id' => 2, 'name' => '1ère Primaire', 'cycle' => 'primaire', 'establishment_id' => 2, 'section_capacity' => null],
        3 => ['id' => 3, 'name' => '3ème Primaire', 'cycle' => 'primaire', 'establishment_id' => 2, 'section_capacity' => 2],
        4 => ['id' => 4, 'name' => '7ème Éducation de base (CTEB)', 'cycle' => 'secondaire', 'establishment_id' => 3, 'section_capacity' => null],
    ];
    $store->students = [
        10 => ['id' => 10, 'dob' => '2023-03-01', 'status' => 'active'],   // 3 ans en sept. 2026
        11 => ['id' => 11, 'dob' => '2024-05-01', 'status' => 'active'],   // 2 ans
        12 => ['id' => 12, 'dob' => '2021-01-01', 'status' => 'active'],   // 5 ans
        13 => ['id' => 13, 'dob' => '2020-01-01', 'status' => 'active'],   // 6 ans
        14 => ['id' => 14, 'dob' => '2019-01-01', 'status' => 'left'],
        15 => ['id' => 15, 'dob' => null, 'status' => 'active'],
        16 => ['id' => 16, 'dob' => '2014-06-01', 'status' => 'active'],   // 12 ans
    ];
    $audit = new FakeAudit();
    $events = new EventDispatcher();
    $seen = [];
    $events->listen('student.enrolled', function ($e) use (&$seen) { $seen[] = $e->payload['level']; });
    $auth = new AuthorizationService(new FakePermissions(['director' => ['students.manage'], 'teacher' => ['dashboard.view']]));
    $svc = new EnrollmentService($store, $auth, new NullLicenseGuard(), new FakeTx(), $events, $audit);
    $director = new Actor(15, 'director', 1, 'Odette', [1, 2]);
    $on = new DateTimeImmutable('2026-09-07');

    $id = $svc->enroll($director, 10, 1, 1, $on);
    T::same('inscription maternelle à 3 ans', 1, $id);
    T::same('classe de l\'élève mise à jour', 1, $store->studentClass[10]);
    T::same('établissement enregistré', 1, $store->rows[0]['establishment_id']);
    T::same('audit', 'enroll', $audit->entries[0]['action']);
    T::same('événement avec le niveau', ['maternel'], $seen);

    T::throws('2 ans : trop jeune pour la maternelle', BusinessRuleException::class, fn() => $svc->enroll($director, 11, 1, 1, $on));
    T::throws('5 ans : trop jeune pour la 1ère primaire', BusinessRuleException::class, fn() => $svc->enroll($director, 12, 2, 1, $on));
    T::check('6 ans : admis en 1ère primaire', $svc->enroll($director, 13, 2, 1, $on) === 2);
    T::throws('déjà inscrit cette année', BusinessRuleException::class, fn() => $svc->enroll($director, 13, 2, 1, $on));
    T::check('autre année : permis', $svc->enroll($director, 13, 2, 2, $on) === 3);
    T::throws('élève non actif', BusinessRuleException::class, fn() => $svc->enroll($director, 14, 3, 1, $on));
    T::check('âge non renseigné : accepté', $svc->enroll($director, 15, 3, 1, $on) > 0);
    $store->enrolled['3:1'] = 2;
    T::throws('division complète (capacité 2)', BusinessRuleException::class, fn() => $svc->enroll($director, 16, 3, 1, $on));
    T::throws('classe inconnue', NotFoundException::class, fn() => $svc->enroll($director, 10, 99, 1, $on));
    T::throws('élève inconnu', NotFoundException::class, fn() => $svc->enroll($director, 99, 3, 1, $on));
    T::throws('établissement non rattaché (secondaire)', PermissionDeniedException::class, fn() => $svc->enroll($director, 16, 4, 1, $on));
    T::throws('rôle sans permission', PermissionDeniedException::class, fn() => $svc->enroll(new Actor(9, 'teacher', 1, 'T', [2]), 16, 3, 3, $on));
    $admin = new Actor(1, 'director', 1, 'Admin', null);
    T::check('administrateur : tous les établissements', $svc->enroll($admin, 16, 4, 1, $on) > 0);
    T::check('âge calculé', EnrollmentService::ageAt('2020-09-08', $on) === 5 && EnrollmentService::ageAt('2020-09-07', $on) === 6);

    $svcExpired = new EnrollmentService($store, $auth, new DenyingGuard(), new FakeTx(), $events, $audit);
    T::throws('licence expirée / plafond : inscription refusée', LicenseException::class, fn() => $svcExpired->enroll($director, 12, 1, 5, $on));
};
