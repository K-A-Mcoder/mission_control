<?php

namespace App\Models;

use Etus\Framework\Database\Model;
use Etus\Framework\Database\Connection;

class Role extends Model
{
    public const SYSTEM_ROLES = ['super_admin', 'admin', 'manager', 'user'];

    protected string $table      = 'roles';
    protected string $primaryKey = 'id';
    protected bool   $timestamps  = true;
    protected bool   $auditFields = false;
    protected bool   $logging     = true;

    // ── Lookups ───────────────────────────────────────────────────────────────

    public function findByName(string $name): ?array
    {
        return $this->where('role_name', $name)->first();
    }

    /** All roles with user count and permission count */
    public function allWithCounts(): array
    {
        return $this->db()->select(
            'SELECT r.*,
                    COUNT(DISTINCT u.user_id) AS user_count,
                    COUNT(DISTINCT rp.permission_id) AS permission_count
             FROM   roles r
             LEFT JOIN users u  ON u.role_id = r.id
             LEFT JOIN role_permissions rp ON rp.role_id = r.id
             GROUP  BY r.id
             ORDER  BY r.id ASC',
        );
    }

    /** Users that belong to a role */
    public function users(int $roleId, int $limit = 50): array
    {
        return $this->db()->select(
            'SELECT u.user_id, u.full_name, u.email, u.status, u.created_at
             FROM   users u WHERE u.role_id=? ORDER BY u.full_name ASC LIMIT ?',
            [$roleId, $limit],
        );
    }

    public function get()
    {
        $this->db()->select('SELECT id, name FROM roles ORDER BY name ASC');
        
    }

    /**
     * Return all permissions belonging to a role.
     *
     * @return array<int, array<string, mixed>>
     */
    public function permissions(int $roleId): array
    {
        return $this->db()->select(
            'SELECT p.*
             FROM   permissions p
             JOIN   role_permissions rp ON rp.permission_id = p.id
             WHERE  rp.role_id = ?
             ORDER  BY p.name ASC',
            [$roleId],
        );
    }

    /**
     * Return permission names only (useful for Gate cache loading).
     *
     * @return array<int, string>
     */
    public function permissionNames(int $roleId): array
    {
        return array_column($this->permissions($roleId), 'name');
    }

    public function permissionIds(int $roleId): array
    {
        return array_map('intval', array_column($this->permissions($roleId), 'id'));
    }

    // ── Sync ──────────────────────────────────────────────────────────────────

    /**
     * Replace all permissions for a role with the given permission IDs.
     *
     * @param array<int, int> $permissionIds
     */
    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $db = $this->db();

        $db->execute('DELETE FROM role_permissions WHERE role_id = ?', [$roleId]);

        foreach ($permissionIds as $permissionId) {
            $db->execute(
                'INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)',
                [$roleId, $permissionId],
            );
        }
    }

    /** Whether a role is a system-protected role (cannot be deleted) */
    public function isSystemRole(string $roleName): bool
    {
        return in_array(strtolower($roleName), self::SYSTEM_ROLES, true);
    }

    // ── Shortcut ──────────────────────────────────────────────────────────────

    private function db(): Connection
    {
        return Connection::getInstance();
    }

    // protected string $table      = 'roles';
    // protected string $primaryKey = 'id';
    // protected bool   $timestamps  = true;
    // protected bool   $auditFields = false;
    // protected bool   $logging     = true;

    // // ── Lookups ───────────────────────────────────────────────────────────────

    // public function findByName(string $name): ?array
    // {
    //     return $this->where('role_name', $name)->first();
    // }





    /** All permissions belonging to a role */
    // public function permissions(int $roleId): array
    // {
    //     return $this->db()->select(
    //         'SELECT p.* FROM permissions p
    //          JOIN   role_permissions rp ON rp.permission_id = p.id
    //          WHERE  rp.role_id=? ORDER BY p.group ASC, p.name ASC',
    //         [$roleId],
    //     );
    // }

    // public function permissionNames(int $roleId): array
    // {
    //     return array_column($this->permissions($roleId), 'name');
    // }



    /** Replace all permissions for a role atomically */
    // public function syncPermissions(int $roleId, array $permissionIds): void
    // {
    //     $db = $this->db();
    //     $db->execute('DELETE FROM role_permissions WHERE role_id=?', [$roleId]);

    //     foreach (array_unique(array_map('intval', $permissionIds)) as $pid) {
    //         $db->execute(
    //             'INSERT OR IGNORE INTO role_permissions (role_id, permission_id) VALUES (?,?)',
    //             [$roleId, $pid],
    //         );
    //     }
    // }



    // private function db(): Connection { return Connection::getInstance(); }
}
