<?php

namespace App\Models;

use Etus\Framework\Database\Model;
use Etus\Framework\Database\Connection;

class ChatRoom extends Model
{
    /** Room types */
    public const TYPE_TEAM    = 'team';      // Team + mission bound room, auto-created
    public const TYPE_DIRECT  = 'direct';    // 1-to-1 DM between any two users
    public const TYPE_COMMAND = 'command';   // Commander ↔ commander channel

    public const TYPES = [self::TYPE_TEAM, self::TYPE_DIRECT, self::TYPE_COMMAND];

    /** Roles that can participate in command channels */
    public const COMMAND_ROLES = ['super_admin', 'admin', 'manager', 'lead'];

    protected string $table      = 'chat_rooms';
    protected string $primaryKey = 'id';
    protected bool   $timestamps  = true;
    protected bool   $auditFields = false;
    protected bool   $logging     = false;

    // ── Lookups ───────────────────────────────────────────────────────────────

    public function findWithParticipants(int $id): ?array
    {
        $room = $this->db()->selectOne(
            'SELECT cr.*,
                    t.name  AS team_name,
                    m.title AS mission_title,
                    m.m_code AS mission_code
             FROM   chat_rooms cr
             LEFT JOIN teams    t ON t.id  = cr.team_id
             LEFT JOIN missions m ON m.id  = cr.mission_id
             WHERE  cr.id = ?',
            [$id],
        );

        if (!$room) return null;

        $room['participants'] = $this->participants($id);
        return $room;
    }

    /** All rooms a user can access */
    public function forUser($userId): array
    {
        return $this->db()->select(
            "SELECT cr.*,
            t.name   AS team_name,
            m.title  AS mission_title,
            (SELECT body_preview
             FROM chat_messages
             WHERE room_id = cr.id
             ORDER BY id DESC
             LIMIT 1) AS last_preview,
            (SELECT created_at
             FROM chat_messages
             WHERE room_id = cr.id
             ORDER BY id DESC
             LIMIT 1) AS last_message_at,
            (SELECT COUNT(*)
             FROM chat_messages cm2
             LEFT JOIN chat_message_reads cmr
                    ON cmr.message_id = cm2.id
                    AND cmr.user_id = ?
             WHERE cm2.room_id = cr.id
               AND cmr.message_id IS NULL
            ) AS unread_count
     FROM chat_rooms cr
     JOIN chat_participants cp
          ON cp.room_id = cr.id
          AND cp.user_id = ?
     LEFT JOIN teams t    ON t.id = cr.team_id
     LEFT JOIN missions m ON m.id = cr.mission_id
     WHERE cr.is_active = 1
     ORDER BY last_message_at IS NULL,
              last_message_at DESC,
              cr.created_at DESC",
            [$userId, $userId],
        );
        // return $this->db()->select(
        //     "SELECT cr.*,
        //             t.name   AS team_name,
        //             m.title  AS mission_title,
        //             (SELECT body_preview FROM chat_messages
        //              WHERE room_id = cr.id ORDER BY id DESC LIMIT 1) AS last_preview,
        //             (SELECT created_at FROM chat_messages
        //              WHERE room_id = cr.id ORDER BY id DESC LIMIT 1) AS last_message_at,
        //             (SELECT COUNT(*) FROM chat_messages cm2
        //              JOIN chat_message_reads cmr ON cmr.message_id = cm2.id AND cmr.user_id = ?
        //              WHERE cm2.room_id = cr.id AND cmr.read_at IS NULL) AS unread_count
        //      FROM   chat_rooms cr
        //      JOIN   chat_participants cp ON cp.room_id = cr.id AND cp.user_id = ?
        //      LEFT JOIN teams    t ON t.id  = cr.team_id
        //      LEFT JOIN missions m ON m.id  = cr.mission_id
        //      WHERE  cr.is_active = 1
        //      ORDER  BY last_message_at DESC NULLS LAST, cr.created_at DESC",
        //     [$userId, $userId],
        // );
    }

    /** Simpler unread count query */
    public function unreadCount(int $roomId, $userId): int
    {
        $row = $this->db()->selectOne(
            'SELECT COUNT(*) AS n FROM chat_messages
             WHERE room_id=? AND sender_id != ?
             AND id NOT IN (SELECT message_id FROM chat_message_reads WHERE user_id=?)',
            [$roomId, $userId, $userId],
        );
        return (int)($row['n'] ?? 0);
    }

    /** Participants with user details */
    public function participants(int $roomId): array
    {
        return $this->db()->select(
            'SELECT cp.*, u.full_name, u.email, r.role_name,
                    cp.is_admin AS is_room_admin
             FROM   chat_participants cp
             JOIN   users u ON u.user_id = cp.user_id
             JOIN   roles r ON r.id = u.role_id
             WHERE  cp.room_id=?
             ORDER  BY u.full_name ASC',
            [$roomId],
        );
    }

