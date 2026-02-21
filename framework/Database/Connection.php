<?php

namespace Etus\Framework\Database;

use PDO;
use PDOStatement;

class Connection
{
    private static ?self $instance = null;
    private readonly PDO $pdo;

    private function __construct(
        string $connectionString,
        string $username = '',
        string $password = '',
    ) {
        $this->pdo = new PDO($connectionString, $username ?: null, $password ?: null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public static function create(
        string $connectionString,
        string $username = '',
        string $password = '',
    ): static {
        if (null === static::$instance) {
            static::$instance = new static($connectionString, $username, $password);
        }

        return static::$instance;
    }

    public static function getInstance(): static
    {
        if (null === static::$instance) {
            throw new \RuntimeException('Connection has not been initialised. Call Connection::create() first.');
        }

        return static::$instance;
    }

    /**
     * Execute a SELECT query and return all matching rows.
     *
     * @param array<string, mixed> $bindings
     * @return array<int, array<string, mixed>>
     */
    public function select(string $sql, array $bindings = []): array
    {
        $statement = $this->prepare($sql, $bindings);

        return $statement->fetchAll();
    }

    /**
     * Execute a SELECT query and return the first matching row, or null.
     *
     * @param array<string, mixed> $bindings
     * @return array<string, mixed>|null
     */
    public function selectOne(string $sql, array $bindings = []): ?array
    {
        $statement = $this->prepare($sql, $bindings);

        $result = $statement->fetch();

        return $result !== false ? $result : null;
    }

    /**
     * Execute an INSERT, UPDATE, or DELETE statement and return the number of affected rows.
     *
     * @param array<string, mixed> $bindings
     */
    public function execute(string $sql, array $bindings = []): int
    {
        $statement = $this->prepare($sql, $bindings);

        return $statement->rowCount();
    }

    /**
     * Execute an INSERT statement and return the last inserted ID.
     *
     * @param array<string, mixed> $bindings
     */
    public function insert(string $sql, array $bindings = []): string
    {
        $this->prepare($sql, $bindings);
        
        return $this->pdo->lastInsertId();
    }

    private function prepare(string $sql, array $bindings): PDOStatement
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($bindings);

        return $statement;
    }
}
