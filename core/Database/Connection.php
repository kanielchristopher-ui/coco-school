<?php
declare(strict_types=1);

namespace CocoCore\Database;

use CocoCore\Contracts\TransactionInterface;
use PDO;

/** Connexion PDO (requêtes paramétrées uniquement) + transactions imbriquables. */
final class Connection implements TransactionInterface
{
    private int $depth = 0;

    public function __construct(private PDO $pdo)
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /** @param array{host:string,name:string,user:string,pass:string,port?:int,charset?:string} $c */
    public static function fromConfig(array $c): self
    {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $c['host'], $c['port'] ?? 3306, $c['name'], $c['charset'] ?? 'utf8mb4');
        return new self(new PDO($dsn, $c['user'], $c['pass'], [PDO::ATTR_EMULATE_PREPARES => false]));
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    /** @param array<int|string,mixed> $params */
    public function run(string $sql, array $params = []): \PDOStatement
    {
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st;
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $row = $this->run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->run($sql, $params)->fetchAll();
    }

    public function fetchValue(string $sql, array $params = []): mixed
    {
        $v = $this->run($sql, $params)->fetchColumn();
        return $v === false ? null : $v;
    }

    public function insert(string $table, array $data): int
    {
        if (!preg_match('/^[a-z_][a-z0-9_]*$/i', $table)) {
            throw new \InvalidArgumentException('Nom de table invalide.');
        }
        foreach (array_keys($data) as $col) {
            if (!preg_match('/^[a-z_][a-z0-9_]*$/i', (string) $col)) {
                throw new \InvalidArgumentException('Nom de colonne invalide.');
            }
        }
        $cols = implode(', ', array_map(fn($c) => "`$c`", array_keys($data)));
        $this->run("INSERT INTO `$table` ($cols) VALUES (" . implode(', ', array_fill(0, count($data), '?')) . ')', array_values($data));
        return (int) $this->pdo->lastInsertId();
    }

    public function transaction(callable $work): mixed
    {
        $outer = $this->depth === 0;
        $this->depth++;
        try {
            if ($outer) {
                $this->pdo->beginTransaction();
            }
            $result = $work();
            if ($outer) {
                $this->pdo->commit();
            }
            return $result;
        } catch (\Throwable $e) {
            if ($outer && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        } finally {
            $this->depth--;
        }
    }
}
