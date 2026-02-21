<?php

namespace App\Models;

use Etus\Framework\Database\Model;
use Etus\Framework\Database\Connection;

class Mission extends Model
{
    public const CLASSIFICATIONS = ['PUBLIC', 'CONFIDENTIAL', 'SECRET'];

    protected string $table      = 'missions';
    protected string $primaryKey = 'id';
    protected bool   $timestamps  = true;
    protected bool   $auditFields = true;
    protected bool   $logging     = true;

    // ── Reads ─────────────────────────────────────────────────────────────────

    /**
     * All missions with task counts — admin sees everything.
     *
     * @return array<int, array<string, mixed>>
     */
    public function allWithTaskCounts(): array
    {
        return $this->db()->select(
            'SELECT m.*,
                    COUNT(t.id) AS total_tasks,
                    SUM(CASE WHEN t.status = \'closed\' THEN 1 ELSE 0 END) AS completed_tasks
             FROM   missions m
             LEFT   JOIN tasks t ON m.id = t.mission_id
             WHERE  m.deleted_at IS NULL
             GROUP  BY m.id
             ORDER  BY m.created_at DESC',
        );
    }

    /**
     * Non-SECRET missions — for managers.
     *
     * @return array<int, array<string, mixed>>
     */
    public function nonSecretWithTaskCounts(): array
    {
        return $this->db()->select(
            'SELECT m.*,
                    COUNT(t.id) AS total_tasks,
                    SUM(CASE WHEN t.status = \'closed\' THEN 1 ELSE 0 END) AS completed_tasks
             FROM   missions m
             LEFT   JOIN tasks t ON m.id = t.mission_id
             WHERE  m.classification != \'SECRET\'
             AND    m.deleted_at IS NULL
             GROUP  BY m.id
             ORDER  BY m.created_at DESC',
        );
    }

    /**
     * Missions visible to a specific user via their team memberships.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forUser($userId): array
    {
        return $this->db()->select(
            'SELECT m.*,
                    COUNT(t.id) AS total_tasks,
                    SUM(CASE WHEN t.status = \'closed\' THEN 1 ELSE 0 END) AS completed_tasks
             FROM   missions m
             LEFT   JOIN tasks t ON m.id = t.mission_id
             WHERE  m.deleted_at IS NULL
             AND    m.id IN (
                 SELECT mission_id FROM teams
                 WHERE  id IN (
                     SELECT team_id FROM team_membership WHERE user_id = ?
                 )
             )
             GROUP  BY m.id
             ORDER  BY m.created_at DESC',
            [$userId],
        );
    }

    /**
     * Find a single non-deleted mission by ID.
     *
     * @return array<string, mixed>|null
     */
    public function findActive(int $id): ?array
    {
        return $this->db()->selectOne(
            'SELECT * FROM missions WHERE id = ? AND deleted_at IS NULL',
            [$id],
        );
    }

    // ── Writes ────────────────────────────────────────────────────────────────

    /**
     * Soft delete a single mission.
     */
    public function softDelete(int $id, int $deletedBy): int
    {
        return $this->db()->execute(
            'UPDATE missions SET deleted_at = ?, deleted_by = ? WHERE id = ? AND deleted_at IS NULL',
            [date('Y-m-d H:i:s'), $deletedBy, $id],
        );
    }

    /**
     * Soft delete multiple missions in a single query.
     *
     * @param  array<int, int> $ids
     */
    public function softDeleteMany(array $ids, int $deletedBy): int
    {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        return $this->db()->execute(
            "UPDATE missions SET deleted_at = ?, deleted_by = ? WHERE id IN ({$placeholders}) AND deleted_at IS NULL",
            [date('Y-m-d H:i:s'), $deletedBy, ...$ids],
        );
    }

    // ── Team assignment ───────────────────────────────────────────────────────

    /**
     * All teams assigned to a mission.
     *
     * @return array<int, array<string, mixed>>
     */
    public function teamsForMission(int $missionId): array
    {
        return $this->db()->select(
            'SELECT t.*
             FROM   teams t
             JOIN   mission_team_assignments mta ON mta.team_id = t.id
             WHERE  mta.mission_id = ?
             ORDER  BY t.name ASC',
            [$missionId],
        );
    }

    /**
     * All teams — for the assign-team dropdown on the show page.
     *
     * @return array<int, array<string, mixed>>
     */
    public function allTeams(): array
    {
        return $this->db()->select('SELECT id, name FROM teams ORDER BY name ASC');
    }

    /**
     * Assign a team to a mission (upsert — silently ignores duplicate).
     */
    public function assignTeam(int $missionId, int $teamId, int $assignedBy): void
    {
        $this->db()->execute(
            'INSERT INTO mission_team_assignments (mission_id, team_id, assigned_by)
             VALUES (?, ?, ?)
             ON CONFLICT (mission_id, team_id) DO NOTHING',
            [$missionId, $teamId, $assignedBy],
        );
    }

    /**
     * Return all user IDs who are members of a given team.
     *
     * @return array<int, int>
     */
    public function teamMemberIds(int $teamId): array
    {
        $rows = $this->db()->select(
            'SELECT user_id FROM team_membership WHERE team_id = ?',
            [$teamId],
        );

        return array_map('intval', array_column($rows, 'user_id'));
    }

    // ── Shortcut ──────────────────────────────────────────────────────────────

    private function db(): Connection
    {
        return Connection::getInstance();
    }
}
