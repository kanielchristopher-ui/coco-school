<?php
declare(strict_types=1);

namespace CocoCore\Licensing;

use CocoCore\Exceptions\LicenseException;
use CocoCore\Levels\SchoolLevel;

/**
 * Contenu signé d'une licence : titulaire, validité, niveaux couverts (maternel / primaire / secondaire),
 * modules, plafonds d'élèves par niveau (0 = illimité).
 */
final class License
{
    /** @param list<string> $levels @param list<string> $modules @param array<string,int> $maxStudents */
    public function __construct(
        public readonly string $id,
        public readonly string $holder,
        public readonly \DateTimeImmutable $issuedAt,
        public readonly \DateTimeImmutable $validFrom,
        public readonly \DateTimeImmutable $validUntil,
        public readonly int $graceDays,
        public readonly string $edition,
        public readonly array $levels,
        public readonly array $modules,
        public readonly array $maxStudents
    ) {
    }

    /** @param array<string,mixed> $p */
    public static function fromArray(array $p): self
    {
        foreach (['license_id', 'holder', 'valid_from', 'valid_until'] as $k) {
            if (!isset($p[$k]) || !is_string($p[$k]) || trim($p[$k]) === '') {
                throw new LicenseException('Licence malformée : champ « ' . $k . ' » manquant.', 'malformed');
            }
        }
        $date = static function (string $v, string $field): \DateTimeImmutable {
            $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $v);
            if (!$d || $d->format('Y-m-d') !== $v) {
                throw new LicenseException('Licence malformée : date « ' . $field . ' » invalide.', 'malformed');
            }
            return $d;
        };
        $from = $date($p['valid_from'], 'valid_from');
        $until = $date($p['valid_until'], 'valid_until');
        if ($until < $from) {
            throw new LicenseException('Licence malformée : fin avant début.', 'malformed');
        }
        $levels = array_values((array) ($p['levels'] ?? []));
        foreach ($levels as $l) {
            if (SchoolLevel::tryFrom((string) $l) === null) {
                throw new LicenseException('Licence malformée : niveau « ' . $l . ' » inconnu.', 'malformed');
            }
        }
        $grace = (int) ($p['grace_days'] ?? 0);
        if ($grace < 0 || $grace > 365) {
            throw new LicenseException('Licence malformée : délai de grâce invalide.', 'malformed');
        }
        $max = [];
        foreach ((array) ($p['max_students'] ?? []) as $lvl => $n) {
            $max[(string) $lvl] = max(0, (int) $n);
        }
        return new self(
            (string) $p['license_id'], (string) $p['holder'],
            isset($p['issued_at']) ? $date((string) $p['issued_at'], 'issued_at') : $from,
            $from, $until, $grace, (string) ($p['edition'] ?? 'standard'), $levels,
            array_values((array) ($p['modules'] ?? ['*'])), $max
        );
    }

    /** Dernier instant de validité (fin de journée). */
    public function validUntilEnd(): \DateTimeImmutable
    {
        return $this->validUntil->setTime(23, 59, 59);
    }

    public function graceEnd(): \DateTimeImmutable
    {
        return $this->validUntil->modify('+' . $this->graceDays . ' days')->setTime(23, 59, 59);
    }

    public function allowsLevel(SchoolLevel|string $level): bool
    {
        $v = $level instanceof SchoolLevel ? $level->value : SchoolLevel::parse($level)->value;
        return in_array($v, $this->levels, true);
    }

    public function allowsModule(string $module): bool
    {
        return in_array('*', $this->modules, true) || in_array($module, $this->modules, true);
    }

    /** Plafond d'élèves actifs pour un niveau ; 0 = illimité. */
    public function studentLimit(SchoolLevel|string $level): int
    {
        $v = $level instanceof SchoolLevel ? $level->value : SchoolLevel::parse($level)->value;
        return $this->maxStudents[$v] ?? ($this->maxStudents['*'] ?? 0);
    }
}
