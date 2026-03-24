<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\Notification;
use Etus\Framework\Auth\Gate;
use Etus\Framework\Http\JsonResponse;

class ChatApiController extends BaseController
{
    protected array $middleware = ['auth'];

    private ChatRoom    $rooms;
    private ChatMessage $messages;

    public function __construct()
    {
        $this->rooms    = new ChatRoom();
        $this->messages = new ChatMessage();
    }

    // ── POST /api/chat/{roomId}/send ──────────────────────────────────────────

    public function send(string $roomId): JsonResponse
    {
        $userId = $this->authId();
        $roomId = (int) $roomId;

        if (! $this->rooms->isParticipant($roomId, $userId)) {
            return JsonResponse::forbidden('You are not a member of this room.');
        }

        $body     = trim($this->request->input('body', ''));
        $type     = $this->request->input('type', ChatMessage::TYPE_TEXT);
        $parentId = (int) $this->request->input('parent_id', 0) ?: null;

        if (empty($body)) {
            return JsonResponse::error('Message body cannot be empty.');
        }

        if (! in_array($type, ChatMessage::TYPES, true)) {
            $type = ChatMessage::TYPE_TEXT;
        }

        // Only leads/commanders can send broadcast or order messages
        if (in_array($type, [ChatMessage::TYPE_BROADCAST, ChatMessage::TYPE_ORDER], true)) {
            $room = $this->rooms->findWithParticipants($roomId);
            $isLeadInRoom = false;
            foreach ($room['participants'] as $p) {
                if ($p['user_id'] === $userId && $p['is_room_admin']) {
                    $isLeadInRoom = true;
                    break;
                }
            }
            if (! $isLeadInRoom && ! Gate::hasAnyRole(['super_admin', 'admin', 'manager'])) {
                return JsonResponse::forbidden('Only room leads/commanders can send orders or broadcasts.');
            }
        }

        try {
            $msg = $this->messages->send($roomId, $userId, $body, $type, $parentId);
        } catch (\Throwable $e) {
            return JsonResponse::serverError($e->getMessage());
        }

        // Notify other participants
        $this->notifyParticipants($roomId, $userId, $body, (int)$msg['id']);

        return JsonResponse::success('Sent.', ['message' => $this->formatMsg($msg)]);
    }

    // ── GET /api/chat/{roomId}/poll?after={id} ────────────────────────────────

    public function poll(string $roomId): JsonResponse
    {
        $userId  = $this->authId();
        $roomId  = (int) $roomId;
        $afterId = (int) $this->request->query('after', '0');

        if (! $this->rooms->isParticipant($roomId, $userId)) {
            return JsonResponse::forbidden();
        }

        $msgs = $this->messages->poll($roomId, $afterId);

        // Mark polled messages as read
        foreach ($msgs as $msg) {
            $this->messages->markRead((int)$msg['id'], $userId);
        }

        return JsonResponse::success('OK', [
            'messages'     => array_map([$this, 'formatMsg'], $msgs),
            'unread_count' => $this->messages->unreadCount($roomId, $userId),
        ]);
    }

    // ── POST /api/chat/{roomId}/read ──────────────────────────────────────────

    public function markRead(string $roomId): JsonResponse
    {
        $userId = $this->authId();
        $roomId = (int) $roomId;

        if (! $this->rooms->isParticipant($roomId, $userId)) {
            return JsonResponse::forbidden();
        }

        $this->messages->markRoomRead($roomId, $userId);

        return JsonResponse::success('Marked as read.', ['unread_count' => 0]);
    }

    // ── POST /api/chat/messages/{id}/ack ─────────────────────────────────────

    public function acknowledge(string $id): JsonResponse
    {
        $userId = $this->authId();

        try {
            $this->messages->acknowledge((int) $id, $userId);
            return JsonResponse::success('Acknowledged.');
        } catch (\Throwable $e) {
            return JsonResponse::serverError($e->getMessage());
        }
    }

    // ── POST /api/chat/messages/{id}/delete ───────────────────────────────────

    public function delete(string $id): JsonResponse
    {
        $userId = $this->authId();

        $deleted = $this->messages->softDelete((int) $id, $userId);

        if (! $deleted) {
            return JsonResponse::error('Cannot delete this message.', 403);
        }

        return JsonResponse::success('Message deleted.');
    }

    // ── GET /api/chat/rooms ───────────────────────────────────────────────────

    public function roomList(): JsonResponse
    {
        $userId = $this->authId();
        $rooms  = $this->rooms->forUser($userId);

        return JsonResponse::success('OK', ['rooms' => $rooms]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Format a message row for JSON response (strip the raw blob) */
    private function formatMsg(array $msg): array
    {
        unset($msg['body_enc']);          // Never send raw encrypted blob to client
        $msg['sender_avatar'] = strtoupper(substr($msg['sender_name'] ?? '?', 0, 1));
        $msg['is_order']      = $msg['msg_type'] === ChatMessage::TYPE_ORDER;
        $msg['is_broadcast']  = $msg['msg_type'] === ChatMessage::TYPE_BROADCAST;
        $msg['time_ago']      = $this->timeAgo($msg['created_at'] ?? '');
        return $msg;
    }

    private function timeAgo(string $dt): string
    {
        $diff = time() - strtotime($dt);
        if ($diff < 60)    return 'just now';
        if ($diff < 3600)  return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        return date('M j', strtotime($dt));
    }

    private function notifyParticipants(int $roomId, $senderId, string $body, int $msgId): void
    {
        $participants = $this->rooms->participantIds($roomId);
        $targets      = array_filter($participants, fn($uid) => $uid !== $senderId);
        
        if (empty($targets)) return;

        $senderName = $_SESSION['user_name'] ?? 'Someone';
        $preview    = mb_substr($body, 0, 60);

        Notification::send(
            userIds: array_values($targets),
            title: "{$senderName} sent a message",
            body: $preview,
            url: "/chat/{$roomId}",
            type: Notification::TYPE_GENERAL,
            senderId: $senderId,
            relatedId: $msgId,
            relatedType: 'chat_message',
        );
    }
}
