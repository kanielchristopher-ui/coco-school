<?php
declare(strict_types=1);

namespace CocoCore\Repositories;

use CocoCore\Database\Connection;
use CocoCore\Domain\Entity;

/**
 * Dépôt générique pour TOUS les modèles du Core : requêtes paramétrées, colonnes validées par liste blanche (FIELDS).
 * Critères : ['col' => valeur] | ['col' => null] (IS NULL) | ['col' => ['>=', 5]] | ['col' => ['IN', [1,2]]] | ['col' => ['LIKE', '%a%']]
 */
final class EntityRepository
{
    private const OPS = ['=', '!=', '<', '<=', '>', '>=', 'LIKE', 'IN', 'NOT IN'];

    /** @param class-string<Entity> $entity */
    public function __construct(private Connection $db, private string $entity)
    {
        if (!is_subclass_of($entity, Entity::class)) {
            throw new \InvalidArgumentException($entity . ' n\'est pas un modèle du Core.');
        }
    }

    /** @param int|string|array<string,int|string> $id */
    public function find(int|string|array $id): ?Entity
    {
        $e = $this->entity;
        $keys = $e::PRIMARY_KEY;
        $values = is_array($id) ? $id : [$keys[0] => $id];
        $row = $this->db->fetch('SELECT * FROM `' . $e::TABLE . '` WHERE ' . $this->where($values, $params) . ' LIMIT 1', $params);
        return $row === null ? null : $e::fromRow($row);
    }

    /** @param array<string,mixed> $criteria @return list<Entity> */
    public function findBy(array $criteria = [], ?string $orderBy = null, ?int $limit = null, int $offset = 0): array
    {
        $e = $this->entity;
        $sql = 'SELECT * FROM `' . $e::TABLE . '`';
        $params = [];
        if ($criteria) {
            $sql .= ' WHERE ' . $this->where($criteria, $params);
        }
        if ($orderBy !== null) {
            $sql .= ' ORDER BY ' . $this->order($orderBy);
        }
        if ($limit !== null) {
            $sql .= ' LIMIT ' . max(0, $limit) . ' OFFSET ' . max(0, $offset);
        }
        return array_map(fn(array $r) => $e::fromRow($r), $this->db->fetchAll($sql, $params));
    }

    public function count(array $criteria = []): int
    {
        $e = $this->entity;
        $params = [];
        $sql = 'SELECT COUNT(*) FROM `' . $e::TABLE . '`' . ($criteria ? ' WHERE ' . $this->where($criteria, $params) : '');
        return (int) $this->db->fetchValue($sql, $params);
    }

    /** @param Entity|array<string,mixed> $data @return int identifiant créé (0 si clé composite) */
    public function insert(Entity|array $data): int
    {
        $e = $this->entity;
        $entity = $data instanceof Entity ? $data : new $e($data);
        $entity->assertValid(true);
        $row = array_filter($entity->toRow(), fn($v) => $v !== null);
        foreach ($row as $k => $v) {
            if (is_bool($v)) {
                $row[$k] = (int) $v;
            }
        }
        return $this->db->insert($e::TABLE, $row);
    }

    /** @param int|string $id @param array<string,mixed> $changes @return int lignes modifiées */
    public function update(int|string $id, array $changes): int
    {
        $e = $this->entity;
        if (!$changes) {
            return 0;
        }
        $sets = [];
        $params = [];
        foreach ($changes as $col => $v) {
            $this->assertField((string) $col);
            $sets[] = '`' . $col . '` = ?';
            $params[] = is_bool($v) ? (int) $v : $v;
        }
        $params[] = $id;
        return $this->db->run('UPDATE `' . $e::TABLE . '` SET ' . implode(', ', $sets) . ' WHERE `' . $e::PRIMARY_KEY[0] . '` = ?', $params)->rowCount();
    }

    public function delete(int|string $id): int
    {
        $e = $this->entity;
        return $this->db->run('DELETE FROM `' . $e::TABLE . '` WHERE `' . $e::PRIMARY_KEY[0] . '` = ?', [$id])->rowCount();
    }

    private function assertField(string $col): void
    {
        $e = $this->entity;
        if (!array_key_exists($col, $e::FIELDS)) {
            throw new \InvalidArgumentException(sprintf('Colonne inconnue « %s » pour %s.', $col, $e::TABLE));
        }
    }

    /** @param array<string,mixed> $criteria @param array<int,mixed> $params */
    private function where(array $criteria, ?array &$params): string
    {
        $params = [];
        $parts = [];
        foreach ($criteria as $col => $cond) {
            $this->assertField((string) $col);
            if ($cond === null) {
                $parts[] = '`' . $col . '` IS NULL';
            } elseif (is_array($cond)) {
                [$op, $val] = $cond;
                $op = strtoupper((string) $op);
                if (!in_array($op, self::OPS, true)) {
                    throw new \InvalidArgumentException('Opérateur interdit : ' . $op);
                }
                if ($op === 'IN' || $op === 'NOT IN') {
                    $val = array_values((array) $val);
                    if (!$val) {
                        $parts[] = $op === 'IN' ? '1 = 0' : '1 = 1';
                        continue;
                    }
                    $parts[] = '`' . $col . '` ' . $op . ' (' . implode(', ', array_fill(0, count($val), '?')) . ')';
                    array_push($params, ...$val);
                } else {
                    $parts[] = '`' . $col . '` ' . $op . ' ?';
                    $params[] = $val;
                }
            } else {
                $parts[] = '`' . $col . '` = ?';
                $params[] = is_bool($cond) ? (int) $cond : $cond;
            }
        }
        return implode(' AND ', $parts) ?: '1 = 1';
    }

    private function order(string $orderBy): string
    {
        if (!preg_match('/^(\w+)(?:\s+(ASC|DESC))?$/i', trim($orderBy), $m)) {
            throw new \InvalidArgumentException('Tri invalide.');
        }
        $this->assertField($m[1]);
        return '`' . $m[1] . '` ' . strtoupper($m[2] ?? 'ASC');
    }
}
