<?php
declare(strict_types=1);

namespace CocoCore\Modules\Finance;

/** Calculs de frais scolaires : barème par établissement, réduction fratrie, bourse, pénalité de retard, échéancier. */
final class FeePlanService
{
    /** Réduction par rang dans la fratrie inscrite : 1er enfant 0 %, 2ème 10 %, 3ème et suivants 20 % (valeurs par défaut à valider). */
    public const SIBLING_DISCOUNT = [1 => 0.0, 2 => 10.0, 3 => 20.0];

    public function siblingDiscountPercent(int $siblingRank): float
    {
        return self::SIBLING_DISCOUNT[min(max(1, $siblingRank), 3)];
    }

    /**
     * Lignes de facture d'un élève.
     * @param list<array{fee_type:string,amount:float|string,frequency:string,class_id:?int}> $plans barèmes actifs de l'établissement
     * @return list<array{fee_type:string,frequency:string,gross:float,discount:float,scholarship:float,net:float}>
     */
    public function invoiceLines(array $plans, int $classId, int $siblingRank = 1, float $scholarshipPercent = 0.0): array
    {
        $lines = [];
        foreach ($plans as $p) {
            if ($p['class_id'] !== null && (int) $p['class_id'] !== $classId) {
                continue;
            }
            $gross = round((float) $p['amount'], 2);
            // la réduction fratrie ne s'applique qu'aux frais récurrents (pas à l'inscription)
            $pct = $p['frequency'] === 'one_time' ? 0.0 : $this->siblingDiscountPercent($siblingRank);
            $discount = round($gross * $pct / 100, 2);
            // bourse en % du montant restant après réduction fratrie, sur les frais récurrents uniquement
            $schol = $p['frequency'] === 'one_time' ? 0.0 : round(($gross - $discount) * min(100.0, max(0.0, $scholarshipPercent)) / 100, 2);
            $lines[] = ['fee_type' => (string) $p['fee_type'], 'frequency' => (string) $p['frequency'], 'gross' => $gross, 'discount' => $discount,
                'scholarship' => $schol, 'net' => round($gross - $discount - $schol, 2)];
        }
        return $lines;
    }

    /** Pénalité de retard : $ratePercent % par mois ENTIER de retard (0 si non échu). */
    public function lateFee(float $outstanding, \DateTimeImmutable $due, \DateTimeImmutable $today, float $ratePercent = 2.0): float
    {
        if ($today <= $due || $outstanding <= 0) {
            return 0.0;
        }
        $months = (int) $due->diff($today)->format('%y') * 12 + (int) $due->diff($today)->format('%m');
        return round($outstanding * $ratePercent / 100 * $months, 2);
    }

    /** Répartit un montant en $parts tranches (la dernière absorbe l'arrondi). @return list<float> */
    public function schedule(float $total, int $parts): array
    {
        if ($parts < 1) {
            throw new \InvalidArgumentException('Au moins une tranche.');
        }
        $each = floor($total / $parts * 100) / 100;
        $out = array_fill(0, $parts, $each);
        $out[$parts - 1] = round($total - $each * ($parts - 1), 2);
        return $out;
    }
}
