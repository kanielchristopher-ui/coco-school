<?php
declare(strict_types=1);

namespace CocoCore\Domain;

use CocoCore\Exceptions\ValidationException;

/**
 * Modèle de domaine : une ligne de table typée, validée, immuable.
 * Les sous-classes (core/Domain/Entities/*) sont GÉNÉRÉES depuis les migrations par tools/generate_entities.py
 * — ne pas les modifier à la main (relancer le générateur après chaque migration).
 */
abstract class Entity
{
    public const TABLE = '';
    /** @var list<string> */
    public const PRIMARY_KEY = ['id'];
    /** @var array<string,string> champ => type (int, float, string, bool ; préfixe ? = nullable) */
    public const FIELDS = [];
    /** @var list<string> champs NOT NULL sans valeur par défaut (obligatoires à la création) */
    public const REQUIRED = [];
    /** @var array<string,list<string>> */
    public const ENUMS = [];
    /** @var array<string,int> longueur maximale des VARCHAR/CHAR */
    public const LENGTHS = [];

    /** @var array<string,mixed> */
    private array $data;

    /** @param array<string,mixed> $data */
    final public function __construct(array $data = [])
    {
        $this->data = static::cast($data);
    }

    /** @param array<string,mixed> $row */
    public static function fromRow(array $row): static
    {
        return new static($row);
    }

    /** @return array<string,mixed> */
    public function toRow(): array
    {
        return $this->data;
    }

    public function get(string $field): mixed
    {
        if (!array_key_exists($field, static::FIELDS)) {
            throw new \InvalidArgumentException(sprintf('Champ inconnu « %s » pour %s.', $field, static::TABLE));
        }
        return $this->data[$field] ?? null;
    }

    public function __get(string $field): mixed
    {
        return $this->get($field);
    }

    public function __isset(string $field): bool
    {
        return isset($this->data[$field]);
    }

    /** @param array<string,mixed> $changes @return static copie modifiée (l'original reste inchangé) */
    public function with(array $changes): static
    {
        return new static(array_merge($this->data, $changes));
    }

    /** @return array<string,string> champ => message ; vide si valide */
    public function validate(bool $forInsert = true): array
    {
        $errors = [];
        if ($forInsert) {
            foreach (static::REQUIRED as $f) {
                $v = $this->data[$f] ?? null;
                if ($v === null || $v === '') {
                    $errors[$f] = 'Champ obligatoire.';
                }
            }
        }
        foreach (static::ENUMS as $f => $allowed) {
            $v = $this->data[$f] ?? null;
            if ($v !== null && !in_array((string) $v, $allowed, true)) {
                $errors[$f] = 'Valeur non autorisée (' . implode(', ', $allowed) . ').';
            }
        }
        foreach (static::LENGTHS as $f => $max) {
            $v = $this->data[$f] ?? null;
            if (is_string($v) && mb_strlen($v) > $max) {
                $errors[$f] = sprintf('%d caractères maximum.', $max);
            }
        }
        return $errors;
    }

    public function assertValid(bool $forInsert = true): void
    {
        $errors = $this->validate($forInsert);
        if ($errors) {
            throw new ValidationException($errors);
        }
    }

    /** @param array<string,mixed> $row @return array<string,mixed> */
    private static function cast(array $row): array
    {
        $out = [];
        foreach (static::FIELDS as $field => $type) {
            if (!array_key_exists($field, $row)) {
                continue;
            }
            $v = $row[$field];
            $nullable = $type[0] === '?';
            $base = ltrim($type, '?');
            if ($v === null || ($v === '' && $nullable && $base !== 'string')) {
                $out[$field] = null;
                continue;
            }
            $out[$field] = match ($base) {
                'int' => (int) $v,
                'float' => (float) $v,
                'bool' => (bool) (int) $v,
                default => (string) $v,
            };
        }
        return $out;
    }
}
