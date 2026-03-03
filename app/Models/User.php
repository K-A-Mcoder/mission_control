<?php

namespace App\Models;

use Etus\Framework\Database\Model;
use Etus\Framework\Database\Connection;

// class User extends Model
// {
//     protected string $table      = 'users';
//     protected string $primaryKey = 'user_id';
//     protected bool   $timestamps  = true;
//     protected bool   $auditFields = false;
//     protected bool   $logging     = true;

//     // ── Lookups ──────────────────────────────────────────────────────────────

//     /**
//      * Find a user by email, joining their role name.
//      *
//      * @return array<string, mixed>|null
//      */
//     public function findByEmailWithRole(string $email): ?array
//     {
//         return $this->db()->selectOne(
//             'SELECT u.user_id, u.full_name, u.email, u.password, u.status,
//                     u.failed_attempts, u.last_failed_at,
//                     r.role_name
//              FROM   users u
//              JOIN   roles r ON r.id = u.role_id
//              WHERE  u.email = ?
//              LIMIT  1',
//             [$email],
//         );
//     }

//     public function findByEmail(string $email): ?array
//     {
//         return $this->where('email', $email)->first();
//     }

//     public function active(): array
//     {
//         return $this->where('status', 'active')->get();
//     }

//     // ── Login flow ────────────────────────────────────────────────────────────

//     public function resetFailedAttempts($userId): void
//     {
//         $this->db()->execute(
//             'UPDATE users SET failed_attempts = 0, last_login_at = ? WHERE user_id = ?',
//             [date('Y-m-d H:i:s'), $userId],
//         );
//     }

//     public function incrementFailedAttempts($userId): void
//     {
//         $this->db()->execute(
//             'UPDATE users SET failed_attempts = failed_attempts + 1, last_failed_at = ? WHERE user_id = ?',
//             [date('Y-m-d H:i:s'), $userId],
//         );
//     }

//     public function suspend($userId): void
//     {
//         $this->db()->execute(
//             "UPDATE users SET status = 'suspended' WHERE user_id = ?",
//             [$userId],
//         );
//     }

//     public function recordLoginAttempt(string $email, string $ip): void
//     {
//         $this->db()->execute(
//             'INSERT INTO login_attempts (email, ip_address, attempt_time) VALUES (?, ?, ?)',
//             [$email, $ip, date('Y-m-d H:i:s')],
//         );
//     }

//     // ── Account activation ────────────────────────────────────────────────────

//     /**
//      * Store a hashed activation token with an expiry timestamp.
//      * Always store the HASHED token, never the raw one.
//      */
//     public function storeActivationToken(string $userId, string $hashedToken, string $expiry): void
//     {
//         $this->db()->execute(
//             'UPDATE users
//              SET    activation_token = ?,
//                     activation_expires_at = ?
//              WHERE  user_id = ?',
//             [$hashedToken, $expiry, $userId],
//         );
//     }

//     /**
//      * Find a pending user by their hashed activation token.
//      * Returns null if: not found, already active, or token expired.
//      *
//      * @return array<string, mixed>|null
//      */
//     public function findByActivationToken(string $hashedToken): ?array
//     {
//         return $this->db()->selectOne(
//             "SELECT *
//              FROM   users
//              WHERE  activation_token    = ?
//              AND    status              = 'inactive'
//              AND    activation_expires_at > ?",
//             [$hashedToken, date('Y-m-d H:i:s')],
//         );
//     }

//     /**
//      * Mark a user as active and clear the activation token — single atomic UPDATE.
//      */
//     public function activate($userId): void
//     {
//         $this->db()->execute(
//             "UPDATE users
//              SET    status                = 'active',
//                     activation_token      = NULL,
//                     activation_expires_at = NULL
//              WHERE  user_id = ?",
//             [$userId],
//         );
//     }

//     /**
//      * Find a pending user by email for re-sending the activation link.
//      *
//      * @return array<string, mixed>|null
//      */
//     public function findPendingByEmail(string $email): ?array
//     {
//         return $this->db()->selectOne(
//             "SELECT * FROM users WHERE email = ? AND status = 'pending'",
//             [$email],
//         );
//     }

//     // ── Remember me ───────────────────────────────────────────────────────────

//     public function storeRememberToken($userId, string $hashedToken, string $expiry): void
//     {
//         $this->db()->execute(
//             'UPDATE users SET remember_token = ?, remember_expiry = ? WHERE user_id = ?',
//             [$hashedToken, $expiry, $userId],
//         );
//     }

//     /**
//      * Find a user by their raw remember-me token.
//      * Hashes the raw token before querying to match what was stored.
//      *
//      * @return array<string, mixed>|null
//      */
//     public function findByRememberToken($userId, string $rawToken): ?array
//     {
//         return $this->db()->selectOne(
//             'SELECT * FROM users
//              WHERE  user_id = ?
//              AND    remember_token = ?
//              AND    remember_expiry > ?',
//             [$userId, hash('sha256', $rawToken), date('Y-m-d H:i:s')],
//         );
//     }

