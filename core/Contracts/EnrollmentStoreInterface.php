<?php
declare(strict_types=1);

namespace CocoCore\Contracts;

interface EnrollmentStoreInterface
{
    /** @return array{id:int,dob:?string,status:string}|null */
    public function studentInfo(int $studentId): ?array;

    /** @return array{id:int,name:string,cycle:string,establishment_id:?int,section_capacity:?int}|null */
    public function classInfo(int $classId, ?int $sectionId = null): ?array;

    public function enrolledCount(int $classId, int $yearId): int;

    public function hasEnrollment(int $studentId, int $yearId): bool;

    /** @param array<string,mixed> $row @return int */
    public function insertEnrollment(array $row): int;

    public function setStudentClass(int $studentId, int $classId, ?int $sectionId): void;
}
