<?php

namespace App\Models;

use Etus\Framework\Database\Model;
use Etus\Framework\Database\Connection;

class Team extends Model
{
    public const STATUSES = ['active', 'inactive', 'archived'];

    protected string $table      = 'teams';
    protected string $primaryKey = 'id';
    protected bool   $timestamps  = true;
    protected bool   $auditFields = true;
    protected bool   $logging     = true;

    // ── Single lookups ────────────────────────────────────────────────────────

    /**
     * Find an active (non-deleted) team with its lead's name.
     *
     * @return array<string, mixed>|null
     */
    public function findActive(int $id): ?array
    {
        return $this->db()->selectOne(
            'SELECT t.*,
                    u.full_name AS lead_name,
                    u.email     AS lead_email,
                    COUNT(DISTINCT tm.id) AS member_count
             FROM   teams t
             LEFT   JOIN users u           ON u.user_id = t.lead_id
             LEFT   JOIN team_membership tm ON tm.team_id = t.id
             WHERE  t.id = ?
             AND    t.deleted_at IS NULL
             GROUP  BY t.id',
            [$id],
        );
    }

    // ── Collection lookups ────────────────────────────────────────────────────

    /**
     * All active teams with lead name and member count.
     *
     * @return array<int, array<string, mixed>>
     */
    public function allActive(): array
    {
        return $this->db()->select(
            'SELECT t.*,
                    u.full_name AS lead_name,
                    COUNT(DISTINCT tm.id) AS member_count
             FROM   teams t
             LEFT   JOIN users u            ON u.user_id = t.lead_id
             LEFT   JOIN team_membership tm  ON tm.team_id = t.id
             WHERE  t.deleted_at IS NULL
             GROUP  BY t.id
             ORDER  BY t.name ASC',
        );
    }

    /**
     * Teams the current user belongs to.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forUser($userId): array
    {
        return $this->db()->select(
            'SELECT t.*,
                    u.full_name AS lead_name,
                    tm2.role    AS my_role
             FROM   teams t
             JOIN   team_membership tm2 ON tm2.team_id = t.id AND tm2.user_id = ?
             LEFT   JOIN users u        ON u.user_id = t.lead_id
             WHERE  t.deleted_at IS NULL
             ORDER  BY t.name ASC',
            [$userId],
        );
    }

    // ── Members ───────────────────────────────────────────────────────────────

    /**
     * All members of a team with user details.
     *
     * @return array<int, array<string, mixed>>
     */
    public function members(int $teamId): array
    {
        return $this->db()->select(
            'SELECT u.user_id, u.full_name, u.email, r.role_name,
                    tm.role AS team_role, tm.created_at AS joined_at
             FROM   team_membership tm
             JOIN   users u  ON u.user_id = tm.user_id
             JOIN   roles r  ON r.id      = u.role_id
             WHERE  tm.team_id = ?
             ORDER  BY tm.role DESC, u.full_name ASC',
            [$teamId],
        );
    }

    /**
     * Check whether a user is already a member of the team.
     */
    public function hasMember(int $teamId, int $userId): bool
    {
        $row = $this->db()->selectOne(
            'SELECT id FROM team_membership WHERE team_id = ? AND user_id = ?',
            [$teamId, $userId],
        );

        return $row !== null;
    }

