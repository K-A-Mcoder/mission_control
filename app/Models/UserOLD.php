<?php

namespace App\Models;

use Etus\Framework\Database\Model;
use Etus\Framework\Database\Connection;

class UserOLD extends Model
{
    protected string $table      = 'users';
    protected string $primaryKey = 'user_id';
    protected bool   $timestamps  = true;
    protected bool   $auditFields = false;
    protected bool   $logging     = true;

    // ── Lookups ──────────────────────────────────────────────────────────────

    /**
     * Find a user by email, joining their role name.
     *
     * @return array<string, mixed>|null
     */
    public function findByEmailWithRole(string $email): ?array
    {
        return $this->db()->selectOne(
            'SELECT u.user_id, u.full_name, u.email, u.password, u.status,
                    u.failed_attempts, u.last_failed_at,
                    r.role_name
             FROM   users u
             JOIN   roles r ON r.id = u.role_id
             WHERE  u.email = ?
             LIMIT  1',
            [$email],
        );
    }

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    public function active(): array
    {
        return $this->where('status', 'active')->get();
    }

    // ── Login flow ────────────────────────────────────────────────────────────

    public function resetFailedAttempts($userId): void
    {
        $this->db()->execute(
            'UPDATE users SET failed_attempts = 0, last_login_at = ? WHERE user_id = ?',
            [date('Y-m-d H:i:s'), $userId],
        );
    }

    public function incrementFailedAttempts($userId): void
    {
        $this->db()->execute(
            'UPDATE users SET failed_attempts = failed_attempts + 1, last_failed_at = ? WHERE user_id = ?',
            [date('Y-m-d H:i:s'), $userId],
        );
    }

    public function suspend($userId): void
    {
        $this->db()->execute(
            "UPDATE users SET status = 'suspended' WHERE user_id = ?",
            [$userId],
        );
    }

    public function recordLoginAttempt(string $email, string $ip): void
    {
        $this->db()->execute(
            'INSERT INTO login_attempts (email, ip_address, attempt_time) VALUES (?, ?, ?)',
            [$email, $ip, date('Y-m-d H:i:s')],
        );
    }

    // ── Remember me ───────────────────────────────────────────────────────────

    public function storeRememberToken(int $userId, string $hashedToken, string $expiry): void
    {
        $this->db()->execute(
            'UPDATE users SET remember_token = ?, remember_expiry = ? WHERE user_id = ?',
            [$hashedToken, $expiry, $userId],
        );
    }

    /**
     * Find a user by their raw remember-me token.
     * Hashes the raw token before querying to match what was stored.
     *
     * @return array<string, mixed>|null
     */
    public function findByRememberToken(int $userId, string $rawToken): ?array
    {
        return $this->db()->selectOne(
            'SELECT * FROM users
             WHERE  user_id = ?
             AND    remember_token = ?
             AND    remember_expiry > ?',
            [$userId, hash('sha256', $rawToken), date('Y-m-d H:i:s')],
        );
    }

    public function clearRememberToken(int $userId): void
    {
        $this->db()->execute(
            'UPDATE users SET remember_token = NULL, remember_expiry = NULL WHERE user_id = ?',
            [$userId],
        );
    }

    // ── Shortcut ──────────────────────────────────────────────────────────────

    private function db(): Connection
    {
        return Connection::getInstance();
    }
}
