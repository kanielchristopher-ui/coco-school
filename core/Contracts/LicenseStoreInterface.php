<?php
declare(strict_types=1);

namespace CocoCore\Contracts;

use CocoCore\Licensing\License;

interface LicenseStoreInterface
{
    public function activeToken(): ?string;

    public function saveLicense(string $token, License $license, ?int $userId): void;

    public function state(string $key): ?string;

    public function setState(string $key, string $value): void;

    /** @param array<string,mixed> $details */
    public function event(string $event, array $details = []): void;

    /** Nombre d'élèves actifs du niveau. */
    public function countStudents(string $level): int;

    /** @return list<string> */
    public function revokedIds(): array;
}
