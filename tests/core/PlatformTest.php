<?php
declare(strict_types=1);

use CocoCore\Database\Migrator;
use CocoCore\Events\DomainEvent;
use CocoCore\Events\EventDispatcher;
use CocoCore\Exceptions\BusinessRuleException;
use CocoCore\Modules\Bulletins\BulletinWorkflow;
use CocoCore\Plugins\CCPLG\PackageException;
use CocoCore\Plugins\Templates\SafeTemplateRenderer;

return function (): void {
    // événements
    $d = new EventDispatcher();
    $log = [];
    $d->listen('a', function (DomainEvent $e) use (&$log) { $log[] = 'a:' . $e->payload['x']; });
    $d->listen('*', function (DomainEvent $e) use (&$log) { $log[] = '*:' . $e->name; });
    $d->dispatch(new DomainEvent('a', ['x' => 1]));
    $d->dispatch(new DomainEvent('b'));
    T::same('dispatcher', ['a:1', '*:a', '*:b'], $log);

    // flux de bulletins
    $w = new BulletinWorkflow();
    T::check('draft -> validated', $w->canTransition('draft', 'validated'));
    T::check('draft -> published interdit', !$w->canTransition('draft', 'published'));
    T::check('published -> draft interdit (jamais modifié)', !$w->canTransition('published', 'draft'));
    T::check('published verrouille la saisie', $w->locksMarks('published') && !$w->locksMarks('draft'));
    $w->assertTransition('director', 'primaire', 'draft', 'validated', true);
    T::throws('validation sans notes complètes', BusinessRuleException::class, fn() => $w->assertTransition('director', 'primaire', 'draft', 'validated', false));
    T::throws('publication par le mauvais rôle', BusinessRuleException::class, fn() => $w->assertTransition('prefet', 'primaire', 'validated', 'published', true));
    $w->assertTransition('prefet', 'secondaire', 'validated', 'published', true);
    T::check('transition valide acceptée', true);

    // migrations : découverte ordonnée, noms invalides ignorés
    $dir = sys_get_temp_dir() . '/coco_mig_' . bin2hex(random_bytes(4));
    mkdir($dir);
    foreach (['0002_b.sql', '0001_a.sql', 'readme.txt', '12_bad.sql', '0003_c_d.sql'] as $f) {
        file_put_contents("$dir/$f", '-- x');
    }
    T::same('migrations découvertes dans l\'ordre', ['0001', '0002', '0003'], array_keys(Migrator::discover($dir)));
    array_map('unlink', glob("$dir/*"));
    rmdir($dir);

    // migrations du dépôt : numérotation continue
    $repo = array_keys(Migrator::discover(__DIR__ . '/../../database/migrations'));
    T::same('migrations du dépôt', ['0001', '0002', '0003', '0004'], $repo);

    // modèles sûrs
    $r = new SafeTemplateRenderer();
    T::check('variables échappées', str_contains($r->render('<p>{{name}}</p>', ['name' => '<b>x</b>']), '&lt;b&gt;'));
    T::throws('script refusé', PackageException::class, fn() => $r->render('<script>alert(1)</script>', []));
};
