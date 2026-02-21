<?php

namespace Etus\Framework\Auth;

class Auth
{
    /**
     * Check if a user is currently logged in.
     */
    public static function check(): bool
    {
        return ! empty($_SESSION['is_logged_in']) && ! empty($_SESSION['user_id']);
    }

    /**
     * Check if no user is logged in.
     */
    public static function guest(): bool
    {
        return ! static::check();
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function name(): ?string
    {
        return $_SESSION['user_name'] ?? null;
    }

    public static function email(): ?string
    {
        return $_SESSION['user_email'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    /**
     * Return all session user data as an array.
     *
     * @return array<string, mixed>|null
     */
    public static function user(): ?array
    {
        if (! static::check()) {
            return null;
        }

        return [
            'user_id' => static::id(),
            'name'    => static::name(),
            'email'   => static::email(),
            'role'    => static::role(),
        ];
    }
}
