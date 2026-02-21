<?php

namespace App\Models;

use Etus\Framework\Database\Model;
use Etus\Framework\Database\Connection;

class Role extends Model
{
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

    // ── Shortcut ──────────────────────────────────────────────────────────────

    private function db(): Connection
    {
        return Connection::getInstance();
    }
}
