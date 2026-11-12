<?php
declare(strict_types=1);

namespace CocoCore\Modules\Grading;

/**
 * Schéma d'évaluation (RDC : primaire = 3 trimestres, secondaire = 2 semestres).
 * Chaque étape a un maximum et un poids EXPLICITES : aucun total n'est calculé en
 * supposant des périodes égales.
 */
final class EvaluationScheme
{
    /** @param array<string,mixed> $config */
    public function __construct(private array $config)
    {
    }

    /** @return array<string,mixed> */
    public function config(): array
    {
        return $this->config;
    }

    /** @return list<array<string,mixed>> */
    public function terms(): array
    {
        return $this->config['terms'] ?? [];
    }

    /** Étapes saisissables (période, examen) indexées par id. */
    public function inputSteps(): array
    {
        $out = [];
        foreach ($this->terms() as $term) {
            foreach ($term['steps'] as $step) {
                if (in_array($step['type'], ['period', 'exam'], true)) {
                    $out[$step['id']] = [
                        'id' => $step['id'], 'label' => $step['label'], 'type' => $step['type'], 'term' => $term['id'],
                        'term_label' => $term['label'], 'max' => (float) ($step['max_points'] ?? 10), 'weight' => (float) ($step['weight'] ?? 1),
                    ];
                }
            }
        }
        return $out;
    }

    /** Périodes de bulletin : chaque trimestre/semestre + « YEAR » si un total général existe. */
    public function periods(): array
    {
        $list = [];
        foreach ($this->terms() as $term) {
            $list[$term['id']] = $term['label'];
        }
        if (!empty($this->config['final_steps'])) {
            $list['YEAR'] = $this->config['final_steps'][0]['label'] ?? 'Total général';
        }
        return $list;
    }

    public function termOfStep(string $stepId): ?string
    {
        return $this->inputSteps()[$stepId]['term'] ?? null;
    }

    /** @return array<string,mixed> */
    public function finalStep(): array
    {
        return $this->config['final_steps'][0] ?? ['id' => 'TOTAL_GENERAL', 'label' => 'Total général'];
    }
}
