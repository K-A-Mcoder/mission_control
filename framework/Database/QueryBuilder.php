<?php

namespace Etus\Framework\Database;

class QueryBuilder
{
    private array $wheres  = [];
    private ?int  $limit   = null;
    private ?int  $offset  = null;
    private string $orderBy = '';
    private array $bindings = [];

    public function __construct(
        private readonly Connection $connection,
        private readonly string $table,
    ) {}

    public function where(string $column, string $operatorOrValue, mixed $value = null): static
    {
        // Allow shorthand: where('name', 'John') defaults to =
        if ($value === null) {
            $value    = $operatorOrValue;
            $operator = '=';
        } else {
            $operator = strtoupper($operatorOrValue);
        }

        $this->wheres[]   = "{$column} {$operator} ?";
        $this->bindings[] = $value;

        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->orderBy = "ORDER BY {$column} " . strtoupper($direction);

        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Execute the query and return all matching rows.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(): array
    {
        return $this->connection->select($this->buildSql(), $this->bindings);
    }

    /**
     * Execute the query and return the first matching row, or null.
     *
     * @return array<string, mixed>|null
     */
    public function first(): ?array
    {
        $this->limit(1);

        return $this->connection->selectOne($this->buildSql(), $this->bindings);
    }

    /**
     * Return the count of matching rows.
     */
    public function count(): int
    {
        $sql    = "SELECT COUNT(*) as aggregate FROM {$this->table}" . $this->buildWhere();
        $result = $this->connection->selectOne($sql, $this->bindings);

        return (int) ($result['aggregate'] ?? 0);
    }

    private function buildSql(): string
    {
        $sql = "SELECT * FROM {$this->table}";
        $sql .= $this->buildWhere();

        if ($this->orderBy) {
            $sql .= " {$this->orderBy}";
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    private function buildWhere(): string
    {
        if (empty($this->wheres)) {
            return '';
        }

        return ' WHERE ' . implode(' AND ', $this->wheres);
    }
}
