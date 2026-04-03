<?php

namespace App\Models;

use Etus\Framework\Database\Model;
use Etus\Framework\Database\Connection;

class Task extends Model
{
    public const STATUSES   = ['open', 'in_progress', 'review', 'closed'];
    public const PRIORITIES = ['low', 'medium', 'high', 'critical'];

    protected string $table      = 'tasks';
    protected string $primaryKey = 'id';
    protected bool   $timestamps  = true;
    protected bool   $auditFields = true;
    protected bool   $logging     = true;

    // ── Single lookup ─────────────────────────────────────────────────────────

    public function findActive(int $id): ?array
    {
        return $this->db()->selectOne(
            'SELECT tk.*,
                    u.full_name AS assignee_name,
                    u.email     AS assignee_email,
                    t.name      AS team_name,
                    m.title     AS mission_title
             FROM   tasks tk
             LEFT JOIN users    u ON u.user_id = tk.assigned_to
             LEFT JOIN teams    t ON t.id       = tk.team_id
             LEFT JOIN missions m ON m.id       = tk.mission_id
             WHERE  tk.id = ? AND tk.deleted_at IS NULL',
            [$id],
        );
    }

    // ── Collection lookups ────────────────────────────────────────────────────

    public function forUser(int $userId): array
    {
        return $this->db()->select(
            "SELECT tk.*, t.name AS team_name, m.title AS mission_title
             FROM   tasks tk
             LEFT JOIN teams    t ON t.id = tk.team_id
             LEFT JOIN missions m ON m.id = tk.mission_id
             WHERE  tk.assigned_to = ? AND tk.deleted_at IS NULL AND tk.status != 'closed'
             ORDER BY CASE tk.priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END,
                      tk.due_date ASC",
            [$userId],
        );
    }

    public function forMission(int $missionId): array
    {
        return $this->db()->select(
            'SELECT tk.*, u.full_name AS assignee_name, t.name AS team_name
             FROM   tasks tk
             LEFT JOIN users u ON u.user_id = tk.assigned_to
             LEFT JOIN teams t ON t.id      = tk.team_id
             WHERE  tk.mission_id = ? AND tk.deleted_at IS NULL
             ORDER BY tk.status, tk.priority DESC',
            [$missionId],
        );
    }

    // ── Flexible filter ───────────────────────────────────────────────────────

    /**
     * Filter tasks with any combination of criteria.
     *
     * Accepted keys: search, status (string|array), priority (string|array),
     *   team_id, assigned_to, mission_id, due_from (Y-m-d), due_to (Y-m-d),
     *   scope_user (restrict to tasks visible to that user via team membership)
     *
     * @param  array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function filter(array $filters = []): array
    {
        $where  = ['tk.deleted_at IS NULL'];
        $params = [];

        if (! empty($filters['search'])) {
            $where[]  = '(tk.title LIKE ? OR tk.description LIKE ?)';
            $term     = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
        }

        if (! empty($filters['status'])) {
            $statuses = array_values(array_filter(
                (array) $filters['status'],
                fn($s) => in_array($s, self::STATUSES, true)
            ));
            if ($statuses) {
                $where[]  = 'tk.status IN (' . implode(',', array_fill(0, count($statuses), '?')) . ')';
                $params   = array_merge($params, $statuses);
            }
        }

        if (! empty($filters['priority'])) {
            $priorities = array_values(array_filter(
                (array) $filters['priority'],
                fn($p) => in_array($p, self::PRIORITIES, true)
            ));
            if ($priorities) {
                $where[]  = 'tk.priority IN (' . implode(',', array_fill(0, count($priorities), '?')) . ')';
                $params   = array_merge($params, $priorities);
            }
        }

        foreach (['team_id', 'assigned_to', 'mission_id'] as $col) {
            if (! empty($filters[$col])) {
                $where[]  = "tk.{$col} = ?";
                $params[] = (int) $filters[$col];
            }
        }

        if (! empty($filters['due_from'])) {
            $where[] = 'tk.due_date >= ?';
            $params[] = $filters['due_from'];
        }
        if (! empty($filters['due_to'])) {
            $where[] = 'tk.due_date <= ?';
            $params[] = $filters['due_to'];
        }

        if (! empty($filters['scope_user'])) {
            $uid      = (int) $filters['scope_user'];
            $where[]  = '(tk.assigned_to = ? OR tk.team_id IN (SELECT team_id FROM team_membership WHERE user_id = ?))';
            $params[] = $uid;
            $params[] = $uid;
        }

        $w = implode(' AND ', $where);

        return $this->db()->select(
            "SELECT tk.*,
                    u.full_name AS assignee_name,
                    t.name      AS team_name,
                    m.title     AS mission_title
             FROM   tasks tk
             LEFT JOIN users    u ON u.user_id = tk.assigned_to
             LEFT JOIN teams    t ON t.id       = tk.team_id
             LEFT JOIN missions m ON m.id       = tk.mission_id
             WHERE  {$w}
             ORDER BY
                CASE tk.priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END,
                tk.due_date ASC",
            $params,
        );
    }

    // ── Status transition ─────────────────────────────────────────────────────

    public function transition(int $taskId, string $status): void
    {
        if (! in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException("Invalid task status: [{$status}].");
        }

        $this->db()->execute(
            'UPDATE tasks SET status = ?, completed_at = ?, updated_at = ? WHERE id = ?',
            [$status, $status === 'closed' ? date('Y-m-d H:i:s') : null, date('Y-m-d H:i:s'), $taskId],
        );
    }

    public function reassign(int $taskId, int $userId): void
    {
        $this->db()->execute(
            'UPDATE tasks SET assigned_to = ?, updated_at = ? WHERE id = ?',
            [$userId, date('Y-m-d H:i:s'), $taskId]
        );
    }

    // ── Bulk operations ───────────────────────────────────────────────────────

    /**
     * @param array<int, int> $ids
     * @return int rows affected
     */
    public function bulkUpdateStatus(array $ids, string $status): int
    {
        if (empty($ids)) return 0;
        if (! in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException("Invalid task status: [{$status}].");
        }

        $ph  = implode(',', array_fill(0, count($ids), '?'));
        $now = date('Y-m-d H:i:s');
        $ca  = $status === 'closed' ? $now : null;

        return $this->db()->execute(
            "UPDATE tasks SET status = ?, completed_at = ?, updated_at = ?
             WHERE  id IN ({$ph}) AND deleted_at IS NULL",
            [$status, $ca, $now, ...$ids],
        );
    }

