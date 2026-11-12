<?php
declare(strict_types=1);

namespace CocoCore\Plugins\Templates;

use CocoCore\Plugins\CCPLG\PackageException;

/**
 * Moteur de modèles DÉCLARATIF et sûr (aucun PHP / JS exécuté).
 *
 * Syntaxe prise en charge :
 *   {{chemin.vers.valeur}}                       valeur scalaire, toujours échappée (HTML)
 *   {{#each liste as alias}} ... {{/each}}       boucle (imbriquable)  — {{alias.champ}}, {{alias._index}}
 *   {{#if chemin}} ... {{/if}}                   bloc conditionnel (sans « sinon »)
 * Les variables viennent uniquement du tableau de données fourni par l'application.
 */
final class SafeTemplateRenderer
{
    private const MAX_DEPTH = 6;
    private const FORBIDDEN = '/<\?(?:php|=)|<script\b|<iframe\b|<object\b|<embed\b|\bon[a-z]+\s*=|javascript:|@import|expression\s*\(|url\s*\(\s*[\'"]?\s*(?:https?:)?\/\//i';

    public function assertSafe(string $content): void
    {
        if (preg_match(self::FORBIDDEN, $content)) {
            throw new PackageException('Contenu actif ou ressource externe interdit dans le modèle.');
        }
    }

    public function render(string $template, array $data): string
    {
        $this->assertSafe($template);
        return $this->block($template, $data, 0);
    }

    private function block(string $tpl, array $ctx, int $depth): string
    {
        if ($depth > self::MAX_DEPTH) {
            throw new PackageException('Boucles trop imbriquées dans le modèle.');
        }
        $out = '';
        $pos = 0;
        $open = '/\{\{#(each|if)\s+([a-zA-Z0-9_.-]+)(?:\s+as\s+([a-zA-Z0-9_]+))?\s*\}\}/';
        while (preg_match($open, $tpl, $m, PREG_OFFSET_CAPTURE, $pos)) {
            $start = $m[0][1];
            $out .= $this->scalars(substr($tpl, $pos, $start - $pos), $ctx);
            $bodyStart = $start + strlen($m[0][0]);
            [$bodyEnd, $closeLen] = $this->findClose($tpl, $bodyStart, $m[1][0]);
            $body  = substr($tpl, $bodyStart, $bodyEnd - $bodyStart);
            $value = $this->get($ctx, $m[2][0]);

            if ($m[1][0] === 'each') {
                $alias = $m[3][0] ?? 'item';
                if (is_array($value)) {
                    foreach (array_values($value) as $i => $item) {
                        $c = $ctx;
                        $c[$alias] = is_array($item) ? $item + ['_index' => $i + 1] : $item;
                        $out .= $this->block($body, $c, $depth + 1);
                    }
                }
            } elseif (!empty($value)) {
                $out .= $this->block($body, $ctx, $depth + 1);
            }
            $pos = $bodyEnd + $closeLen;
        }
        return $out . $this->scalars(substr($tpl, $pos), $ctx);
    }

    /** @return array{0:int,1:int} position et longueur de la balise fermante correspondante */
    private function findClose(string $tpl, int $from, string $kind): array
    {
        $depth = 1;
        if (!preg_match_all('/\{\{(#(?:each|if)\b[^}]*|\/(?:each|if))\}\}/', $tpl, $all, PREG_OFFSET_CAPTURE | PREG_SET_ORDER, $from)) {
            throw new PackageException('Bloc {{#' . $kind . '}} non fermé.');
        }
        foreach ($all as $tag) {
            $name = $tag[1][0];
            if ($name === '#' . $kind || str_starts_with($name, '#' . $kind . ' ')) {
                $depth++;
            } elseif ($name === '/' . $kind) {
                $depth--;
                if ($depth === 0) {
                    return [$tag[0][1], strlen($tag[0][0])];
                }
            }
        }
        throw new PackageException('Bloc {{#' . $kind . '}} non fermé.');
    }

    private function scalars(string $text, array $ctx): string
    {
        return preg_replace_callback('/\{\{([a-zA-Z0-9_.-]+)\}\}/', function (array $m) use ($ctx): string {
            $value = $this->get($ctx, $m[1]);
            if (!is_scalar($value) && $value !== null) {
                return '';
            }
            return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }, $text) ?? '';
    }

    private function get(array $ctx, string $path): mixed
    {
        $value = $ctx;
        foreach (explode('.', $path) as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return null;
            }
            $value = $value[$key];
        }
        return $value;
    }
}
