<?php

namespace Etus\Framework\Database;

abstract class Model
{
    /*
    |--------------------------------------------------------------------------
    | Configuration — override these in your concrete model
    |--------------------------------------------------------------------------
    */

    /** The database table this model reads and writes to. */
    protected string $table = '';

    /** The primary key column name. */
    protected string $primaryKey = 'id';

    /**
     * Automatically manage created_at and updated_at timestamps.
     * Set to false to disable.
     */
    protected bool $timestamps = true;

    /**
     * Automatically manage created_by and updated_by audit columns.
     * Requires an auth system to be wired into ActivityLogger::resolveUserId().
     * Set to false to disable.
     */
    protected bool $auditFields = true;

    /**
     * Log all write operations (create, update, delete) to the activity_logs table.
     * Set to false to disable.
     */
    protected bool $logging = true;

    /*
    |--------------------------------------------------------------------------
    | Internals
    |--------------------------------------------------------------------------
    */

    private Connection $connection;
    private ActivityLogger $logger;

    public function __construct()
    {
        $this->connection = Connection::getInstance();
        $this->logger     = new ActivityLogger($this->connection);

        if (empty($this->table)) {
            throw new \LogicException(
                'Model [' . static::class . '] must define a $table property.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Query Builder
    |--------------------------------------------------------------------------
    */

    public function where(string $column, string $operatorOrValue, mixed $value = null): QueryBuilder
    {
        return (new QueryBuilder($this->connection, $this->table))
            ->where($column, $operatorOrValue, $value);
    }

    /*
    |--------------------------------------------------------------------------
    | Read
    |--------------------------------------------------------------------------
    */

    /**
     * Return all rows from the table.
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->connection->select("SELECT * FROM {$this->table}");
    }

    /**
     * Find a single row by primary key, or return null.
     *
     * @return array<string, mixed>|null
     */
    public function find(int|string $id): ?array
    {
        return $this->connection->selectOne(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1",
            [$id],
        );
    }

    /**
     * Find a single row by primary key, or throw if not found.
     *
     * @return array<string, mixed>
     * @throws \RuntimeException
     */
    public function findOrFail(int|string $id): array
    {
        $record = $this->find($id);

        if ($record === null) {
            throw new \RuntimeException(
                static::class . " with {$this->primaryKey} [{$id}] not found."
            );
        }

        return $record;
    }

    /*
    |--------------------------------------------------------------------------
    | Write
    |--------------------------------------------------------------------------
    */

    /**
     * Insert a new row and return its ID.
     *
     * @param  array<string, mixed> $data
     */
    public function create(array $data): string
    {
        $data = $this->withTimestamps($data, isNew: true);
        $data = $this->withAuditFields($data, isNew: true);

        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $id = $this->connection->insert(
            "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})",
            array_values($data),
        );

        $this->logActivity('create', $id, $data);

        return $id;
    }

    /**
     * Update an existing row by primary key and return the number of affected rows.
     *
     * @param  array<string, mixed> $data
     */
    public function update(int|string $id, array $data): int
    {
        $data = $this->withTimestamps($data, isNew: false);
        $data = $this->withAuditFields($data, isNew: false);

        $set = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));

        $affected = $this->connection->execute(
            "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = ?",
            [...array_values($data), $id],
        );

        $this->logActivity('update', $id, $data);

        return $affected;
    }

    /**
     * Delete a row by primary key and return the number of affected rows.
     */
    public function delete(int|string $id): int
    {
        $affected = $this->connection->execute(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id],
        );

        $this->logActivity('delete', $id, []);

        return $affected;
    }

    /*
    |--------------------------------------------------------------------------
    | Internals
    |--------------------------------------------------------------------------
    */

    private function withTimestamps(array $data, bool $isNew): array
    {
        if (! $this->timestamps) {
            return $data;
        }

        $now = date('Y-m-d H:i:s');

        if ($isNew) {
            $data['created_at'] = $now;
        }

        $data['updated_at'] = $now;

        return $data;
    }

    private function withAuditFields(array $data, bool $isNew): array
    {
        if (! $this->auditFields) {
            return $data;
        }

        $userId = $_SESSION['user_id'] ?? null;

        if ($isNew) {
            $data['created_by'] = $userId;
        }

        $data['updated_by'] = $userId;

        return $data;
    }

    private function logActivity(string $action, int|string $id, array $data): void
    {
        if (! $this->logging) {
            return;
        }

        $this->logger->log($action, static::class, $id, $data);
    }
}
