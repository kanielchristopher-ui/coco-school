<?php
declare(strict_types=1);

namespace CocoCore\Modules\Identity;

/** Identifiants : officiel (ELV-0001 / ENS-0001 / PAR-0001) et contrôle souple du numéro PERM. */
final class IdGenerator
{
    public static function official(string $prefix, int $lastNumber): string
    {
        if (!preg_match('/^[A-Z]{2,5}$/', $prefix)) {
            throw new \InvalidArgumentException('Préfixe invalide.');
        }
        return sprintf('%s-%04d', $prefix, $lastNumber + 1);
    }

    /** Le format exact du numéro PERM dépend de l'inspection : on exige seulement 6 à 20 caractères alphanumériques ou tirets. */
    public static function isPlausiblePerm(string $perm): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9\-]{6,20}$/', $perm);
    }
}
