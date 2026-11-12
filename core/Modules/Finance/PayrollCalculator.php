<?php
declare(strict_types=1);

namespace CocoCore\Modules\Finance;

/**
 * Paie du personnel : brut = base + indemnités ; retenues = cotisations (taux configurable) + retenues fixes ; net = brut − retenues.
 * Les taux légaux (sécurité sociale, impôt) DOIVENT être configurés selon la législation en vigueur : aucun taux n'est présumé exact.
 */
final class PayrollCalculator
{
    /** @param array{social_rate?:float,tax_rate?:float} $rates taux en % appliqués au brut */
    public function __construct(private array $rates = [])
    {
    }

    /**
     * @param list<float> $allowances @param list<float> $fixedDeductions
     * @return array{gross:float,social:float,tax:float,fixed:float,deductions:float,net:float}
     */
    public function compute(float $basic, array $allowances = [], array $fixedDeductions = []): array
    {
        $gross = round($basic + array_sum($allowances), 2);
        $social = round($gross * ($this->rates['social_rate'] ?? 0.0) / 100, 2);
        $tax = round($gross * ($this->rates['tax_rate'] ?? 0.0) / 100, 2);
        $fixed = round(array_sum($fixedDeductions), 2);
        $ded = round($social + $tax + $fixed, 2);
        return ['gross' => $gross, 'social' => $social, 'tax' => $tax, 'fixed' => $fixed, 'deductions' => $ded, 'net' => max(0.0, round($gross - $ded, 2))];
    }
}
