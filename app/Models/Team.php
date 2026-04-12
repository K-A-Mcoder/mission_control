<?php

namespace App\Models;

use Etus\Framework\Database\Model;
use Etus\Framework\Database\Connection;

class Team extends Model
{
    public const STATUSES = ['active', 'inactive', 'archived'];
    public const M_STATUSES = ['active', 'deleted', 'suspended'];

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

    // /**
    //  * All active teams with lead name and member count.
    //  *
    //  * @return array<int, array<string, mixed>>
    //  */
    // public function allActive(): array
    // {
    //     return $this->db()->select(
    //         'SELECT t.*,
    //                 u.full_name AS lead_name,
    //                 COUNT(DISTINCT tm.id) AS member_count
    //          FROM   teams t
    //          LEFT   JOIN users u            ON u.user_id = t.lead_id
    //          LEFT   JOIN team_membership tm  ON tm.team_id = t.id
    //          WHERE  t.deleted_at IS NULL
    //          GROUP  BY t.id,u.full_name, tm2.role
    //          ORDER  BY t.name ASC',
    //     );
    // }

    // /**
    //  * Teams the current user belongs to.
    //  *
    //  * @return array<int, array<string, mixed>>
    //  */
    // public function forUser(string $userId): array
    // {
    //     return $this->db()->select(
    //         'SELECT t.*,
    //             u.full_name               AS lead_name,
    //             tm2.role                  AS my_role,
    //             COUNT(DISTINCT tm_all.id) AS member_count
    //      FROM   teams t
    //      JOIN   team_membership tm2     ON tm2.team_id = t.id AND tm2.user_id = ?
    //      JOIN   team_membership tm_all  ON tm_all.team_id = t.id
    //      LEFT   JOIN users u            ON u.user_id = t.lead_id
    //      WHERE  t.deleted_at IS NULL
    //      GROUP  BY t.id, u.full_name, tm2.role
    //      ORDER  BY t.name ASCC',
    //         [$userId],
    //     );
    // }

    public function allActive(): array
    {
        return $this->db()->select(
            'SELECT t.*,
                u.full_name           AS lead_name,
                COUNT(DISTINCT tm.id) AS member_count
         FROM   teams t
         LEFT   JOIN users u 
                ON u.user_id = t.lead_id
         LEFT   JOIN team_membership tm 
                ON tm.team_id = t.id 
                AND tm.status != ?
         WHERE  t.deleted_at IS NULL
         GROUP  BY t.id, u.full_name
         ORDER  BY t.name ASC',
            ["deleted"]
        );
    }

    public function forUser_v1(string $userId): array
    {
        return $this->db()->select(
            'SELECT t.*,
                u.full_name               AS lead_name,
                tm2.role                  AS my_role,
                COUNT(DISTINCT tm_all.id) AS member_count
         FROM   teams t
         JOIN   team_membership tm2      ON tm2.team_id = t.id AND tm2.user_id = ? AND tm.status != ?
         JOIN   team_membership tm_all   ON tm_all.team_id = t.id
         LEFT   JOIN users u             ON u.user_id = t.lead_id
         WHERE  t.deleted_at IS NULL
         GROUP  BY t.id, u.full_name, tm2.role
         ORDER  BY t.name ASC',
            [$userId, "deleted"],
        );
    }
    public function forUser(string $userId): array
    {
        return $this->db()->select(
            'SELECT t.*,
                u.full_name               AS lead_name,
                tm2.role                  AS my_role,
                COUNT(DISTINCT tm_all.id) AS member_count
         FROM   teams t
         JOIN   team_membership tm2      
                ON tm2.team_id = t.id 
                AND tm2.user_id = ? 
                AND tm2.status != ?
         LEFT   JOIN team_membership tm_all   
                ON tm_all.team_id = t.id 
                AND tm_all.status != ?
         LEFT   JOIN users u             
                ON u.user_id = t.lead_id
         WHERE  t.deleted_at IS NULL
         GROUP  BY t.id, u.full_name, tm2.role
         ORDER  BY t.name ASC',
            [$userId, "deleted", "deleted"],
        );
    }

    // ── Members ───────────────────────────────────────────────────────────────

    /**
     * All members of a team with user details.
     *
     * @return array<int, array<string, mixed>>
     */
    public function members(int $teamId, $m_status = 'active'): array
    {
        return $this->db()->select(
            'SELECT u.user_id, u.full_name, u.email, r.role_name,
                    tm.role AS team_role, tm.created_at AS joined_at
             FROM   team_membership tm
             JOIN   users u  ON u.user_id = tm.user_id
             JOIN   roles r  ON r.id      = u.role_id
             WHERE  tm.team_id = ? 
             AND tm.status = ?
             ORDER  BY tm.role DESC, u.full_name ASC',
            [$teamId, $m_status],
        );
    }

    /**
     * Check whether a user is already a member of the team.
     */
    public function hasMember(int $teamId, string $userId): bool
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
    public function addMember(int $teamId, string $userId, string $addedBy, string $role = 'member'): void
    {
        $user = $this->db()->select(
            'SELECT user_id FROM users WHERE user_id = ?',
            [$userId]
        );

        if (!$user) {
            throw new \Exception("User does not exist.");
        }

        if ($this->hasMember($teamId, $userId)) {
            return;
        }

        $this->db()->execute(
            'INSERT INTO team_membership (team_id, `user_id`, `role`, added_by)
             VALUES (?, ?, ?, ?)',
            [$teamId, $userId, $role, $addedBy],
        );
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(int $teamId, $userId, $m_status = 'deleted'): void
    {
        // remove peramently done by super_admin
        // $this->db()->execute(
        //     'DELETE FROM team_membership WHERE team_id = ? AND user_id = ?',
        //     [$teamId, $userId],
        // );

        // change status to deleted don by admin and managers, team leaded
        $this->db()->execute(
            'UPDATE team_membership SET `status` = ? WHERE team_id = ? AND user_id = ?',
            [$m_status, $teamId, $userId],
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

    /**
     * Get current active mission assigned to a team.
     *
     * @return array<string, mixed>|null
     */
    public function current_mission(int $teamId): ?array
    {
        $result = $this->db()->select(
            'SELECT m.*, mta.assigned_at
         FROM   missions m
         JOIN   mission_team_assignments mta ON mta.mission_id = m.id
         WHERE  mta.team_id   = ?
         AND    m.status     != ?
         AND    m.deleted_at  IS NULL
         ORDER  BY mta.assigned_at DESC
         LIMIT 1',
            [$teamId, 'closed'],
        );

        return $result[0] ?? null;
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

    public function softDelete(int $teamId, string $deletedBy): void
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