//     public function clearRememberToken($userId): void
//     {
//         $this->db()->execute(
//             'UPDATE users SET remember_token = NULL, remember_expiry = NULL WHERE user_id = ?',
//             [$userId],
//         );
//     }

//     public function isFirstUser(): bool
//     {
//         $count = $this->db()->selectOne('SELECT COUNT(*) AS count FROM users');
//         return $count && $count['count'] == 0;
//     }

//     // ── Shortcut ──────────────────────────────────────────────────────────────

//     private function db(): Connection
//     {
//         return Connection::getInstance();
//     }
// }

class User extends Model
{
    public const STATUSES = ['active', 'pending', 'suspended', 'inactive'];

    protected string $table      = 'users';
    protected string $primaryKey = 'user_id';
    protected bool   $timestamps  = true;
    protected bool   $auditFields = false;
    protected bool   $logging     = true;

    // ── Admin: full list ──────────────────────────────────────────────────────

    /** All users with role name + team count — for admin list view */
    public function allWithRoles(): array
    {
        return $this->db()->select(
            'SELECT u.*,
                    r.role_name,
                    r.id AS role_id,
                    COUNT(DISTINCT tm.team_id) AS team_count
             FROM   users u
             JOIN   roles r  ON r.id = u.role_id
             LEFT JOIN team_membership tm ON tm.user_id = u.user_id
             GROUP  BY u.user_id
             ORDER  BY u.created_at DESC',
        );
    }

    /** Single user with role and teams for the show/edit pages */
    public function findWithRole($userId): ?array
    {
        return $this->db()->selectOne(
            'SELECT u.*, r.role_name, r.id AS role_id
             FROM   users u
             JOIN   roles r ON r.id = u.role_id
             WHERE  u.user_id = ?',
            [$userId],
        );
    }

    /** Users list filtered by role for selects/dropdowns */
    public function byRole(string $roleName): array
    {
        return $this->db()->select(
            'SELECT u.user_id, u.full_name, u.email, u.status
             FROM   users u
             JOIN   roles r ON r.id = u.role_id
             WHERE  r.role_name = ? AND u.status = \'active\'
             ORDER  BY u.full_name ASC',
            [$roleName],
        );
    }

    /** All active users with role name (for dropdowns/selects) */
    public function active(): array
    {
        return $this->db()->select(
            'SELECT u.*, r.role_name, r.id AS role_id
             FROM   users u
             JOIN   roles r ON r.id = u.role_id
             WHERE  u.status = \'active\'
             ORDER  BY u.full_name ASC',
        );
    }

    /** Teams a user belongs to */
    public function teams($userId): array
    {
        return $this->db()->select(
            'SELECT t.*, tm.role AS member_role, tm.created_at AS joined_at
             FROM   teams t
             JOIN   team_membership tm ON tm.team_id = t.id
             WHERE  tm.user_id = ? AND t.deleted_at IS NULL
             ORDER  BY t.name ASC',
            [$userId],
        );
    }

    /** Recent tasks assigned to user */
    public function recentTasks($userId, int $limit = 5): array
    {
        return $this->db()->select(
            'SELECT tk.*, t.name AS team_name
             FROM   tasks tk
             LEFT JOIN teams t ON t.id = tk.team_id
             WHERE  tk.assigned_to = ? AND tk.deleted_at IS NULL
             ORDER  BY tk.created_at DESC LIMIT ?',
            [$userId, $limit],
        );
    }

