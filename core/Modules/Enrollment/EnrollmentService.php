<?php
declare(strict_types=1);

namespace CocoCore\Modules\Enrollment;

use CocoCore\Contracts\AuditLogInterface;
use CocoCore\Contracts\EnrollmentStoreInterface;
use CocoCore\Contracts\EventDispatcherInterface;
use CocoCore\Contracts\LicenseGuardInterface;
use CocoCore\Contracts\TransactionInterface;
use CocoCore\Events\DomainEvent;
use CocoCore\Exceptions\BusinessRuleException;
use CocoCore\Exceptions\NotFoundException;
use CocoCore\Exceptions\PermissionDeniedException;
use CocoCore\Levels\ClassLadder;
use CocoCore\Levels\LevelRegistry;
use CocoCore\Levels\SchoolLevel;
use CocoCore\Security\Actor;
use CocoCore\Security\AuthorizationService;

/**
 * Inscription d'un élève dans une classe pour une année scolaire.
 * Règles : licence (niveau couvert + plafond d'élèves), accès à l'établissement, élève actif, une seule inscription par année,
 * âge d'admission en première classe du niveau (3 ans maternel, 6 ans primaire), effectif maximal de la division.
 */
final class EnrollmentService
{
    public function __construct(
        private EnrollmentStoreInterface $store,
        private AuthorizationService $authorization,
        private LicenseGuardInterface $license,
        private TransactionInterface $tx,
        private EventDispatcherInterface $events,
        private AuditLogInterface $audit
    ) {
    }

    /** Âge en années révolues à la date de référence. */
    public static function ageAt(string $dob, \DateTimeImmutable $reference): int
    {
        return (int) (new \DateTimeImmutable($dob))->diff($reference)->format('%y');
    }

    /**
     * @param \DateTimeImmutable $ageReference date de référence de l'âge (par défaut : date d'inscription)
     * @return int identifiant de l'inscription
     */
    public function enroll(Actor $actor, int $studentId, int $classId, int $yearId, \DateTimeImmutable $on, ?int $sectionId = null, ?\DateTimeImmutable $ageReference = null): int
    {
        $this->authorization->authorize($actor, 'students.manage');

        $class = $this->store->classInfo($classId, $sectionId) ?? throw new NotFoundException('Classe introuvable.');
        $level = SchoolLevel::parse($class['cycle']);
        if ($class['establishment_id'] !== null && !$actor->canAccessEstablishment((int) $class['establishment_id'])) {
            throw new PermissionDeniedException('Vous n\'avez pas accès à cet établissement.');
        }
        $this->license->requireQuota($level->value);

        $student = $this->store->studentInfo($studentId) ?? throw new NotFoundException('Élève introuvable.');
        if ($student['status'] !== 'active') {
            throw new BusinessRuleException('Seul un élève actif peut être inscrit.');
        }

        // âge d'admission : exigé à l'entrée du niveau (M1, P1, EB7) ; au-delà, seul l'âge maximal est signalé
        $code = ClassLadder::codeFromName($class['name']);
        $profile = LevelRegistry::profile($level);
        if ($student['dob'] !== null && $code !== null && $code === $profile->classLadder()[0]['code']) {
            $age = self::ageAt($student['dob'], $ageReference ?? $on);
            $range = $profile->admissionAge();
            if ($age < $range['min']) {
                throw new BusinessRuleException(sprintf('Âge insuffisant : %d ans (minimum %d ans révolus pour %s).', $age, $range['min'], $class['name']));
            }
        }

        $cap = ClassCapacity::check($level->value, $this->store->enrolledCount($classId, $yearId), $class['section_capacity']);
        if (!$cap['ok']) {
            throw new BusinessRuleException(sprintf('Classe complète (%d élèves maximum).', $cap['limit']));
        }

        $id = $this->tx->transaction(function () use ($studentId, $classId, $sectionId, $yearId, $on, $class): int {
            if ($this->store->hasEnrollment($studentId, $yearId)) {
                throw new BusinessRuleException('Cet élève est déjà inscrit pour cette année scolaire.');
            }
            $id = $this->store->insertEnrollment([
                'student_id' => $studentId, 'class_id' => $classId, 'section_id' => $sectionId, 'establishment_id' => (int) $class['establishment_id'],
                'academic_year_id' => $yearId, 'enrolled_on' => $on->format('Y-m-d'), 'status' => 'enrolled',
            ]);
            $this->store->setStudentClass($studentId, $classId, $sectionId);
            return $id;
        });
        $this->audit->record($actor->id, 'enroll', 'student_enrollment', $id, ['student_id' => $studentId, 'class_id' => $classId, 'year' => $yearId]);
        $this->events->dispatch(new DomainEvent('student.enrolled', ['enrollment_id' => $id, 'student_id' => $studentId, 'class_id' => $classId, 'level' => $level->value]));
        return $id;
    }
}
