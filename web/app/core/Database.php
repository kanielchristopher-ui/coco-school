<?php
/**
 * ============================================================
 *  Database
 *  PDO singleton wrapper using prepared statements only.
 *  Provides query helpers for the entire application.
 * ============================================================
 */

declare(strict_types=1);

class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    /**
     * Private constructor - creates the PDO connection.
     */
    private function __construct()
    {
        global $dbConfig;

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['dbname'],
            $dbConfig['charset']
        );

        try {
            $this->pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the singleton database instance.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Execute a prepared statement and return the PDO statement.
     */
    public function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch a single row.
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $row = $this->run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Fetch all rows.
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->run($sql, $params)->fetchAll();
    }

    /**
     * Fetch a single column value (first column of first row).
     */
    public function fetchColumn(string $sql, array $params = []): mixed
    {
        return $this->run($sql, $params)->fetchColumn();
    }

    /**
     * Fetch the first column of all rows (e.g. list of ids).
     */
    public function fetchCol(string $sql, array $params = []): array
    {
        return $this->run($sql, $params)->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Insert a row into a table and return the new id.
     */
    public function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($c) => ':' . $c, $columns);

        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $table,
            implode('`, `', $columns),
            implode(', ', $placeholders)
        );

        $params = [];
        foreach ($data as $key => $value) {
            $params[':' . $key] = $value;
        }

        $this->run($sql, $params);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Update rows and return the affected row count.
     *
     * WHERE params may be provided positionally (?) or as named (:key)
     * placeholders. PDO forbids mixing positional and named placeholders in a
     * single statement, so positional values are rewritten to named
     * placeholders (:where_0, :where_1, ...) before binding.
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $sets = [];
        $params = [];
        foreach ($data as $key => $value) {
            $sets[] = '`' . $key . '` = :set_' . $key;
            $params[':set_' . $key] = $value;
        }

        if ($whereParams !== []) {
            if (array_is_list($whereParams)) {
                $index = 0;
                $where = preg_replace_callback('/\?/', static function () use (&$index): string {
                    return ':where_' . $index++;
                }, $where);
                foreach (array_values($whereParams) as $i => $value) {
                    $params[':where_' . $i] = $value;
                }
            } else {
                foreach ($whereParams as $key => $value) {
                    $params[$key] = $value;
                }
            }
        }

        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE %s',
            $table,
            implode(', ', $sets),
            $where
        );

        return $this->run($sql, $params)->rowCount();
    }

    /**
     * Delete rows and return the affected row count.
     */
    public function delete(string $table, string $where, array $whereParams = []): int
    {
        $sql = sprintf('DELETE FROM `%s` WHERE %s', $table, $where);
        return $this->run($sql, $whereParams)->rowCount();
    }

    /**
     * Count rows for a query.
     */
    public function count(string $sql, array $params = []): int
    {
        return (int) $this->fetchColumn($sql, $params);
    }

    /**
     * Begin a database transaction.
     */
    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Commit the active transaction.
     */
    public function commit(): void
    {
        $this->pdo->commit();
    }

    /**
     * Roll back the active transaction.
     */
    public function rollBack(): void
    {
        $this->pdo->rollBack();
    }

    /**
     * Quote an identifier safely (backticks). Prevents injection in dynamic table/column names.
     */
    public function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
