<?php

namespace App\Models;

use Etus\Framework\Auth\Gate;
use Etus\Framework\Database\Model;
use Etus\Framework\Database\Connection;

class Role extends Model
{
    // ── Constants ─────────────────────────────────────────────────────────────

    /**
     * System-defined roles that ship with the application.
     * These cannot be deleted and their names cannot be changed.
     * super_admin always bypasses all permission checks in Gate.
     */
    public const SYSTEM_ROLES = [
        'super_admin',
        'admin',
        'manager',
        'user',
    ];

    /**
     * Display labels for system roles — used in the admin UI.
     */
    public const SYSTEM_ROLE_LABELS = [
        'super_admin' => 'Super Admin',
        'admin'       => 'Admin',
        'manager'     => 'Manager / Commander',
        'user'        => 'User',
    ];

    // ── Model config ──────────────────────────────────────────────────────────

    protected string $table      = 'roles';
    protected string $primaryKey = 'id';
    protected bool   $timestamps  = true;
    protected bool   $auditFields = false;
    protected bool   $logging     = true;

    // ── Basic lookups ─────────────────────────────────────────────────────────

    /**
     * All roles as a plain flat array.
     * Alias of all() — used by views that need a simple list for dropdowns.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(): array
    {
        return $this->db()->select(
            'SELECT * FROM roles ORDER BY id ASC',
        );
    }

    /**
     * Find a role by its slug name (e.g. 'super_admin').
     *
     * @return array<string, mixed>|null
     */
    public function findByName(string $name): ?array
    {
        return $this->where('role_name', $name)->first();
    }

    /**
     * Find a role by name or throw if it doesn't exist.
     *
     * @return array<string, mixed>
     * @throws \RuntimeException
     */
    public function findByNameOrFail(string $name): array
    {
        $role = $this->findByName($name);

        if ($role === null) {
            throw new \RuntimeException("Role [{$name}] not found.");
        }

        return $role;
    }

    /**
     * Whether a role name slug is already taken (case-insensitive).
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $sql    = 'SELECT COUNT(*) AS n FROM roles WHERE LOWER(role_name) = ?';
        $params = [strtolower($name)];

        if ($excludeId !== null) {
            $sql    .= ' AND id != ?';
            $params[] = $excludeId;
        }

        $row = $this->db()->selectOne($sql, $params);
        return (int)($row['n'] ?? 0) > 0;
    }

    // ── Collections ───────────────────────────────────────────────────────────

    /**
     * All roles with aggregate user count and permission count.
     * Used by the admin roles index card view.
     *
     * @return array<int, array<string, mixed>>
     */
    public function allWithCounts(): array
    {
        return $this->db()->select(
            'SELECT r.*,
                    COUNT(DISTINCT u.user_id)       AS user_count,
                    COUNT(DISTINCT rp.permission_id) AS permission_count
             FROM   roles r
             LEFT JOIN users u             ON u.role_id  = r.id
             LEFT JOIN role_permissions rp ON rp.role_id = r.id
             GROUP  BY r.id
             ORDER  BY r.id ASC',
        );
    }

    /**
     * Flat list suitable for <select> dropdowns:
     *   [ ['id' => 1, 'role_name' => 'admin', 'label' => 'Admin'], … ]
     *
     * @return array<int, array<string, mixed>>
     */
    public function allForSelect(): array
    {
        $rows = $this->get();

        foreach ($rows as &$row) {
            $row['label'] = self::SYSTEM_ROLE_LABELS[$row['role_name']]
                ?? ucwords(str_replace('_', ' ', $row['role_name']));
        }

        return $rows;
    }

    // ── Users in a role ───────────────────────────────────────────────────────

    /**
     * Users that have been assigned this role, with basic profile columns.
     *
     * @return array<int, array<string, mixed>>
     */
    public function users(int $roleId, int $limit = 100): array
    {
        $sql = 'SELECT u.user_id, u.full_name, u.email, u.status, u.last_login_at, u.created_at
             FROM   users u
             WHERE  u.role_id = ?
             ORDER  BY u.full_name ASC
             LIMIT ' . $limit;
        return $this->db()->select(
            $sql,
            [$roleId],
        );
    }

    /**
     * Count of users carrying a specific role.
     */
    public function userCount(int $roleId): int
    {
        $row = $this->db()->selectOne(
            'SELECT COUNT(*) AS n FROM users WHERE role_id = ?',
            [$roleId],
        );
        return (int)($row['n'] ?? 0);
    }