    public function participantIds(int $roomId): array
    {
        $rows = $this->db()->select(
            'SELECT user_id FROM chat_participants WHERE room_id=?',
            [$roomId],
        );
        return array_map('intval', array_column($rows, 'user_id'));
    }

    public function isParticipant(int $roomId, $userId): bool
    {
        $row = $this->db()->selectOne(
            'SELECT id FROM chat_participants WHERE room_id=? AND user_id=?',
            [$roomId, $userId],
        );
        return $row !== null;
    }

    // ── Direct message room ───────────────────────────────────────────────────

    /** Find existing direct room between two users, or return null */
    public function findDirectRoom($userA, $userB): ?array
    {
        return $this->db()->selectOne(
            "SELECT cr.* FROM chat_rooms cr
             WHERE  cr.type = 'direct'
             AND    EXISTS (SELECT 1 FROM chat_participants WHERE room_id=cr.id AND user_id=?)
             AND    EXISTS (SELECT 1 FROM chat_participants WHERE room_id=cr.id AND user_id=?)
             LIMIT 1",
            [$userA, $userB],
        );
    }

    /** Create a direct message room between two users */
    public function createDirect($userA, $userB): int
    {
        // Prevent duplicates
        $existing = $this->findDirectRoom($userA, $userB);
        if ($existing) return (int)$existing['id'];

        $now    = date('Y-m-d H:i:s');
        $roomId = $this->db()->insert(
            'INSERT INTO chat_rooms (type, name, is_active, created_at, updated_at)
             VALUES (?, ?, 1, ?, ?)',
            [self::TYPE_DIRECT, "DM:{$userA}:{$userB}", $now, $now],
        );

        $this->addParticipant($roomId, $userA);
        $this->addParticipant($roomId, $userB);

        return $roomId;
    }

    // ── Team room ─────────────────────────────────────────────────────────────

    /** Find or create a team room for a team+mission combination */
    public function findOrCreateTeamRoom(int $teamId, ?int $missionId, string $name): int
    {
        $existing = $this->db()->selectOne(
            "SELECT id FROM chat_rooms WHERE type='team' AND team_id=? AND (mission_id=? OR mission_id IS NULL) LIMIT 1",
            [$teamId, $missionId],
        );

        if ($existing) return (int)$existing['id'];

        $now    = date('Y-m-d H:i:s');
        $roomId = $this->db()->insert(
            'INSERT INTO chat_rooms (type, name, team_id, mission_id, is_active, created_at, updated_at)
             VALUES (?,?,?,?,1,?,?)',
            [self::TYPE_TEAM, $name, $teamId, $missionId, $now, $now],
        );

        return $roomId;
    }

    // ── Command room ──────────────────────────────────────────────────────────

    /** Find or create a command channel for a mission */
    public function findOrCreateCommandRoom(int $missionId, string $missionCode): int
    {
        $existing = $this->db()->selectOne(
            "SELECT id FROM chat_rooms WHERE type='command' AND mission_id=? LIMIT 1",
            [$missionId],
        );

        if ($existing) return (int)$existing['id'];

        $now    = date('Y-m-d H:i:s');
        $roomId = $this->db()->insert(
            'INSERT INTO chat_rooms (type, name, mission_id, is_active, created_at, updated_at)
             VALUES (?,?,?,1,?,?)',
            [self::TYPE_COMMAND, "CMD:{$missionCode}", $missionId, $now, $now],
        );

        return $roomId;
    }

    // ── Participants ──────────────────────────────────────────────────────────

    public function addParticipant(int $roomId, $userId, bool $isAdmin = false): void
    {
        $this->db()->execute(
            'INSERT IGNORE INTO chat_participants (room_id, user_id, is_admin, joined_at)
             VALUES (?,?,?,?)',
            [$roomId, $userId, $isAdmin ? 1 : 0, date('Y-m-d H:i:s')],
        );
    }

    public function removeParticipant(int $roomId, $userId): void
    {
        $this->db()->execute(
            'DELETE FROM chat_participants WHERE room_id=? AND user_id=?',
            [$roomId, $userId],
        );
    }

    public function syncTeamParticipants(int $roomId, array $userIds): void
    {
        $db  = $this->db();
        $now = date('Y-m-d H:i:s');

        foreach (array_unique($userIds) as $uid) {
            $db->execute(
                'INSERT OR IGNORE INTO chat_participants (room_id, user_id, is_admin, joined_at)
                 VALUES (?,?,0,?)',
                [$roomId, $uid, $now],
            );
        }
    }

    // ── Deactivate ────────────────────────────────────────────────────────────

    public function deactivate(int $roomId): void
    {
        $this->db()->execute(
            'UPDATE chat_rooms SET is_active=0, updated_at=? WHERE id=?',
            [date('Y-m-d H:i:s'), $roomId],
        );
    }

    private function db(): Connection
    {
        return Connection::getInstance();
    }
}
