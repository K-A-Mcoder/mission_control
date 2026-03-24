<?php

namespace App\Models;

use Etus\Framework\Database\Model;
use Etus\Framework\Database\Connection;

/**
 * ChatMessage — encrypted chat messages.
 *
 * Encryption backbone:
 *   Algorithm : AES-256-GCM  (authenticated encryption — detects tampering)
 *   Key       : 32-byte key derived from APP_CHAT_KEY env var via SHA-256
 *   IV        : 12-byte random nonce, generated fresh per message
 *   Tag       : 16-byte authentication tag produced by GCM
 *   Storage   : base64_encode( iv[12] . tag[16] . ciphertext )
 *
 * Decryption fails (returns '[encrypted]') if:
 *   - Key is wrong
 *   - Ciphertext was tampered with
 *   - The stored blob is malformed
 *
 * The plaintext never touches the database.
 */
class ChatMessage extends Model
{
    public const TYPE_TEXT      = 'text';
    public const TYPE_SYSTEM    = 'system';    // auto-generated (e.g. "User joined")
    public const TYPE_BROADCAST = 'broadcast'; // lead announcement to whole team
    public const TYPE_ORDER     = 'order';     // command order requiring acknowledgement

    public const TYPES = [self::TYPE_TEXT, self::TYPE_SYSTEM, self::TYPE_BROADCAST, self::TYPE_ORDER];

    public const CIPHER    = 'aes-256-gcm';
    public const IV_LEN    = 12;
    public const TAG_LEN   = 16;
    public const PREVIEW_LEN = 72;  // chars stored in body_preview

    protected string $table      = 'chat_messages';
    protected string $primaryKey = 'id';
    protected bool   $timestamps  = false;
    protected bool   $auditFields = false;
    protected bool   $logging     = false;

    // ── Encryption / Decryption ───────────────────────────────────────────────

    /**
     * Encrypt a plaintext message body.
     * Returns base64-encoded  iv(12) + tag(16) + ciphertext.
     */
    public static function encrypt(string $plaintext): string
    {
        $key = self::derivedKey();
        $iv  = random_bytes(self::IV_LEN);
        $tag = '';

        $cipher = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LEN,
        );

        if ($cipher === false) {
            throw new \RuntimeException('Chat message encryption failed.');
        }

