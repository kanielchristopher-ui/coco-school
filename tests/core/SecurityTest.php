<?php
declare(strict_types=1);

use CocoCore\Exceptions\BusinessRuleException;
use CocoCore\Exceptions\PermissionDeniedException;
use CocoCore\Exceptions\ValidationException;
use CocoCore\Modules\Auth\AuthService;
use CocoCore\Security\Actor;
use CocoCore\Security\AuthorizationService;
use CocoCore\Security\PasswordHasher;
use CocoCore\Security\SchoolRolePolicy;

return function (): void {
    $auth = new AuthorizationService(new FakePermissions(['cashier' => ['fees.manage'], 'prefet' => ['bulletins.publish']]));
    T::check('caissière : fees.manage', $auth->can(new Actor(1, 'cashier'), 'fees.manage'));
    T::check('caissière : pas bulletins.publish', !$auth->can(new Actor(1, 'cashier'), 'bulletins.publish'));
    T::check('super_admin : tout', $auth->can(new Actor(1, 'super_admin'), 'nimporte.quoi'));
    T::check('rôle inconnu : rien', !$auth->can(new Actor(1, 'inconnu'), 'fees.manage'));
    T::throws('authorize lève', PermissionDeniedException::class, fn() => $auth->authorize(new Actor(1, 'cashier'), 'bulletins.publish'));

    // authentification
    $hasher = new PasswordHasher();
    $users = new FakeUsers([
        ['login' => 'prefet', 'id' => 11, 'password' => $hasher->hash('secret123'), 'status' => 'active', 'role_slug' => 'prefet', 'first_name' => 'Prosper', 'last_name' => 'Mbayo'],
        ['login' => 'gele', 'id' => 12, 'password' => $hasher->hash('secret123'), 'status' => 'suspended', 'role_slug' => 'teacher', 'first_name' => 'A', 'last_name' => 'B'],
        ['login' => 'vieux', 'id' => 13, 'password' => password_hash('secret123', PASSWORD_BCRYPT, ['cost' => 4]), 'status' => 'active', 'role_slug' => 'teacher', 'first_name' => 'V', 'last_name' => 'X'],
    ]);
    $audit = new FakeAudit();
    $svc = new AuthService($users, $hasher, $audit);
    $a = $svc->attempt('prefet', 'secret123');
    T::same('connexion : rôle', 'prefet', $a->role);
    T::same('connexion : nom', 'Prosper Mbayo', $a->name);
    T::throws('mauvais mot de passe', BusinessRuleException::class, fn() => $svc->attempt('prefet', 'faux'));
    T::throws('compte inconnu (même message)', BusinessRuleException::class, fn() => $svc->attempt('nobody', 'x'));
    T::throws('compte suspendu', BusinessRuleException::class, fn() => $svc->attempt('gele', 'secret123'));
    T::throws('champs vides', ValidationException::class, fn() => $svc->attempt('', ''));

    // portée par établissement
    $users->establishments = [11 => [3], 12 => [1, 2]];
    $users2 = new FakeUsers([
        ['login' => 'prefet', 'id' => 11, 'password' => $hasher->hash('x1234567'), 'status' => 'active', 'role_slug' => 'prefet', 'first_name' => 'P', 'last_name' => 'M'],
        ['login' => 'dir', 'id' => 12, 'password' => $hasher->hash('x1234567'), 'status' => 'active', 'role_slug' => 'director', 'first_name' => 'D', 'last_name' => 'K'],
        ['login' => 'root', 'id' => 1, 'password' => $hasher->hash('x1234567'), 'status' => 'active', 'role_slug' => 'admin', 'first_name' => 'A', 'last_name' => 'B'],
    ]);
    $users2->establishments = [11 => [3], 12 => [1, 2], 1 => [3]];
    $svc2 = new AuthService($users2, $hasher, new FakeAudit());
    $pf = $svc2->attempt('prefet', 'x1234567');
    T::check('préfet : secondaire seulement', $pf->canAccessEstablishment(3) && !$pf->canAccessEstablishment(1) && !$pf->canAccessEstablishment(2));
    $dr = $svc2->attempt('dir', 'x1234567');
    T::check('directeur : maternel + primaire', $dr->canAccessEstablishment(1) && $dr->canAccessEstablishment(2) && !$dr->canAccessEstablishment(3));
    T::check('admin : tous les établissements', $svc2->attempt('root', 'x1234567')->canAccessEstablishment(3) && $svc2->attempt('root', 'x1234567')->canAccessEstablishment(1));
    T::check('échecs journalisés', count(array_filter($audit->entries, fn($e) => $e['action'] === 'login_failed')) === 2);

    // politique de rôles pour les bulletins
    $p = new SchoolRolePolicy();
    T::check('directeur publie le primaire', $p->canPublish('director', 'primaire'));
    T::check('directeur ne publie pas le secondaire', !$p->canPublish('director', 'secondaire'));
    T::check('préfet publie le secondaire', $p->canPublish('prefet', 'secondaire'));
    T::check('préfet ne publie pas le primaire', !$p->canPublish('prefet', 'primaire'));
    T::check('maternelle = primaire', $p->canPublish('director', 'maternelle'));
    T::check('proviseur valide le secondaire', $p->canValidate('proviseur', 'secondaire'));
    T::check('proviseur ne publie pas', !$p->canPublish('proviseur', 'secondaire'));
    T::check('enseignant assigné saisit', $p->canEnterMarks('teacher', true));
    T::check('enseignant non assigné ne saisit pas', !$p->canEnterMarks('teacher', false));
    T::check('parent ne voit pas un brouillon', !$p->canViewBulletin('parent', 'draft', 5, 9, true));
    T::check('parent voit le publié de son enfant', $p->canViewBulletin('parent', 'published', 5, 9, true));
    T::check('parent non lié ne voit rien', !$p->canViewBulletin('parent', 'published', 5, 9, false));
    T::check('élève voit uniquement le sien', $p->canViewBulletin('student', 'published', 9, 9, false) && !$p->canViewBulletin('student', 'published', 8, 9, false));
};
