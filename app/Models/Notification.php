<?php

namespace App\Models;

use Etus\Framework\Database\Connection;
use Etus\Framework\Database\Model;

class Notification extends Model
{
    public const TYPE_REPORT_SUBMITTED = 'report_submitted';
    public const TYPE_REPORT_REVIEWED  = 'report_reviewed';
    public const TYPE_REPORT_ACTIONED  = 'report_actioned';
    public const TYPE_REPORT_ESCALATED = 'report_escalated';
    public const TYPE_TASK_ASSIGNED    = 'task_assigned';
    public const TYPE_TEAM_ADDED       = 'team_added';
    public const TYPE_GENERAL          = 'info';

    protected string $table      = 'notifications';
    protected string $primaryKey = 'id';
    protected bool   $timestamps  = false;
    protected bool   $logging     = false;

    // ── Read ─────────────────────────────────────────────────────────────────

    /**
     * All notifications for a user, newest first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forUserOLD($userId, int $limit = 30): array
    {
        return $this->db()->select(
            'SELECT n.*, u.full_name AS sender_name
             FROM   notifications n
             LEFT JOIN users u ON u.user_id = n.sender_id
             WHERE  n.user_id = ?
             ORDER  BY n.created_at DESC
             LIMIT  ?',
            [$userId, $limit],
        );
    }
    
    public function forUser($userId, int $limit = 30): array
    {
        $limit = (int) $limit;

        return $this->db()->select(
            "SELECT n.*, u.full_name AS sender_name
         FROM notifications n
         LEFT JOIN users u ON u.user_id = n.sender_id
         WHERE n.user_id = ?
         ORDER BY n.created_at DESC
         LIMIT $limit",
            [$userId]
        );
    }

    /**
     * Unread notifications only — used for the bell badge count.
     *
     * @return array<int, array<string, mixed>>
     */
    public function unread($userId): array
    {
        return $this->db()->select(
            'SELECT n.*, u.full_name AS sender_name
             FROM   notifications n
             LEFT JOIN users u ON u.user_id = n.sender_id
             WHERE  n.user_id = ? AND n.is_read = 0
             ORDER  BY n.created_at DESC',
            [$userId],
        );
    }

    /**
     * Count of unread notifications — for the badge number.
     */
    public function unreadCount($userId): int
    {
        $row = $this->db()->selectOne(
            'SELECT COUNT(*) AS n FROM notifications WHERE user_id = ? AND is_read = 0',
            [$userId],
        );
        return (int) ($row['n'] ?? 0);
    }

    /**
     * Notifications created after a given timestamp (for long-polling / SSE).
     *
     * @return array<int, array<string, mixed>>
     */
    public function since($userId, string $afterDatetime): array
    {
        return $this->db()->select(
            'SELECT n.*, u.full_name AS sender_name
             FROM   notifications n
             LEFT JOIN users u ON u.user_id = n.sender_id
             WHERE  n.user_id = ? AND n.created_at > ? AND n.is_read = 0
             ORDER  BY n.created_at DESC',
            [$userId, $afterDatetime],
        );
    }

    // ── Mark read ────────────────────────────────────────────────────────────

    public function markRead(int $notificationId, $userId): void
    {
        $this->db()->execute(
            'UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?',
            [$notificationId, $userId],
        );
    }

    public function markAllRead($userId): void
    {
        $this->db()->execute(
            'UPDATE notifications SET is_read = 1 WHERE user_id = ?',
            [$userId],
        );
    }

    // ── Create ───────────────────────────────────────────────────────────────

    /**
     * Send a notification to one or more users.
     *
     * @param int|int[] $userIds
     */
    public static function send(
        string|array $userIds,
        string    $title,
        string    $body,
        string    $url      = '',
        string    $type     = self::TYPE_GENERAL,
        ?string      $senderId = null,
        ?int      $relatedId   = null,
        ?string   $relatedType = null,
    ): void {
        $db      = Connection::getInstance();
        $now     = date('Y-m-d H:i:s');
        $userIds = is_array($userIds) ? $userIds : [$userIds];

        foreach (array_unique($userIds) as $uid) {
            $db->execute(
                'INSERT INTO notifications
                    (user_id, sender_id, title, body, url, type, related_id, related_type, is_read, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?)',
                [$uid, $senderId, $title, $body, $url, $type, $relatedId, $relatedType, $now],
            );
        }
    }

    private function db(): Connection
    {
        return Connection::getInstance();
    }
}
