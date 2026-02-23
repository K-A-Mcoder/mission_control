<?php

namespace App\Models;

use Etus\Framework\Database\Model;
use Etus\Framework\Database\Connection;

class Permission_v1 extends Model
{
    /** Canonical permission groups and their slugs */
    public const GROUPS = [
        'users'       => 'Users',
        'roles'       => 'Roles',
        'permissions' => 'Permissions',
        'missions'    => 'Missions',
        'teams'       => 'Teams',
        'tasks'       => 'Tasks',
        'reports'     => 'Reports',
        'chat'        => 'Chat',
        'settings'    => 'Settings',
    ];

    protected string $table      = 'permissions';
    protected string $primaryKey = 'id';
    protected bool   $timestamps  = true;
    protected bool   $auditFields = false;
    protected bool   $logging     = true;

    // ── Lookups ───────────────────────────────────────────────────────────────

    public function findByName(string $name): ?array
    {
        return $this->where('name', $name)->first();
    }

    /**
     * Return all permissions grouped by their group column.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function allGrouped(): array
    {
        $rows = $this->db()->select(
            'SELECT * FROM permissions ORDER BY `group` ASC, name ASC'
        );

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['group'] ?? 'general'][] = $row;
        }

        return $grouped;
    }
    
    /** All permissions flat, with the count of roles that have each one */
    public function allWithRoleCount(): array
    {
        return $this->db()->select(
            'SELECT p.*, COUNT(rp.role_id) AS role_count
             FROM   permissions p
             LEFT JOIN role_permissions rp ON rp.permission_id = p.id
             GROUP  BY p.id
             ORDER  BY p.group ASC, p.name ASC',
        );
    }

    /**
     * Return permissions assigned to a specific role.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forRole(int $roleId): array
    {
        return $this->db()->select(
            'SELECT p.*
             FROM   permissions p
             JOIN   role_permissions rp ON rp.permission_id = p.id
             WHERE  rp.role_id = ?',
            [$roleId],
        );
    }

    /**
     * Return permission IDs for a role (useful for form checkboxes).
     *
     * @return array<int, int>
     */
    public function idsForRole(int $roleId): array
    {
        return array_map(
            'intval',
            array_column($this->forRole($roleId), 'id'),
        );
    }

    /** Check whether a permission name already exists */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $sql    = 'SELECT COUNT(*) AS n FROM permissions WHERE name=?';
        $params = [$name];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $row = $this->db()->selectOne($sql, $params);
        return (int)($row['n'] ?? 0) > 0;
    }

    // ── Shortcut ──────────────────────────────────────────────────────────────

    private function db(): Connection
    {
        return Connection::getInstance();
    }


    

    // protected string $table      = 'permissions';
    // protected string $primaryKey = 'id';
    // protected bool   $timestamps  = true;
    // protected bool   $auditFields = false;
    // protected bool   $logging     = true;

    // ── Lookups ───────────────────────────────────────────────────────────────

    // public function findByName(string $name): ?array
    // {
    //     return $this->where('name', $name)->first();
    // }

    /** All permissions grouped by their `group` column */
    // public function allGrouped(): array
    // {
    //     $rows    = $this->db()->select('SELECT * FROM permissions ORDER BY `group` ASC, name ASC');
    //     $grouped = [];
    //     foreach ($rows as $row) {
    //         $grouped[$row['group'] ?? 'general'][] = $row;
    //     }
    //     return $grouped;
    // }

    
    

    /** Permissions assigned to a specific role */
    // public function forRole(int $roleId): array
    // {
    //     return $this->db()->select(
    //         'SELECT p.* FROM permissions p
    //          JOIN role_permissions rp ON rp.permission_id = p.id
    //          WHERE rp.role_id=?',
    //         [$roleId],
    //     );
    // }

    // public function idsForRole(int $roleId): array
    // {
    //     return array_map('intval', array_column($this->forRole($roleId), 'id'));
    // }

    

    // private function db(): Connection { return Connection::getInstance(); }
}
