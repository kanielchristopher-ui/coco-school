<?php
declare(strict_types=1);

namespace CocoCore\Modules\Finance;

/** Statut d'un frais d'élève d'après les encaissements actifs (règle de l'application historique). */
final class FeeStatusCalculator
{
    public const PAID = 'paid';
    public const PARTIAL = 'partial';
    public const PENDING = 'pending';

    public static function status(float $amount, float $paid): string
    {
        if ($paid >= $amount - 0.0001 && $amount > 0) {
            return self::PAID;
        }
        return $paid > 0 ? self::PARTIAL : self::PENDING;
    }

    public static function remaining(float $amount, float $paid): float
    {
        return max(0.0, round($amount - $paid, 2));
    }
}