    // ── Permissions on a role ─────────────────────────────────────────────────

    /**
     * All permission rows belonging to a role, ordered by group then name.
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
             ORDER  BY p.`group` ASC, p.name ASC',
            [$roleId],
        );
    }

    /**
     * Permission name strings only — used by Gate::cacheRolePermissions().
     *
     * @return array<int, string>
     */
    public function permissionNames(int $roleId): array
    {
        return array_column($this->permissions($roleId), 'name');
    }

    /**
     * Permission IDs only — used to pre-tick checkboxes in the admin UI.
     *
     * @return array<int, int>
     */
    public function permissionIds(int $roleId): array
    {
        return array_map('intval', array_column($this->permissions($roleId), 'id'));
    }

    /**
     * Check whether a role currently has a named permission.
     */
    public function hasPermission(int $roleId, string $permissionName): bool
    {
        $row = $this->db()->selectOne(
            'SELECT 1 AS found
             FROM   role_permissions rp
             JOIN   permissions p ON p.id = rp.permission_id
             WHERE  rp.role_id = ? AND p.name = ?
             LIMIT  1',
            [$roleId, $permissionName],
        );
        return $row !== null;
    }

    // ── Permission mutations ──────────────────────────────────────────────────

    /**
     * Add a single permission to a role (idempotent — safe to call twice).
     * Flushes the Gate permission cache after the change.
     */
    public function addPermission(int $roleId, int $permissionId): void
    {
        $this->db()->execute(
            'INSERT OR IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)',
            [$roleId, $permissionId],
        );

        Gate::flushCache();
    }

    /**
     * Remove a single permission from a role.
     * Flushes the Gate permission cache after the change.
     */
    public function removePermission(int $roleId, int $permissionId): void
    {
        $this->db()->execute(
            'DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?',
            [$roleId, $permissionId],
        );

        Gate::flushCache();
    }

    /**
     * Replace ALL permissions for a role atomically in a transaction.
     * Safe to call with an empty array (removes all permissions).
     * Flushes the Gate permission cache after the change.
     *
     * @param array<int, int> $permissionIds
     */
    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $db  = $this->db();
        $ids = array_unique(array_map('intval', $permissionIds));

        $db->execute('DELETE FROM role_permissions WHERE role_id = ?', [$roleId]);

        foreach ($ids as $pid) {
            if ($pid > 0) {
                $db->execute(
                    'INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)',
                    [$roleId, $pid],
                );
            }
        }

        Gate::flushCache();
    }

    /**
     * Assign every existing permission in the DB to a role.
     * Used when creating the super_admin role or resetting it.
     */
    public function grantAllPermissions(int $roleId): void
    {
        $this->db()->execute(
            'INSERT OR IGNORE INTO role_permissions (role_id, permission_id)
             SELECT ?, id FROM permissions',
            [$roleId],
        );

        Gate::flushCache();
    }

    // ── Role mutations ────────────────────────────────────────────────────────

    /**
     * Rename a custom role.
     * Will not rename system roles — returns false if role is protected.
     */
    public function rename(int $roleId, string $newName): bool
    {
        $role = $this->find($roleId);

        if (! $role || $this->isSystemRole($role['role_name'])) {
            return false;
        }

        $slug = strtolower(str_replace([' ', '-'], '_', trim($newName)));

        $this->db()->execute(
            'UPDATE roles SET role_name = ?, updated_at = ? WHERE id = ?',
            [$slug, date('Y-m-d H:i:s'), $roleId],
        );

        return true;
    }

    // ── Guards ────────────────────────────────────────────────────────────────

    /**
     * Whether a role slug is a protected system role that cannot be deleted or renamed.
     */
    public function isSystemRole(string $roleName): bool
    {
        return in_array(strtolower($roleName), self::SYSTEM_ROLES, true);
    }

    /**
     * Whether a role can be safely deleted:
     *   - not a system role
     *   - has zero users assigned
     */
    public function isDeletable(int $roleId): bool
    {
        $role = $this->find($roleId);

        if (! $role || $this->isSystemRole($role['role_name'])) {
            return false;
        }

        return $this->userCount($roleId) === 0;
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function db(): Connection
    {
        return Connection::getInstance();
    }
}
