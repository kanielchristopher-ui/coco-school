<?php
/**
 * ============================================================
 *  Base Model
 *  Provides a lightweight, reusable CRUD layer on top of the
 *  Database class. Each child model defines $table and may
 *  override $primaryKey.
 * ============================================================
 */

declare(strict_types=1);

abstract class Model
{
    protected string $table = '';
    protected string $primaryKey = 'id';

    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * All rows (optionally ordered / limited).
     */
    public function all(string $orderBy = '', string $direction = 'ASC'): array
    {
        $order = $orderBy !== '' ? " ORDER BY `$orderBy` $direction" : '';
        return $this->db->fetchAll("SELECT * FROM `{$this->table}`" . $order);
    }

    /**
     * Find a single row by primary key.
     */
    public function find(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?",
            [$id]
        );
    }

    /**
     * Find rows matching a WHERE clause.
     */
    public function where(string $column, mixed $value): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE `$column` = ?",
            [$value]
        );
    }

    /**
     * Find the first row matching a WHERE clause.
     */
    public function firstWhere(string $column, mixed $value): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}` WHERE `$column` = ? LIMIT 1",
            [$value]
        );
    }

    /**
     * Create a new record and return the new id.
     */
    public function create(array $data): int
    {
        return $this->db->insert($this->table, $data);
    }

    /**
     * Update a record by primary key.
     */
    public function update(int $id, array $data): bool
    {
        return $this->db->update(
            $this->table,
            $data,
            "`{$this->primaryKey}` = :pk",
            [':pk' => $id]
        ) >= 0;
    }

    /**
     * Delete a record by primary key.
     */
    public function delete(int $id): bool
    {
        return $this->db->delete($this->table, "`{$this->primaryKey}` = ?", [$id]) > 0;
    }

    /**
     * Count all rows.
     */
    public function count(): int
    {
        return $this->db->count("SELECT COUNT(*) FROM `{$this->table}`");
    }
}