    /**
     * Add a member to the team. Silently ignores if already a member.
     */
    public function addMember(int $teamId, int $userId, int $addedBy, string $role = 'member'): void
    {
        if ($this->hasMember($teamId, $userId)) {
            return;
        }

        $this->db()->execute(
            'INSERT INTO team_membership (team_id, user_id, role, added_by, created_at)
             VALUES (?, ?, ?, ?, ?)',
            [$teamId, $userId, $role, $addedBy, date('Y-m-d H:i:s')],
        );
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(int $teamId, int $userId): void
    {
        $this->db()->execute(
            'DELETE FROM team_membership WHERE team_id = ? AND user_id = ?',
            [$teamId, $userId],
        );
    }

    /**
     * Return all user IDs in a team (useful for notifications).
     *
     * @return array<int, int>
     */
    public function memberIds(int $teamId): array
    {
        $rows = $this->db()->select(
            'SELECT user_id FROM team_membership WHERE team_id = ?',
            [$teamId],
        );

        return array_map('intval', array_column($rows, 'user_id'));
    }

    // ── Tasks ─────────────────────────────────────────────────────────────────

    /**
     * All active tasks for a team.
     *
     * @return array<int, array<string, mixed>>
     */
    public function tasks(int $teamId): array
    {
        return $this->db()->select(
            'SELECT tk.*,
                    u.full_name AS assignee_name
             FROM   tasks tk
             LEFT   JOIN users u ON u.user_id = tk.assigned_to
             WHERE  tk.team_id    = ?
             AND    tk.deleted_at IS NULL
             ORDER  BY
                CASE tk.priority
                    WHEN \'critical\' THEN 1
                    WHEN \'high\'     THEN 2
                    WHEN \'medium\'   THEN 3
                    ELSE 4
                END,
                tk.due_date ASC',
            [$teamId],
        );
    }

    /**
     * Task counts per status for a team (for the kanban summary).
     *
     * @return array<string, int>
     */
    public function taskStats(int $teamId): array
    {
        $rows = $this->db()->select(
            'SELECT status, COUNT(*) AS total
             FROM   tasks
             WHERE  team_id = ? AND deleted_at IS NULL
             GROUP  BY status',
            [$teamId],
        );

        $stats = ['open' => 0, 'in_progress' => 0, 'review' => 0, 'closed' => 0];

        foreach ($rows as $row) {
            $stats[$row['status']] = (int) $row['total'];
        }

        return $stats;
    }

    // ── Missions ──────────────────────────────────────────────────────────────

    /**
     * All missions assigned to a team.
     *
     * @return array<int, array<string, mixed>>
     */
    public function missions(int $teamId): array
    {
        return $this->db()->select(
            'SELECT m.*, mta.assigned_at
             FROM   missions m
             JOIN   mission_team_assignments mta ON mta.mission_id = m.id
             WHERE  mta.team_id   = ?
             AND    m.deleted_at  IS NULL
             ORDER  BY mta.assigned_at DESC',
            [$teamId],
        );
    }


    // ── Summary stats ─────────────────────────────────────────────────────────

    /**
     * Global team stats for dashboards.
     * pass $scopeUserId to show only teams that user belongs to.
     *
     * @return array{total: int, active: int, inactive: int, archived: int}
     */
    public function stats($scopeUserId = null): array
    {
        $where  = ["deleted_at IS NULL"];
        $params = [];

        if ($scopeUserId !== null) {
            $where[]  = "id IN (SELECT team_id FROM team_membership WHERE user_id = ?)";
            $params[] = $scopeUserId;
        }

        $w    = implode(" AND ", $where);
        $rows = $this->db()->select(
            "SELECT status, COUNT(*) AS total FROM teams WHERE {$w} GROUP BY status",
            $params,
        );

        $stats = ["total" => 0, "active" => 0, "inactive" => 0, "archived" => 0];
        foreach ($rows as $r) {
            if (isset($stats[$r["status"]])) {
                $stats[$r["status"]] = (int) $r["total"];
            }
        }
        $stats["total"] = $stats["active"] + $stats["inactive"] + $stats["archived"];

        return $stats;
    }

    // ── Soft delete ───────────────────────────────────────────────────────────

    public function softDelete(int $teamId, int $deletedBy): void
    {
        $this->db()->execute(
            'UPDATE teams SET deleted_at = ?, deleted_by = ? WHERE id = ?',
            [date('Y-m-d H:i:s'), $deletedBy, $teamId],
        );
    }

    // ── Shortcut ──────────────────────────────────────────────────────────────

    private function db(): Connection
    {
        return Connection::getInstance();
    }
}
