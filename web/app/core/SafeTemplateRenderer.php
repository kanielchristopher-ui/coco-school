<?php
/**
 * Rendu de modèles HTML déclaratifs : uniquement des {{variables}} simples,
 * toujours échappées. Aucun PHP, JS, gestionnaire d'événement ni directive.
 */

declare(strict_types=1);

final class SafeTemplateRenderer
{
    public function assertSafe(string $template): void
    {
        if (preg_match('/<\?(?:php|=)|<script\b|<iframe\b|<object\b|<embed\b|\bon[a-z]+\s*=|javascript:/i', $template)) {
            throw new CcplgException('Contenu actif interdit dans le modèle.');
        }
    }

    /**
     * @param array<string,mixed> $data  variables ; les valeurs listées dans $rawKeys sont
     *                                   du HTML déjà construit par le moteur (jamais fourni par un paquet).
     */
    public function render(string $template, array $data, array $rawKeys = []): string
    {
        $this->assertSafe($template);
        return (string) preg_replace_callback('/\{\{([a-zA-Z0-9_.-]+)\}\}/', static function (array $match) use ($data, $rawKeys): string {
            $value = $data;
            foreach (explode('.', $match[1]) as $key) {
                if (!is_array($value) || !array_key_exists($key, $value)) {
                    return '';
                }
                $value = $value[$key];
            }
            if (!is_scalar($value) && $value !== null) {
                return '';
            }
            if (in_array($match[1], $rawKeys, true)) {
                return (string) $value;
            }
            return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }, $template);
    }
}
