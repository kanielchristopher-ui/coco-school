<?php
declare(strict_types=1);

use CocoCore\Database\Connection;
use CocoCore\Domain\Entities\Establishment;
use CocoCore\Domain\Entities\SchoolClass;
use CocoCore\Domain\Entities\Student;
use CocoCore\Domain\Entity;
use CocoCore\Domain\EntityRegistry;
use CocoCore\Exceptions\ValidationException;
use CocoCore\Repositories\EntityRepository;

return function (): void {
    $all = EntityRegistry::all();
    T::check('au moins 70 modèles', count($all) >= 70, (string) count($all));
    $bad = [];
    foreach (EntityRegistry::TABLES as $table => $class) {
        if (!is_subclass_of($class, Entity::class) || $class::TABLE !== $table || !$class::FIELDS || !$class::PRIMARY_KEY) {
            $bad[] = $table;
        }
        foreach ($class::PRIMARY_KEY as $pk) {
            if (!array_key_exists($pk, $class::FIELDS)) {
                $bad[] = $table . '.' . $pk;
            }
        }
        foreach ($class::REQUIRED as $f) {
            if (!array_key_exists($f, $class::FIELDS)) {
                $bad[] = $table . '.required.' . $f;
            }
        }
    }
    T::same('modèles cohérents (table, champs, clé primaire)', [], $bad);
    foreach (['students', 'classes', 'establishments', 'licenses', 'student_enrollments', 'development_assessments', 'national_exam_registrations', 'periodic_bulletins', 'student_marks', 'fee_collections', 'discipline_records'] as $t) {
        T::check("modèle pour $t", isset(EntityRegistry::TABLES[$t]));
    }

    // typage et validation
    $s = Student::fromRow(['id' => '5', 'first_name' => 'Mbuyi', 'gender' => 'male', 'class_id' => '4', 'user_id' => null, 'dob' => '2019-05-12']);
    T::same('entier typé', 5, $s->id);
    T::same('nullable', null, $s->user_id);
    T::same('chaîne', 'Mbuyi', $s->first_name);
    $s2 = $s->with(['first_name' => 'Kabongo']);
    T::check('with() ne modifie pas l\'original', $s->first_name === 'Mbuyi' && $s2->first_name === 'Kabongo');
    T::throws('champ inconnu refusé', InvalidArgumentException::class, fn() => $s->get('inexistant'));
    $errs = (new Student(['last_name' => 'X']))->validate();
    T::check('champs obligatoires signalés', isset($errs['first_name']) || isset($errs['admission_no']), json_encode($errs));
    $bad = (new SchoolClass(['name' => 'X', 'cycle' => 'lycée']))->validate();
    T::check('cycle hors liste refusé', isset($bad['cycle']));
    T::check('cycle valide accepté', !isset((new SchoolClass(['name' => 'X', 'cycle' => 'primaire']))->validate()['cycle']));
    T::throws('assertValid lève', ValidationException::class, fn() => (new SchoolClass(['cycle' => 'primaire']))->assertValid());
    T::check('énumération des niveaux', SchoolClass::ENUMS['cycle'] === ['maternel', 'primaire', 'secondaire']);
    T::check('établissement : niveau', Establishment::ENUMS['level'] === ['maternel', 'primaire', 'secondaire']);

    // dépôt générique sur SQLite en mémoire (ignoré si pdo_sqlite absent)
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        echo "  (dépôt générique ignoré : pdo_sqlite absent)\n";
        return;
    }
    $db = new Connection(new PDO('sqlite::memory:'));
    $db->pdo()->exec('CREATE TABLE establishments (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT NOT NULL, name TEXT NOT NULL, level TEXT NOT NULL, status TEXT DEFAULT "active", head_name TEXT)');
    $repo = new EntityRepository($db, Establishment::class);
    $id1 = $repo->insert(['code' => 'MAT-01', 'name' => 'Maternelle', 'level' => 'maternel']);
    $id2 = $repo->insert(['code' => 'PRI-01', 'name' => 'Primaire', 'level' => 'primaire']);
    $repo->insert(['code' => 'SEC-01', 'name' => 'Secondaire', 'level' => 'secondaire']);
    T::same('insert retourne l\'identifiant', 1, $id1);
    T::same('find', 'Primaire', $repo->find($id2)?->name);
    T::same('find absent', null, $repo->find(999));
    T::same('count', 3, $repo->count());
    T::same('critère simple', 1, count($repo->findBy(['level' => 'primaire'])));
    T::same('critère IN', 2, count($repo->findBy(['level' => ['IN', ['maternel', 'secondaire']]])));
    T::same('critère LIKE + tri', 'Primaire', $repo->findBy(['name' => ['LIKE', 'Pri%']], 'name DESC')[0]->name);
    T::same('IS NULL', 3, count($repo->findBy(['head_name' => null])));
    T::same('limite', 2, count($repo->findBy([], 'id', 2)));
    T::same('update', 1, $repo->update($id1, ['head_name' => 'Mme Lumbala']));
    T::same('update appliqué', 'Mme Lumbala', $repo->find($id1)?->head_name);
    T::same('delete', 1, $repo->delete($id2));
    T::same('count après delete', 2, $repo->count());
    T::throws('colonne inconnue refusée', InvalidArgumentException::class, fn() => $repo->findBy(['nimporte' => 1]));
    T::throws('injection dans le tri refusée', InvalidArgumentException::class, fn() => $repo->findBy([], 'id; DROP TABLE establishments'));
    T::throws('opérateur interdit', InvalidArgumentException::class, fn() => $repo->findBy(['level' => ['OR 1=1 --', 'x']]));
    T::throws('insertion invalide refusée', ValidationException::class, fn() => $repo->insert(['name' => 'sans code']));
    T::same('la table existe toujours', 2, $repo->count());
};
