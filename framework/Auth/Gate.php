<?php

namespace Etus\Framework\Auth;

use Etus\Framework\Exceptions\AuthorizationException;

class Gate
{
    /** @var array<string, callable> Custom ability definitions */
    private static array $abilities = [];

    /** @var array<string, array<string>> Role → permissions map (cached) */
    private static array $rolePermissionsCache = [];

    // ── Ability definitions ───────────────────────────────────────────────────

    /**
     * Register a custom ability.
     *
     *   Gate::define('edit-post', function (array $user, array $post): bool {
     *       return $user['user_id'] === $post['author_id'];
     *   });
     */
    public static function define(string $ability, callable $callback): void
    {
        static::$abilities[$ability] = $callback;
    }

    // ── Core checks ───────────────────────────────────────────────────────────

    /**
     * Check if the current session user has a permission.
     */
    public static function can(string $permission): bool
    {
        $user = static::currentUser();

        if (! $user) {
            return false;
        }

        // Super-admin bypasses all checks
        if (static::isSuperAdmin($user)) {
            return true;
        }

        // Check a registered custom ability first
        if (isset(static::$abilities[$permission])) {
            return (bool) call_user_func(static::$abilities[$permission], $user);
        }

        // Fall through to role-based permission lookup
        return static::roleHasPermission($user['role'], $permission);
    }

    /**
     * Check if the current user has ANY of the given permissions.
     */
    public static function canAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (static::can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the current user has ALL of the given permissions.
     */
    public static function canAll(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (! static::can($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Inverse of can().
     */
    public static function cannot(string $permission): bool
    {
        return ! static::can($permission);
    }

    // ── Role checks ───────────────────────────────────────────────────────────

    public static function hasRole(string $role): bool
    {
        $user = static::currentUser();

        return $user && strtolower($user['role']) === strtolower($role);
    }

    public static function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if (static::hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    // ── Enforcement ───────────────────────────────────────────────────────────

    /**
     * Throw an AuthorizationException if the user cannot perform the action.
     *
     *   Gate::authorize('edit-users');
     */
    public static function authorize(string $permission): void
    {
        if (! static::can($permission)) {
            throw new AuthorizationException(
                "You are not authorized to perform [{$permission}]."
            );
        }
    }

    /**
     * Throw an AuthorizationException if the user does not have the role.
     *
     *   Gate::authorizeRole('admin');
     */
    public static function authorizeRole(string $role): void
    {
        if (! static::hasRole($role)) {
            throw new AuthorizationException(
                "You do not have the required role [{$role}]."
            );
        }
    }

    // ── Cache management ──────────────────────────────────────────────────────

    /**
     * Pre-load permissions for a role into the cache.
     * Call this at login time for best performance.
     *
     * @param array<int, string> $permissions
     */
    public static function cacheRolePermissions(string $role, array $permissions): void
    {
        static::$rolePermissionsCache[strtolower($role)] = $permissions;
    }

    public static function flushCache(): void
    {
        static::$rolePermissionsCache = [];
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    /**
     * @return array<string, mixed>|null
     */
    public static function currentUser(): ?array
    {
        if (empty($_SESSION['is_logged_in']) || empty($_SESSION['user_id'])) {
            return null;
        }

        return [
            'user_id' => $_SESSION['user_id'],
            'role'    => $_SESSION['role'] ?? '',
        ];
    }

    private static function isSuperAdmin(array $user): bool
    {
        return strtolower($user['role']) === 'super_admin';
    }

    private static function roleHasPermission(string $role, string $permission): bool
    {
        $role = strtolower($role);

        if (isset(static::$rolePermissionsCache[$role])) {
            return in_array($permission, static::$rolePermissionsCache[$role], strict: true);
        }

        // No cache loaded — load from DB on demand
        static::loadPermissionsFromDb($role);

        return in_array(
            $permission,
            static::$rolePermissionsCache[$role] ?? [],
            strict: true,
        );
    }

    private static function loadPermissionsFromDb(string $role): void
    {
        try {
            $db   = \Etus\Framework\Database\Connection::getInstance();
            $rows = $db->select(
                'SELECT p.name
                 FROM   permissions p
                 JOIN   role_permissions rp ON rp.permission_id = p.id
                 JOIN   roles r             ON r.id = rp.role_id
                 WHERE  LOWER(r.role_name) = ?',
                [strtolower($role)],
            );

            static::$rolePermissionsCache[$role] = array_column($rows, 'name');
        } catch (\Throwable) {
            static::$rolePermissionsCache[$role] = [];
        }
    }
}