    /** Stats summary for a user */
    public function stats($userId): array
    {
        $tasks = $this->db()->selectOne(
            'SELECT COUNT(*) AS total,
                    SUM(CASE WHEN status=\'open\' THEN 1 ELSE 0 END) AS open,
                    SUM(CASE WHEN status=\'in_progress\' THEN 1 ELSE 0 END) AS in_progress,
                    SUM(CASE WHEN status=\'closed\' THEN 1 ELSE 0 END) AS closed
             FROM   tasks WHERE assigned_to=? AND deleted_at IS NULL',
            [$userId],
        );
        $reports = $this->db()->selectOne(
            'SELECT COUNT(*) AS total FROM reports WHERE author_id=?',
            [$userId],
        );
        return [
            'tasks'   => $tasks   ?? ['total' => 0, 'open' => 0, 'in_progress' => 0, 'closed' => 0],
            'reports' => $reports ?? ['total' => 0],
        ];
    }

    // ── Status management ─────────────────────────────────────────────────────

    public function setStatus($userId, string $status): void
    {
        if (! in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException("Invalid status [{$status}].");
        }
        $this->db()->execute(
            'UPDATE users SET status=?, updated_at=? WHERE user_id=?',
            [$status, date('Y-m-d H:i:s'), $userId],
        );
    }

    public function changeRole($userId, int $roleId): void
    {
        $this->db()->execute(
            'UPDATE users SET role_id=?, updated_at=? WHERE user_id=?',
            [$roleId, date('Y-m-d H:i:s'), $userId],
        );
    }

    public function updateProfile($userId, array $data): void
    {
        $allowed = ['full_name', 'email'];
        $set     = [];
        $params  = [];

        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $set[]    = "{$col} = ?";
                $params[] = $data[$col];
            }
        }

        if (empty($set)) return;

        $params[] = date('Y-m-d H:i:s');
        $params[] = $userId;

        $this->db()->execute(
            'UPDATE users SET ' . implode(', ', $set) . ', updated_at=? WHERE user_id=?',
            $params,
        );
    }

    public function resetPassword($userId, string $hashedPassword): void
    {
        $this->db()->execute(
            'UPDATE users SET password=?, updated_at=? WHERE user_id=?',
            [$hashedPassword, date('Y-m-d H:i:s'), $userId],
        );
    }

    public function softDelete($userId): void
    {
        $this->setStatus($userId, 'inactive');
    }

    public function isFirstUser(): bool
    {
        $count = $this->db()->selectOne('SELECT COUNT(*) AS count FROM users');
        return $count && $count['count'] == 0;
    }

    // ── Auth flow ─────────────────────────────────────────────────────────────

    public function findByEmailWithRole(string $email): ?array
    {
        return $this->db()->selectOne(
            'SELECT u.user_id, u.full_name, u.email, u.password, u.status,
                    u.failed_attempts, u.last_failed_at, r.role_name
             FROM   users u
             JOIN   roles r ON r.id = u.role_id
             WHERE  u.email = ? LIMIT 1',
            [$email],
        );
    }

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    public function resetFailedAttempts($userId): void
    {
        $this->db()->execute(
            'UPDATE users SET failed_attempts=0, last_login_at=? WHERE user_id=?',
            [date('Y-m-d H:i:s'), $userId],
        );
    }

    public function incrementFailedAttempts($userId): void
    {
        $this->db()->execute(
            'UPDATE users SET failed_attempts=failed_attempts+1, last_failed_at=? WHERE user_id=?',
            [date('Y-m-d H:i:s'), $userId],
        );
    }

    public function suspend($userId): void
    {
        $this->setStatus($userId, 'suspended');
    }

    public function recordLoginAttempt(string $email, string $ip): void
    {
        $this->db()->execute(
            'INSERT INTO login_attempts (email, ip_address, attempt_time) VALUES (?,?,?)',
            [$email, $ip, date('Y-m-d H:i:s')],
        );
    }

    // ── Activation ────────────────────────────────────────────────────────────

    public function storeActivationToken($userId, string $hashedToken, string $expiry): void
    {
        $this->db()->execute(
            'UPDATE users SET activation_token=?, activation_expires_at=? WHERE user_id=?',
            [$hashedToken, $expiry, $userId],
        );
    }

    public function findByActivationToken(string $hashedToken): ?array
    {
        return $this->db()->selectOne(
            "SELECT * FROM users
             WHERE activation_token=? AND status='pending' AND activation_expires_at>?",
            [$hashedToken, date('Y-m-d H:i:s')],
        );
    }

    public function activate($userId): void
    {
        $this->db()->execute(
            "UPDATE users SET status='active', activation_token=NULL, activation_expires_at=NULL WHERE user_id=?",
            [$userId],
        );
    }

    public function findPendingByEmail(string $email): ?array
    {
        return $this->db()->selectOne(
            "SELECT * FROM users WHERE email=? AND status='pending'",
            [$email],
        );
    }

    // ── Remember me ───────────────────────────────────────────────────────────

    public function storeRememberToken($userId, string $hashedToken, string $expiry): void
    {
        $this->db()->execute(
            'UPDATE users SET remember_token=?, remember_expiry=? WHERE user_id=?',
            [$hashedToken, $expiry, $userId],
        );
    }

    public function findByRememberToken($userId, string $rawToken): ?array
    {
        return $this->db()->selectOne(
            'SELECT * FROM users WHERE user_id=? AND remember_token=? AND remember_expiry>?',
            [$userId, hash('sha256', $rawToken), date('Y-m-d H:i:s')],
        );
    }

    public function clearRememberToken($userId): void
    {
        $this->db()->execute(
            'UPDATE users SET remember_token=NULL, remember_expiry=NULL WHERE user_id=?',
            [$userId],
        );
    }

    // ── Activity log ─────────────────────────────────────────────────────────

    public function activityLog($userId, int $limit = 20): array
    {
        return $this->db()->select(
            'SELECT * FROM activity_logs WHERE user_id=? ORDER BY created_at DESC LIMIT ?',
            [$userId, $limit],
        );
    }

    private function db(): Connection
    {
        return Connection::getInstance();
    }
}
