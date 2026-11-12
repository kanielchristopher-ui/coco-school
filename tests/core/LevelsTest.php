<?php
declare(strict_types=1);

use CocoCore\Levels\ClassLadder;
use CocoCore\Levels\LevelRegistry;
use CocoCore\Levels\Maternel\AgeSection;
use CocoCore\Levels\Maternel\DevelopmentAssessmentService;
use CocoCore\Levels\Maternel\PickupService;
use CocoCore\Levels\Primaire\TenafepService;
use CocoCore\Levels\PromotionDecision;
use CocoCore\Levels\PromotionService;
use CocoCore\Levels\SchoolLevel;
use CocoCore\Levels\Secondaire\ConductService;
use CocoCore\Levels\Secondaire\CourseMaxima;
use CocoCore\Levels\Secondaire\ExetatService;
use CocoCore\Exceptions\ValidationException;

return function (): void {
    // ---------------- trois niveaux DISTINCTS ----------------
    $m = LevelRegistry::profile('maternelle');
    $p = LevelRegistry::profile(SchoolLevel::PRIMAIRE);
    $s = LevelRegistry::profile('secondaire');
    T::same('maternel : 3 classes', 3, count($m->classLadder()));
    T::same('primaire : 6 classes', 6, count($p->classLadder()));
    T::same('secondaire : 6 classes (2 EB + 4 humanités)', 6, count($s->classLadder()));
    T::same('âge d\'entrée maternel', 3, $m->admissionAge()['min']);
    T::same('âge d\'entrée primaire (6 ans révolus)', 6, $p->admissionAge()['min']);
    T::same('évaluation maternel qualitative', 'qualitative', $m->evaluationMode());
    T::same('évaluation primaire numérique', 'numeric', $p->evaluationMode());
    T::check('pas de classement au maternel', !$m->usesRanking() && $p->usesRanking() && $s->usesRanking());
    T::same('pas de seuil de réussite au maternel', null, $m->passMark());
    T::same('seuil 50 % au primaire et secondaire', [50.0, 50.0], [$p->passMark(), $s->passMark()]);
    T::same('examens nationaux', [null, 'TENAFEP', 'EXETAT'], [$m->nationalExam(), $p->nationalExam(), $s->nationalExam()]);
    T::same('classes terminales', [null, 'P6', 'H4'], [$m->nationalExamClass(), $p->nationalExamClass(), $s->nationalExamClass()]);
    T::same('chefs d\'établissement', ['Directeur', 'Directeur', 'Préfet'], [SchoolLevel::MATERNEL->headTitle(), SchoolLevel::PRIMAIRE->headTitle(), SchoolLevel::SECONDAIRE->headTitle()]);
    T::same('rôle du chef', ['director', 'director', 'prefet'], [SchoolLevel::MATERNEL->headRole(), SchoolLevel::PRIMAIRE->headRole(), SchoolLevel::SECONDAIRE->headRole()]);
    T::check('modules propres au maternel', LevelRegistry::moduleEnabled('maternel', 'pickup') && !LevelRegistry::moduleEnabled('primaire', 'pickup') && !LevelRegistry::moduleEnabled('secondaire', 'health'));
    T::check('TENAFEP : primaire seulement', LevelRegistry::moduleEnabled('primaire', 'tenafep') && !LevelRegistry::moduleEnabled('secondaire', 'tenafep'));
    T::check('Exetat, options, conduite, repêchage : secondaire seulement', LevelRegistry::moduleEnabled('secondaire', 'exetat') && !LevelRegistry::moduleEnabled('primaire', 'exetat')
        && LevelRegistry::moduleEnabled('secondaire', 'options') && LevelRegistry::moduleEnabled('secondaire', 'conduct') && !LevelRegistry::moduleEnabled('maternel', 'retake'));
    T::check('paie : secondaire', LevelRegistry::moduleEnabled('secondaire', 'payroll'));
    T::throws('niveau inconnu', InvalidArgumentException::class, fn() => SchoolLevel::parse('lycée'));

    // ---------------- échelle des classes ----------------
    T::same('M1 -> M2', 'M2', ClassLadder::next('M1'));
    T::same('M3 -> P1', 'P1', ClassLadder::next('M3'));
    T::same('P6 -> EB7', 'EB7', ClassLadder::next('P6'));
    T::same('EB8 -> H1', 'H1', ClassLadder::next('EB8'));
    T::same('H4 -> fin', null, ClassLadder::next('H4'));
    T::check('M3 et P6 changent de niveau', ClassLadder::changesLevel('M3') && ClassLadder::changesLevel('P6') && !ClassLadder::changesLevel('P3') && !ClassLadder::changesLevel('H2'));
    T::same('niveau de EB7', SchoolLevel::SECONDAIRE, ClassLadder::levelOf('EB7'));
    T::same('15 classes au total', 15, count(ClassLadder::codes()));
    foreach (['1ère Maternelle' => 'M1', '3ème Maternelle' => 'M3', '1ère Primaire' => 'P1', '6ème Primaire' => 'P6', '7ème Éducation de base (CTEB)' => 'EB7',
             '8ème Éducation de base (CTEB)' => 'EB8', '1ère Humanités Biologie-Chimie' => 'H1', '4ème Humanités Mathématiques-Physique' => 'H4'] as $name => $code) {
        T::same("code de « $name »", $code, ClassLadder::codeFromName($name));
    }
    T::same('nom inconnu', null, ClassLadder::codeFromName('Classe spéciale'));

    // ---------------- maternel ----------------
    T::same('3 ans -> M1', 'M1', AgeSection::classFor(3));
    T::same('5 ans -> M3', 'M3', AgeSection::classFor(5));
    T::same('2 ans : pas de place', null, AgeSection::classFor(2));
    $dev = new DevelopmentAssessmentService();
    $good = [['domain_code' => 'LANG', 'level' => 'acquired'], ['domain_code' => 'MATH', 'level' => 'acquired'], ['domain_code' => 'MOT', 'level' => 'acquired'], ['domain_code' => 'ART', 'level' => 'in_progress'], ['domain_code' => 'SOC', 'level' => 'acquired']];
    $sum = $dev->summarize($good);
    T::same('acquis', 4, $sum['acquired']);
    T::same('taux d\'acquisition', 0.8, $sum['acquired_rate']);
    T::check('prêt pour le primaire', $sum['ready_for_primary']);
    $weak = $dev->summarize([['domain_code' => 'LANG', 'level' => 'not_acquired'], ['domain_code' => 'MATH', 'level' => 'acquired'], ['domain_code' => 'SOC', 'level' => 'acquired']]);
    T::check('langage non acquis : pas prêt malgré 66 %', !$weak['ready_for_primary'] && $weak['attention'] === ['LANG']);
    T::check('aucune appréciation : pas prêt', !$dev->summarize([])['ready_for_primary']);
    T::throws('niveau d\'acquisition invalide', ValidationException::class, fn() => $dev->summarize([['domain_code' => 'LANG', 'level' => 'bien']]));

    $pickup = new PickupService();
    $list = [['full_name' => 'Nadine Lokua', 'active' => 1, 'id_document' => 'CE-123'], ['full_name' => 'Papy Lokua', 'active' => 0, 'id_document' => null]];
    T::check('mère autorisée', $pickup->check($list, 'nadine lokua')['authorized']);
    T::check('accents et casse ignorés', $pickup->check([['full_name' => 'Éliane Mbuyi', 'active' => 1]], 'ELIANE  MBUYI')['authorized']);
    T::check('personne inconnue refusée', !$pickup->check($list, 'Inconnu')['authorized']);
    T::check('autorisation désactivée', !$pickup->check($list, 'Papy Lokua')['authorized']);
    T::check('pièce d\'identité différente refusée', !$pickup->check($list, 'Nadine Lokua', 'CE-999')['authorized']);
    T::check('même pièce acceptée', $pickup->check($list, 'Nadine Lokua', 'ce 123')['authorized']);
    T::check('nom vide refusé', !$pickup->check($list, '  ')['authorized']);

    $promo = new PromotionService();
    $d = $promo->decide('M1', null);
    T::check('maternel : passage automatique M1 -> M2', $d->outcome === PromotionDecision::PROMOTED && $d->nextClass === 'M2');
    T::same('M3 à 6 ans -> P1', 'P1', $promo->decide('M3', null, 6)->nextClass);
    T::same('M3 à 5 ans -> maintien', PromotionDecision::REPEAT, $promo->decide('M3', null, 5)->outcome);

    // ---------------- primaire ----------------
    $d = $promo->decide('P3', 62.5);
    T::check('P3 62,5 % : admis en P4', $d->outcome === 'promoted' && $d->nextClass === 'P4');
    T::same('P3 49,9 % : redoublement', PromotionDecision::REPEAT, $promo->decide('P3', 49.9)->outcome);
    T::same('P3 50 % : admis', PromotionDecision::PROMOTED, $promo->decide('P3', 50.0)->outcome);
    $d = $promo->decide('P6', 71.0);
    T::check('P6 réussie : candidat TENAFEP', $d->outcome === 'completed' && $d->nationalExamEligible && $d->nextClass === 'EB7');
    T::check('P6 échouée : pas de TENAFEP', !$promo->decide('P6', 40.0)->nationalExamEligible);
    $t = new TenafepService();
    T::same('TENAFEP : 60 % école + 40 % test', 62.0, $t->finalPercent(70.0, 50.0));
    T::check('TENAFEP : réussite à 50', $t->passed(50.0) && !$t->passed(49.99));
    T::check('TENAFEP : P6 réussie seulement', $t->eligible('P6', true) && !$t->eligible('P5', true) && !$t->eligible('P6', false));
    T::same('numéro de candidat', 'TEN-2027-PRI01-0007', $t->candidateNumber(2027, 'PRI-01', 7));

    // ---------------- secondaire ----------------
    T::same('H2 à 55 % : admis', 'H3', $promo->decide('H2', 55.0)->nextClass);
    T::same('H2 à 45 % : repêchage', PromotionDecision::RETAKE, $promo->decide('H2', 45.0)->outcome);
    T::same('H2 à 40 % : repêchage (borne)', PromotionDecision::RETAKE, $promo->decide('H2', 40.0)->outcome);
    T::same('H2 à 39,9 % : redoublement', PromotionDecision::REPEAT, $promo->decide('H2', 39.9)->outcome);
    T::same('repêchage non autorisé : redoublement', PromotionDecision::REPEAT, $promo->decide('H2', 45.0, null, false)->outcome);
    $d = $promo->decide('H4', 66.0);
    T::check('H4 réussie : candidat Exetat', $d->outcome === 'completed' && $d->nationalExamEligible && $d->nextClass === null);
    T::same('H4 échouée : redoublement', PromotionDecision::REPEAT, $promo->decide('H4', 30.0)->outcome);
    T::same('EB8 -> H1', 'H1', $promo->decide('EB8', 80.0)->nextClass);
    T::same('maxima 1-2 h', ['period' => 10.0, 'exam' => 20.0], CourseMaxima::forWeeklyHours(2));
    T::same('maxima 4 h', ['period' => 20.0, 'exam' => 40.0], CourseMaxima::forWeeklyHours(4));
    T::same('maxima 6 h', ['period' => 30.0, 'exam' => 60.0], CourseMaxima::forWeeklyHours(6));
    T::same('maxima 8 h', ['period' => 40.0, 'exam' => 80.0], CourseMaxima::forWeeklyHours(8));
    T::same('maxima surchargés', ['period' => 15.0, 'exam' => 25.0], CourseMaxima::forWeeklyHours(2, 15.0, 25.0));
    $ex = new ExetatService();
    T::check('Exetat : H4 réussie seulement', $ex->eligible('H4', true) && !$ex->eligible('H3', true));
    T::same('mention 85', 'Plus grande distinction', $ex->mention(85.0));
    T::same('mention 72', 'Grande distinction', $ex->mention(72.0));
    T::same('mention 61', 'Distinction', $ex->mention(61.0));
    T::same('mention 50', 'Satisfaction', $ex->mention(50.0));
    T::same('mention 49', 'Échec', $ex->mention(49.0));
    T::same('numéro candidat Exetat', 'EXE-2027-SEC01-MP-0012', $ex->candidateNumber(2027, 'SEC-01', 'mp', 12));
    $c = new ConductService();
    $r = $c->evaluate([['severity' => 'minor'], ['severity' => 'serious']]);
    T::same('conduite : 100 - 2 - 15', 83.0, $r['score']);
    T::same('conduite : mention', 'Très bien', $r['mention']);
    T::same('conduite sans incident', 100.0, $c->evaluate([])['score']);
    T::same('conduite plancher à 0', 0.0, $c->evaluate(array_fill(0, 10, ['severity' => 'serious']))['score']);
};
