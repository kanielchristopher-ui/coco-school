<?php
/**
 * Rendu de modèles de bulletin déclaratifs.
 *  - {{cle.sous_cle}}  : valeur simple, TOUJOURS échappée.
 *  - {{{bloc}}}        : bloc HTML déjà construit et échappé par l'application
 *                        (uniquement les noms de $blocks : tableau des notes, signatures…).
 * Aucun PHP, JS, attribut on*= ou lien javascript: n'est accepté.
 */
class SafeTemplate
{
    public function render(string $template, array $data, array $blocks = []): string
    {
        if (preg_match('/<\?(?:php|=)|<script\b|\bon[a-z]+\s*=|javascript:/i', $template)) {
            throw new CcplgException('Active content forbidden in the template.');
        }
        $template = preg_replace_callback('/\{\{\{([a-z_]+)\}\}\}/', static function (array $m) use ($blocks): string {
            return (string) ($blocks[$m[1]] ?? '');
        }, $template) ?? '';

        return preg_replace_callback('/\{\{([a-zA-Z0-9_.-]+)\}\}/', static function (array $m) use ($data): string {
            $value = $data;
            foreach (explode('.', $m[1]) as $key) {
                if (!is_array($value) || !array_key_exists($key, $value)) {
                    return '';
                }
                $value = $value[$key];
            }
            if (!is_scalar($value) && $value !== null) {
                return '';
            }
            return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }, $template) ?? '';
    }
}
