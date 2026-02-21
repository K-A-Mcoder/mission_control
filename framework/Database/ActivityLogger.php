<?php

namespace Etus\Framework\Database;

class ActivityLogger
{
    public function __construct(private readonly Connection $connection) {}

    /**
     * Write an activity log entry.
     *
     * @param array<string, mixed> $payload  The data involved in the action.
     */
    public function log(
        string $action,
        string $model,
        int|string|null $recordId,
        array $payload = [],
    ): void {
        $this->connection->insert(
            'INSERT INTO activity_logs
                (action, model, record_id, payload, user_id, created_at)
             VALUES
                (:action, :model, :record_id, :payload, :user_id, :created_at)',
            [
                ':action'    => $action,
                ':model'     => $model,
                ':record_id' => $recordId,
                ':payload'   => json_encode($payload),
                ':user_id'   => $this->resolveUserId(),
                ':created_at' => date('Y-m-d H:i:s'),
            ],
        );
    }

    private function resolveUserId(): int|string|null
    {
        // Hook this up to your auth system when you have one.
        // e.g. return Auth::id();
        return $_SESSION['user_id'] ?? null;
    }
}