        return base64_encode($iv . $tag . $cipher);
    }

    /**
     * Decrypt an encrypted blob back to plaintext.
     * Returns '[Message unavailable]' on any failure — never throws to the UI.
     */
    public static function decrypt(string $blob): string
    {
        try {
            $raw = base64_decode($blob, strict: true);
            if ($raw === false || strlen($raw) < self::IV_LEN + self::TAG_LEN + 1) {
                return '[Message unavailable]';
            }

            $iv         = substr($raw, 0, self::IV_LEN);
            $tag        = substr($raw, self::IV_LEN, self::TAG_LEN);
            $ciphertext = substr($raw, self::IV_LEN + self::TAG_LEN);
            $key        = self::derivedKey();

            $plain = openssl_decrypt(
                $ciphertext,
                self::CIPHER,
                $key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag,
            );

            return $plain === false ? '[Message unavailable]' : $plain;
        } catch (\Throwable) {
            return '[Message unavailable]';
        }
    }

    /**
     * Derive a stable 32-byte key from the APP_CHAT_KEY environment variable.
     */
    private static function derivedKey(): string
    {
        $raw = $_ENV['APP_CHAT_KEY'] ?? getenv('APP_CHAT_KEY') ?? '';

        if (empty($raw)) {
            throw new \RuntimeException(
                'APP_CHAT_KEY is not set. Add a 32+ char random string to your .env file.'
            );
        }

        // hash_hmac produces a 32-byte binary string for AES-256
        return hash_hmac('sha256', $raw, 'etus-chat-v1', binary: true);
    }

    // ── Write ─────────────────────────────────────────────────────────────────

    /**
     * Store a new encrypted message.
     * Returns the new message row (with decrypted body injected).
     *
     * @return array<string, mixed>
     */
    public function send(
        int    $roomId,
        string    $senderId,
        string $plaintext,
        string $type     = self::TYPE_TEXT,
        ?int   $parentId = null,
    ): array {
        $enc     = self::encrypt($plaintext);
        $preview = mb_substr(strip_tags($plaintext), 0, self::PREVIEW_LEN);
        $now     = date('Y-m-d H:i:s');

        $id = $this->db()->insert(
            'INSERT INTO chat_messages
                (room_id, sender_id, msg_type, body_enc, body_preview, parent_id, is_deleted, created_at)
             VALUES (?,?,?,?,?,?,0,?)',
            [$roomId, $senderId, $type, $enc, $preview, $parentId, $now],
        );

        // Mark as read by sender immediately
        $this->markRead($id, $senderId);

        return array_merge(
            $this->findWithSender($id),
            ['body' => $plaintext],
        );
    }

    /**
     * Insert a system-generated message (no encryption needed for these,
     * but we store them consistently).
     */
    public function system(int $roomId, string $text): int
    {
        $now = date('Y-m-d H:i:s');
        return $this->db()->insert(
            'INSERT INTO chat_messages
                (room_id, sender_id, msg_type, body_enc, body_preview, is_deleted, created_at)
             VALUES (?,0,?,?,?,0,?)',
            [$roomId, self::TYPE_SYSTEM, self::encrypt($text), mb_substr($text, 0, self::PREVIEW_LEN), $now],
        );
    }

    // ── Read ──────────────────────────────────────────────────────────────────

    /**
     * Paginated message history for a room (newest last, client-side ordering).
     * Decrypts each message body before returning.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forRoom(int $roomId, int $limit = 60, int $beforeId = 0): array
    {
        $params = [$roomId];
        $before = '';

        if ($beforeId > 0) {
            $before    = ' AND cm.id < ?';
            $params[] = $beforeId;
        }

        $rows = $this->db()->select(
            "SELECT cm.*,
            u.full_name  AS sender_name,
            r.role_name  AS sender_role,
            p.full_name  AS parent_sender_name
     FROM   chat_messages cm
     LEFT JOIN users u  ON u.user_id = cm.sender_id
     LEFT JOIN roles r  ON r.id      = u.role_id
     LEFT JOIN chat_messages pm ON pm.id = cm.parent_id
     LEFT JOIN users p  ON p.user_id = pm.sender_id
     WHERE  cm.room_id = ? AND cm.is_deleted = 0 {$before}
     ORDER  BY cm.id DESC
     LIMIT  $limit",
            [...$params],
        );

        // Decrypt and reverse (oldest first)
        foreach ($rows as &$row) {
            $row['body'] = self::decrypt($row['body_enc']);
        }
        unset($row);

        return array_reverse($rows);
    }

    /**
     * Poll for messages newer than $afterId — used for real-time updates.
     *
     * @return array<int, array<string, mixed>>
     */
    public function poll(int $roomId, int $afterId): array
    {
        $rows = $this->db()->select(
            'SELECT cm.*,
                    u.full_name AS sender_name,
                    r.role_name AS sender_role
             FROM   chat_messages cm
             LEFT JOIN users u ON u.user_id = cm.sender_id
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE  cm.room_id=? AND cm.id>? AND cm.is_deleted=0
             ORDER  BY cm.id ASC',
            [$roomId, $afterId],
        );

        foreach ($rows as &$row) {
            $row['body'] = self::decrypt($row['body_enc']);
        }
        unset($row);

        return $rows;
    }

    public function findWithSender(int $id): array
    {
        return $this->db()->selectOne(
            'SELECT cm.*, u.full_name AS sender_name, r.role_name AS sender_role
             FROM   chat_messages cm
             LEFT JOIN users u ON u.user_id = cm.sender_id
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE  cm.id=?',
            [$id],
        ) ?? [];
    }

    /** Pending acknowledgement messages for a user in a room */
    public function pendingAcks(int $roomId, $userId): array
    {
        $rows = $this->db()->select(
            "SELECT cm.*, u.full_name AS sender_name
             FROM   chat_messages cm
             LEFT JOIN users u ON u.user_id = cm.sender_id
             WHERE  cm.room_id=? AND cm.msg_type='order' AND cm.is_deleted=0
             AND    cm.id NOT IN (
                 SELECT message_id FROM chat_message_acks WHERE user_id=?
             )
             ORDER BY cm.id ASC",
            [$roomId, $userId],
        );

        foreach ($rows as &$row) {
            $row['body'] = self::decrypt($row['body_enc']);
        }
        unset($row);

        return $rows;
    }

    // ── Read receipts ─────────────────────────────────────────────────────────

    public function markRead(int $messageId, $userId): void
    {
        $this->db()->execute(
            'INSERT IGNORE INTO chat_message_reads (message_id, user_id, read_at) VALUES (?,?,?)',
            [$messageId, $userId, date('Y-m-d H:i:s')],
        );
    }

    public function markRoomRead(int $roomId, $userId): void
    {
        $this->db()->execute(
            'INSERT IGNORE INTO chat_message_reads (message_id, user_id, read_at)
             SELECT id, ?, ? FROM chat_messages WHERE room_id=? AND is_deleted=0',
            [$userId, date('Y-m-d H:i:s'), $roomId],
        );
    }

    // ── Acknowledgement ───────────────────────────────────────────────────────

    public function acknowledge(int $messageId, $userId): void
    {
        $this->db()->execute(
            'INSERT IGNORE INTO chat_message_acks (message_id, user_id, acked_at) VALUES (?,?,?)',
            [$messageId, $userId, date('Y-m-d H:i:s')],
        );
        $this->markRead($messageId, $userId);
    }

    public function ackStatus(int $messageId): array
    {
        return $this->db()->select(
            'SELECT ca.*, u.full_name FROM chat_message_acks ca
             JOIN users u ON u.user_id=ca.user_id WHERE ca.message_id=?',
            [$messageId],
        );
    }

    // ── Edit / Delete ─────────────────────────────────────────────────────────

    public function editMessage(int $id, int $senderId, string $newPlaintext): bool
    {
        $msg = $this->db()->selectOne(
            'SELECT sender_id, msg_type FROM chat_messages WHERE id=?',
            [$id],
        );

        if (!$msg || (int)$msg['sender_id'] !== $senderId || $msg['msg_type'] === self::TYPE_SYSTEM) {
            return false;
        }

        $this->db()->execute(
            'UPDATE chat_messages SET body_enc=?, body_preview=?, edited_at=? WHERE id=?',
            [
                self::encrypt($newPlaintext),
                mb_substr($newPlaintext, 0, self::PREVIEW_LEN),
                date('Y-m-d H:i:s'),
                $id,
            ],
        );

        return true;
    }

    public function softDelete(int $id, int $actorId): bool
    {
        $msg = $this->db()->selectOne(
            'SELECT sender_id FROM chat_messages WHERE id=?',
            [$id],
        );
        if (!$msg) return false;

        $this->db()->execute(
            'UPDATE chat_messages SET is_deleted=1, body_enc=?, body_preview=? WHERE id=?',
            [self::encrypt('[Message deleted]'), '[Message deleted]', $id],
        );

        return true;
    }

    // ── Stats ─────────────────────────────────────────────────────────────────

    public function unreadCount(int $roomId, $userId): int
    {
        $row = $this->db()->selectOne(
            'SELECT COUNT(*) AS n FROM chat_messages
             WHERE room_id=? AND sender_id!=? AND is_deleted=0
             AND id NOT IN (SELECT message_id FROM chat_message_reads WHERE user_id=?)',
            [$roomId, $userId, $userId],
        );
        return (int)($row['n'] ?? 0);
    }

    private function db(): Connection
    {
        return Connection::getInstance();
    }
}
