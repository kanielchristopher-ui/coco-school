<?php
declare(strict_types=1);

namespace CocoCore\Modules\Documents;

/** Données des attestations officielles (le rendu PDF/HTML est fait par la couche d'affichage). */
final class CertificateService
{
    public const TYPES = ['attendance' => 'ATF', 'success' => 'ATR', 'conduct' => 'ATC'];

    /**
     * @param array{school_name:string,school_code:string,head_title:string,head_name:?string,city:?string} $school
     * @param array{full_name:string,dob:?string,birth_place:?string,class_name:string,perm_number:?string} $student
     * @return array<string,mixed>
     */
    public function build(string $type, array $school, array $student, string $yearLabel, int $sequence, \DateTimeImmutable $issuedOn, ?float $percent = null): array
    {
        if (!isset(self::TYPES[$type])) {
            throw new \InvalidArgumentException('Type d\'attestation inconnu : ' . $type);
        }
        if ($type === 'success' && $percent === null) {
            throw new \InvalidArgumentException('Le pourcentage est requis pour une attestation de réussite.');
        }
        return [
            'reference' => sprintf('%s-%s-%s-%04d', self::TYPES[$type], strtoupper($school['school_code']), preg_replace('/\D/', '', $yearLabel), $sequence),
            'type' => $type, 'school' => $school, 'student' => $student, 'year' => $yearLabel,
            'percent' => $percent, 'issued_on' => $issuedOn->format('Y-m-d'), 'place' => $school['city'] ?? '',
        ];
    }
}
