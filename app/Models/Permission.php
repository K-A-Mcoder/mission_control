<?php

namespace App\Models;

use Etus\Framework\Auth\Gate;
use Etus\Framework\Database\Model;
use Etus\Framework\Database\Connection;

class Permission extends Model
{
    // ── Constants ─────────────────────────────────────────────────────────────

    /**
     * Canonical group definitions in display order.
     * Keys are the stored `group` column value; values are the UI label.
     * Used to render the permission matrix in the correct order.
     */
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
        'general'     => 'General',   // catch-all for uncategorised permissions
    ];

    /**
     * Icons to show beside each group in the permission matrix.
     */
    public const GROUP_ICONS = [
        'users'       => 'fa-user',
        'roles'       => 'fa-shield-halved',
        'permissions' => 'fa-key',
        'missions'    => 'fa-crosshairs',
        'teams'       => 'fa-users',
        'tasks'       => 'fa-list-check',
        'reports'     => 'fa-file-lines',
        'chat'        => 'fa-comments',
        'settings'    => 'fa-sliders',
        'general'     => 'fa-layer-group',
    ];

    // ── Model config ──────────────────────────────────────────────────────────

    protected string $table      = 'permissions';
    protected string $primaryKey = 'id';
    protected bool   $timestamps  = true;
    protected bool   $auditFields = false;
    protected bool   $logging     = true;

    // ── Basic lookups ─────────────────────────────────────────────────────────

    /**
     * All permissions as a plain flat array ordered by group then name.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(): array
    {
        return $this->db()->select(
            'SELECT * FROM permissions ORDER BY "group" ASC, name ASC',
        );
    }

    /**
     * Find a permission by its slug (e.g. 'edit-users').
     *
     * @return array<string, mixed>|null
     */
    public function findByName(string $name): ?array
    {
        return $this->where('name', $name)->first();
    }

    /**
     * Find a permission by name or throw if it doesn't exist.
     *
     * @return array<string, mixed>
     * @throws \RuntimeException
     */
    public function findByNameOrFail(string $name): array
    {
        $perm = $this->findByName($name);

        if ($perm === null) {
            throw new \RuntimeException("Permission [{$name}] not found.");
        }

        return $perm;
    }

    /**
     * Whether a permission slug is already taken (case-insensitive).
     * Pass $excludeId when editing an existing permission.
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $sql    = 'SELECT COUNT(*) AS n FROM permissions WHERE LOWER(name) = ?';
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
     * All permissions grouped by their `group` column, respecting the
     * canonical GROUPS ordering defined in the constant.
     *
     * Returns: [ 'users' => [ [...], [...] ], 'roles' => [...], … ]
     *
     * Any group not in the GROUPS constant is appended at the end
     * under its raw group value.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function allGrouped(): array
    {
        // $rows = $this->db()->select(
        //     'SELECT * FROM permissions ORDER BY "group" ASC, name ASC',
        // );

        $rows = $this->allWithRoleCount();

        // Build a bucket for every known group, preserving GROUPS order
        $grouped = array_fill_keys(array_keys(self::GROUPS), []);

        foreach ($rows as $row) {
            $g = $row['group'] ?? 'general';
            // If the group is not in our canonical list, create it on-the-fly
            if (! array_key_exists($g, $grouped)) {
                $grouped[$g] = [];
            }
            $grouped[$g][] = $row;
        }

        // Strip empty buckets so the UI doesn't render empty group headers
        return array_filter($grouped, fn($perms) => ! empty($perms));
    }

    /**
     * All permissions with a role_count column — used by the permissions
     * admin index to show how many roles carry each permission.
     *
     * @return array<int, array<string, mixed>>
     */
    public function allWithRoleCount(): array
    {
        return $this->db()->select(
            'SELECT p.*, COUNT(rp.role_id) AS role_count
             FROM   permissions p
             LEFT JOIN role_permissions rp ON rp.permission_id = p.id
             GROUP  BY p.id
             ORDER  BY p.`group` ASC, p.name ASC',
        );
    }

    /**
     * Same as allWithRoleCount() but returns data already split into groups,
     * in canonical GROUPS order, with group metadata (icon, label) merged in.
     * Used by the permission matrix admin page.
     *
     * @return array<string, array{label: string, icon: string, permissions: array}>
     */
    public function allGroupedWithMeta(): array
    {
        $flat    = $this->allWithRoleCount();
        $buckets = array_fill_keys(array_keys(self::GROUPS), []);

        foreach ($flat as $perm) {
            $g = $perm['group'] ?? 'general';
            if (! array_key_exists($g, $buckets)) {
                $buckets[$g] = [];
            }
            $buckets[$g][] = $perm;
        }

        $result = [];
        foreach ($buckets as $slug => $perms) {
            if (empty($perms)) continue;
            $result[$slug] = [
                'label'       => self::GROUPS[$slug]       ?? ucfirst($slug),
                'icon'        => self::GROUP_ICONS[$slug]  ?? 'fa-layer-group',
                'permissions' => $perms,
            ];
        }

        return $result;
    }

    /**
     * Flat list for <select> dropdowns:
     *   [ ['id' => 1, 'name' => 'view-users', 'label' => 'View Users'], … ]
     *
     * @return array<int, array<string, mixed>>
     */
    public function allForSelect(): array
    {
        return $this->db()->select(
            'SELECT id, name, label, "group" AS grp
             FROM   permissions
             ORDER  BY "group" ASC, label ASC',
        );
    }

    // ── Role-scoped queries ───────────────────────────────────────────────────

    /**
     * All permission rows assigned to a specific role.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forRole(int $roleId): array
    {
        return $this->db()->select(
            'SELECT p.*
             FROM   permissions p
             JOIN   role_permissions rp ON rp.permission_id = p.id
             WHERE  rp.role_id = ?
             ORDER  BY p."group" ASC, p.name ASC',
            [$roleId],
        );
    }

    /**
     * Permission IDs for a role — shorthand for pre-ticking checkboxes.
     *
     * @return array<int, int>
     */
    public function idsForRole(int $roleId): array
    {
        return array_map('intval', array_column($this->forRole($roleId), 'id'));
    }

    /**
     * Permission names (slugs) for a role — shorthand for Gate cache loading.
     *
     * @return array<int, string>
     */
    public function namesForRole(int $roleId): array
    {
        return array_column($this->forRole($roleId), 'name');
    }

    /**
     * All permissions NOT yet assigned to a role — useful for an "add permission" picker.
     *
     * @return array<int, array<string, mixed>>
     */
    public function notAssignedToRole(int $roleId): array
    {
        return $this->db()->select(
            'SELECT p.*
             FROM   permissions p
             WHERE  p.id NOT IN (
                 SELECT permission_id FROM role_permissions WHERE role_id = ?
             )
             ORDER  BY p."group" ASC, p.name ASC',
            [$roleId],
        );
    }

    // ── Bulk operations ───────────────────────────────────────────────────────

    /**
     * Create multiple permissions in one call.
     * Each item: ['name' => '…', 'label' => '…', 'group' => '…']
     * Skips any slug that already exists (INSERT OR IGNORE).
     *
     * @param array<int, array<string, mixed>> $permissions
     */
    public function bulkCreate(array $permissions): void
    {
        $now = date('Y-m-d H:i:s');

        foreach ($permissions as $perm) {
            $this->db()->execute(
                'INSERT OR IGNORE INTO permissions (name, label, "group", created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?)',
                [
                    strtolower(trim($perm['name']  ?? '')),
                    trim($perm['label'] ?? ''),
                    strtolower(trim($perm['group'] ?? 'general')),
                    $now,
                    $now,
                ],
            );
        }
    }

    /**
     * Assign a set of permission IDs to a role without removing existing ones.
     * Flushed Gate cache after applying.
     *
     * @param array<int, int> $permissionIds
     */
    public function bulkAssignToRole(int $roleId, array $permissionIds): void
    {
        foreach (array_unique(array_map('intval', $permissionIds)) as $pid) {
            if ($pid > 0) {
                $this->db()->execute(
                    'INSERT OR IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)',
                    [$roleId, $pid],
                );
            }
        }

        Gate::flushCache();
    }

    // ── Guards ────────────────────────────────────────────────────────────────

    /**
     * Whether a permission name is safe to delete.
     * A permission in use by any role is still deletable (the pivot row
     * will cascade) — this is just an informational helper.
     */
    public function isInUse(int $permissionId): bool
    {
        $row = $this->db()->selectOne(
            'SELECT COUNT(*) AS n FROM role_permissions WHERE permission_id = ?',
            [$permissionId],
        );
        return (int)($row['n'] ?? 0) > 0;
    }

    /**
     * How many roles currently carry a specific permission.
     */
    public function roleCount(int $permissionId): int
    {
        $row = $this->db()->selectOne(
            'SELECT COUNT(*) AS n FROM role_permissions WHERE permission_id = ?',
            [$permissionId],
        );
        return (int)($row['n'] ?? 0);
    }

    /**
     * Whether a given group slug is one of the canonical groups.
     */
    public static function isKnownGroup(string $group): bool
    {
        return array_key_exists(strtolower($group), self::GROUPS);
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function db(): Connection
    {
        return Connection::getInstance();
    }
}
