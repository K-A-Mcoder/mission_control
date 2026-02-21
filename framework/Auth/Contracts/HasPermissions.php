<?php

namespace Etus\Framework\Auth\Contracts;

interface HasPermissions
{
    /**
     * Check if the entity has a specific permission.
     */
    public function can(string $permission): bool;

    /**
     * Check if the entity has any of the given permissions.
     */
    public function canAny(array $permissions): bool;

    /**
     * Check if the entity has all of the given permissions.
     */
    public function canAll(array $permissions): bool;

    /**
     * Check if the entity has a specific role.
     */
    public function hasRole(string $role): bool;

    /**
     * Check if the entity has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool;
}
