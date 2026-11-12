<?php
declare(strict_types=1);

use CocoCore\Modules\Attendance\AttendanceStatistics;
use CocoCore\Modules\Documents\CertificateService;
use CocoCore\Modules\Enrollment\ClassCapacity;
use CocoCore\Modules\Finance\FeePlanService;
use CocoCore\Modules\Finance\PayrollCalculator;
use CocoCore\Modules\Grading\GradeScale;
use CocoCore\Modules\Identity\IdGenerator;
use CocoCore\Modules\Ranking\RankingService;
use CocoCore\Modules\Statistics\ResultStatistics;
use CocoCore\Modules\Timetable\ConflictDetector;

return function (): void {
    // classement
    $r = (new RankingService())->rank(['a' => 80.0, 'b' => 90.0, 'c' => 80.0, 'd' => 70.0]);
    T::same('classement avec ex æquo', ['b' => 1, 'a' => 2, 'c' => 2, 'd' => 4], $r);
    T::same('classement vide', [], (new RankingService())->rank([]));
    T::same('ordinaux', ['1er', '2ème', '10ème'], array_map([new RankingService(), 'ordinal'], [1, 2, 10]));

    // statistiques
    $scale = new GradeScale([
        ['grade' => 'E', 'min_percent' => 90, 'max_percent' => 100, 'point' => 4, 'remarks' => 'Excellent'],
        ['grade' => 'TB', 'min_percent' => 80, 'max_percent' => 89.99, 'point' => 3.5, 'remarks' => 'Très bien'],
        ['grade' => 'B', 'min_percent' => 70, 'max_percent' => 79.99, 'point' => 3, 'remarks' => 'Bien'],
        ['grade' => 'AB', 'min_percent' => 60, 'max_percent' => 69.99, 'point' => 2.5, 'remarks' => 'Assez bien'],
        ['grade' => 'S', 'min_percent' => 50, 'max_percent' => 59.99, 'point' => 2, 'remarks' => 'Satisfaisant'],
        ['grade' => 'Éch', 'min_percent' => 0, 'max_percent' => 49.99, 'point' => 0, 'remarks' => 'Échec'],
    ]);
    $stats = (new ResultStatistics($scale))->summarize([
        ['percent' => 90.0, 'gender' => 'female'], ['percent' => 80.0, 'gender' => 'male'], ['percent' => 60.0, 'gender' => 'female'],
        ['percent' => 50.0, 'gender' => 'male'], ['percent' => 40.0, 'gender' => 'male'],
    ]);
    T::same('effectif', 5, $stats['count']);
    T::same('moyenne', 64.0, $stats['mean']);
    T::same('médiane', 60.0, $stats['median']);
    T::same('min / max', [40.0, 90.0], [$stats['min'], $stats['max']]);
    T::same('écart-type', 18.55, $stats['std_dev']);
    T::same('taux de réussite', 80.0, $stats['pass_rate']);
    T::same('par mention', ['E' => 1, 'TB' => 1, 'AB' => 1, 'S' => 1, 'Éch' => 1], $stats['by_mention']);
    T::same('réussite filles', 100.0, $stats['by_gender']['female']['pass_rate']);
    T::same('réussite garçons', 66.7, $stats['by_gender']['male']['pass_rate']);
    T::same('médiane paire', 55.0, (new ResultStatistics($scale))->summarize([['percent' => 50.0], ['percent' => 60.0]])['median']);
    T::same('statistiques vides', 0, (new ResultStatistics($scale))->summarize([])['count']);

    // assiduité
    $a = new AttendanceStatistics();
    T::same('taux de présence (retards comptés présents)', 90.0, $a->rate(['present' => 8, 'late' => 1, 'absent' => 1, 'leave' => 5]));
    T::same('sans données : 100 %', 100.0, $a->rate([]));
    T::same('alertes d\'absences', [1 => 5, 3 => 3], $a->alerts([1 => 5, 2 => 2, 3 => 3], 3));

    // frais
    $f = new FeePlanService();
    $plans = [['fee_type' => 'Minerval', 'amount' => '30.00', 'frequency' => 'term', 'class_id' => null], ['fee_type' => 'Inscription', 'amount' => 20.0, 'frequency' => 'one_time', 'class_id' => null],
              ['fee_type' => 'Labo', 'amount' => 10.0, 'frequency' => 'term', 'class_id' => 18]];
    $l = $f->invoiceLines($plans, 4, 1);
    T::same('barème : lignes pour la classe 4', 2, count($l));
    T::same('1er enfant : pas de réduction', 30.0, $l[0]['net']);
    $l = $f->invoiceLines($plans, 4, 2);
    T::same('2ème enfant : -10 % sur le minerval', 27.0, $l[0]['net']);
    T::same('pas de réduction sur l\'inscription', 20.0, $l[1]['net']);
    $l = $f->invoiceLines($plans, 4, 3);
    T::same('3ème enfant : -20 %', 24.0, $l[0]['net']);
    $l = $f->invoiceLines($plans, 4, 2, 50.0);
    T::same('bourse 50 % après réduction fratrie', 13.5, $l[0]['net']);
    T::same('bourse : pas sur l\'inscription', 20.0, $l[1]['net']);
    T::same('frais propre à une classe', 3, count($f->invoiceLines($plans, 18, 1)));
    $due = new DateTimeImmutable('2026-10-15');
    T::same('pénalité : 2 mois entiers à 2 %', 4.0, $f->lateFee(100.0, $due, new DateTimeImmutable('2026-12-20')));
    T::same('pas de pénalité avant l\'échéance', 0.0, $f->lateFee(100.0, $due, new DateTimeImmutable('2026-10-15')));
    T::same('pas de pénalité si soldé', 0.0, $f->lateFee(0.0, $due, new DateTimeImmutable('2027-01-01')));
    T::same('échéancier 100 / 3', [33.33, 33.33, 33.34], $f->schedule(100.0, 3));
    T::same('échéancier 90 / 3', [30.0, 30.0, 30.0], $f->schedule(90.0, 3));
    T::same('échéancier : somme exacte', 100.0, round(array_sum($f->schedule(100.0, 7)), 2));
    T::throws('échéancier : 0 tranche', InvalidArgumentException::class, fn() => $f->schedule(10.0, 0));

    // paie
    $p = (new PayrollCalculator(['social_rate' => 5.0, 'tax_rate' => 3.0]))->compute(200.0, [20.0, 30.0], [10.0]);
    T::same('paie : brut', 250.0, $p['gross']);
    T::same('paie : retenues', 30.0, $p['deductions']);
    T::same('paie : net', 220.0, $p['net']);
    T::same('paie sans taux configurés', 250.0, (new PayrollCalculator())->compute(250.0)['net']);
    T::same('paie : net jamais négatif', 0.0, (new PayrollCalculator())->compute(10.0, [], [50.0])['net']);

    // horaires
    $slots = [
        ['id' => 1, 'day' => 1, 'start' => '08:00', 'end' => '09:00', 'teacher_id' => 1, 'class_id' => 1],
        ['id' => 2, 'day' => 1, 'start' => '08:30', 'end' => '09:30', 'teacher_id' => 1, 'class_id' => 2, 'room' => 'R1'],
        ['id' => 3, 'day' => 1, 'start' => '09:00', 'end' => '10:00', 'teacher_id' => 2, 'class_id' => 1, 'room' => 'R1'],
        ['id' => 4, 'day' => 2, 'start' => '08:00', 'end' => '09:00', 'teacher_id' => 1, 'class_id' => 1],
    ];
    T::same('conflits : enseignant + salle ; créneaux contigus et autre jour sans conflit', [['kind' => 'teacher', 'a' => 1, 'b' => 2], ['kind' => 'room', 'a' => 2, 'b' => 3]], (new ConflictDetector())->find($slots));
    T::same('conflit de classe', [['kind' => 'class', 'a' => 1, 'b' => 2]], (new ConflictDetector())->find([
        ['id' => 1, 'day' => 3, 'start' => '10:00', 'end' => '11:00', 'class_id' => 5, 'section_id' => 1],
        ['id' => 2, 'day' => 3, 'start' => '10:30', 'end' => '11:30', 'class_id' => 5, 'section_id' => 1],
    ]));
    T::same('divisions différentes : pas de conflit', [], (new ConflictDetector())->find([
        ['id' => 1, 'day' => 3, 'start' => '10:00', 'end' => '11:00', 'class_id' => 5, 'section_id' => 1],
        ['id' => 2, 'day' => 3, 'start' => '10:00', 'end' => '11:00', 'class_id' => 5, 'section_id' => 2],
    ]));

    // effectifs
    T::same('maternel 29/30', ['ok' => true, 'limit' => 30, 'free' => 1], ClassCapacity::check('maternel', 29));
    T::check('maternel 30/30 : complet', !ClassCapacity::check('maternel', 30)['ok']);
    T::check('capacité de la division prioritaire', !ClassCapacity::check('primaire', 20, 20)['ok'] && ClassCapacity::check('primaire', 20)['ok']);

    // identité et attestations
    T::same('identifiant officiel', 'ELV-0010', IdGenerator::official('ELV', 9));
    T::throws('préfixe invalide', InvalidArgumentException::class, fn() => IdGenerator::official('x1', 1));
    T::check('PERM plausible', IdGenerator::isPlausiblePerm('PERM-100001') && !IdGenerator::isPlausiblePerm('12'));
    $cert = new CertificateService();
    $school = ['school_name' => 'CSEC', 'school_code' => 'PRI-01', 'head_title' => 'Directeur', 'head_name' => 'M. X', 'city' => 'Kinshasa'];
    $stu = ['full_name' => 'Mbuyi Tshibola Jonathan', 'dob' => '2019-05-12', 'birth_place' => 'Kinshasa', 'class_name' => '1ère Primaire', 'perm_number' => 'PERM-100001'];
    $d = $cert->build('attendance', $school, $stu, '2026-2027', 1, new DateTimeImmutable('2026-11-08'));
    T::same('référence d\'attestation', 'ATF-PRI-01-20262027-0001', $d['reference']);
    T::throws('attestation de réussite sans pourcentage', InvalidArgumentException::class, fn() => $cert->build('success', $school, $stu, '2026-2027', 1, new DateTimeImmutable('2026-11-08')));
    T::same('attestation de réussite', 'ATR-PRI-01-20262027-0002', $cert->build('success', $school, $stu, '2026-2027', 2, new DateTimeImmutable('2026-11-08'), 71.5)['reference']);
    T::throws('type inconnu', InvalidArgumentException::class, fn() => $cert->build('diplome', $school, $stu, '2026-2027', 1, new DateTimeImmutable('2026-11-08')));
};
