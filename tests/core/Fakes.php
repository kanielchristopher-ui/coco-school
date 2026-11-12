<?php
declare(strict_types=1);

use CocoCore\Contracts\AuditLogInterface;
use CocoCore\Contracts\EnrollmentStoreInterface;
use CocoCore\Contracts\FeeLedgerRepositoryInterface;
use CocoCore\Contracts\LicenseGuardInterface;
use CocoCore\Contracts\LicenseStoreInterface;
use CocoCore\Exceptions\LicenseException;
use CocoCore\Licensing\License;
use CocoCore\Contracts\PermissionRepositoryInterface;
use CocoCore\Contracts\TransactionInterface;
use CocoCore\Contracts\UserRepositoryInterface;

/** Doublures en mémoire : le Core se teste SANS base de données ni Web. */
final class FakePermissions implements PermissionRepositoryInterface
{
    public function __construct(private array $matrix)
    {
    }

    public function matrix(): array
    {
        return $this->matrix;
    }
}

final class FakeTx implements TransactionInterface
{
    public int $runs = 0;

    public function transaction(callable $work): mixed
    {
        $this->runs++;
        return $work();
    }
}

final class FakeAudit implements AuditLogInterface
{
    public array $entries = [];

    public function record(?int $actorId, string $action, string $entity, ?int $entityId, array $details = []): void
    {
        $this->entries[] = compact('actorId', 'action', 'entity', 'entityId', 'details');
    }
}

final class FakeLedger implements FeeLedgerRepositoryInterface
{
    public array $fees = [];
    public array $collections = [];

    public function findStudentFee(int $feeId): ?array
    {
        return $this->fees[$feeId] ?? null;
    }

    public function activePaid(array $fee): float
    {
        $t = 0.0;
        foreach ($this->collections as $c) {
            if ($c['student_id'] === $fee['student_id'] && $c['fee_type'] === $fee['fee_type']) {
                $t += (float) $c['amount_paid'];
            }
        }
        return $t;
    }

    public function nextReceiptNo(int $year): string
    {
        return sprintf('RCT-%d-%04d', $year, count($this->collections) + 1);
    }

    public function insertCollection(array $collection): int
    {
        $this->collections[] = $collection;
        return count($this->collections);
    }

    public function setFeeStatus(int $feeId, string $status): void
    {
        $this->fees[$feeId]['status'] = $status;
    }
}

final class FakeUsers implements UserRepositoryInterface
{
    public array $rehashed = [];

    public function __construct(private array $users)
    {
    }

    public function findByLogin(string $login): ?array
    {
        foreach ($this->users as $u) {
            if ($u['login'] === $login) {
                return $u;
            }
        }
        return null;
    }

    public function updatePasswordHash(int $userId, string $hash): void
    {
        $this->rehashed[$userId] = $hash;
    }

    public array $establishments = [];

    public function establishmentIds(int $userId): array
    {
        return $this->establishments[$userId] ?? [];
    }
}

final class FakeLicenseStore implements LicenseStoreInterface
{
    public ?string $token = null;
    public array $state = [];
    public array $events = [];
    public array $students = [];
    public array $revoked = [];
    public ?License $saved = null;

    public function activeToken(): ?string
    {
        return $this->token;
    }

    public function saveLicense(string $token, License $license, ?int $userId): void
    {
        $this->token = $token;
        $this->saved = $license;
    }

    public function state(string $key): ?string
    {
        return $this->state[$key] ?? null;
    }

    public function setState(string $key, string $value): void
    {
        $this->state[$key] = $value;
    }

    public function event(string $event, array $details = []): void
    {
        $this->events[] = $event;
    }

    public function countStudents(string $level): int
    {
        return $this->students[$level] ?? 0;
    }

    public function revokedIds(): array
    {
        return $this->revoked;
    }
}

final class DenyingGuard implements LicenseGuardInterface
{
    public function requireWrite(?string $level = null, ?string $module = null): void
    {
        throw new LicenseException('Licence expirée.', 'expired');
    }

    public function requireQuota(string $level, int $adding = 1): void
    {
        throw new LicenseException('Plafond atteint.', 'quota');
    }
}

final class FakeEnrollmentStore implements EnrollmentStoreInterface
{
    public array $students = [];
    public array $classes = [];
    public array $enrolled = [];       // "classId:yearId" => count
    public array $rows = [];
    public array $studentClass = [];

    public function studentInfo(int $studentId): ?array
    {
        return $this->students[$studentId] ?? null;
    }

    public function classInfo(int $classId, ?int $sectionId = null): ?array
    {
        return $this->classes[$classId] ?? null;
    }

    public function enrolledCount(int $classId, int $yearId): int
    {
        return $this->enrolled["$classId:$yearId"] ?? 0;
    }

    public function hasEnrollment(int $studentId, int $yearId): bool
    {
        foreach ($this->rows as $r) {
            if ($r['student_id'] === $studentId && $r['academic_year_id'] === $yearId) {
                return true;
            }
        }
        return false;
    }

    public function insertEnrollment(array $row): int
    {
        $this->rows[] = $row;
        return count($this->rows);
    }

    public function setStudentClass(int $studentId, int $classId, ?int $sectionId): void
    {
        $this->studentClass[$studentId] = $classId;
    }
}