    /**
     * @param array<int, int> $ids
     */
    public function bulkDelete(array $ids, int $deletedBy): int
    {
        if (empty($ids)) return 0;
        $ph  = implode(',', array_fill(0, count($ids), '?'));
        $now = date('Y-m-d H:i:s');

        return $this->db()->execute(
            "UPDATE tasks SET deleted_at = ?, deleted_by = ? WHERE id IN ({$ph}) AND deleted_at IS NULL",
            [$now, $deletedBy, ...$ids],
        );
    }

    // ── Summary stats ─────────────────────────────────────────────────────────

    /**
     * Status-bucketed counts + overdue.
     *
     * @return array<string, int>  keys: open, in_progress, review, closed, overdue, total
     */
    public function stats($scopeUserId = null): array
    {
        $where  = ['deleted_at IS NULL'];
        $params = [];

        if ($scopeUserId !== null) {
            $where[]  = '(assigned_to = ? OR team_id IN (SELECT team_id FROM team_membership WHERE user_id = ?))';
            $params[] = $scopeUserId;
            $params[] = $scopeUserId;
        }

        $w    = implode(' AND ', $where);
        $rows = $this->db()->select("SELECT status, COUNT(*) AS total FROM tasks WHERE {$w} GROUP BY status", $params);

        $stats = array_fill_keys(self::STATUSES, 0);
        foreach ($rows as $r) {
            if (isset($stats[$r['status']])) $stats[$r['status']] = (int) $r['total'];
        }

        $oRow            = $this->db()->selectOne(
            "SELECT COUNT(*) AS n FROM tasks
             WHERE  {$w} AND status != 'closed' AND due_date < ? AND due_date IS NOT NULL",
            [...$params, date('Y-m-d')],
        );
        $stats['overdue'] = (int) ($oRow['n'] ?? 0);
        $stats['total']   = array_sum(array_intersect_key($stats, array_flip(self::STATUSES)));

        return $stats;
    }

    // ── Comments ──────────────────────────────────────────────────────────────

    public function comments(int $taskId): array
    {
        return $this->db()->select(
            'SELECT tc.*, u.full_name AS author_name
             FROM   task_comments tc
             JOIN   users u ON u.user_id = tc.user_id
             WHERE  tc.task_id = ? ORDER BY tc.created_at ASC',
            [$taskId],
        );
    }

    public function addComment(int $taskId, string $userId, string $body): int
    {
        return $this->db()->insert(
            'INSERT INTO task_comments (task_id, `user_id`, body, created_at) VALUES (?, ?, ?, ?)',
            [$taskId, $userId, $body, date('Y-m-d H:i:s')],
        );
    }

    // ── Soft delete ───────────────────────────────────────────────────────────

    public function softDelete(int $taskId, string $deletedBy): void
    {
        $this->db()->execute(
            'UPDATE tasks SET deleted_at = ?, deleted_by = ? WHERE id = ?',
            [date('Y-m-d H:i:s'), $deletedBy, $taskId]
        );
    }

    private function db(): Connection
    {
        return Connection::getInstance();
    }
}
