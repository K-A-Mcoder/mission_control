<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\Notification;
use Etus\Framework\Auth\Gate;
use Etus\Framework\Http\Flash;
use Etus\Framework\Http\Response;
use Etus\Framework\Database\Connection;

class ChatController extends BaseController
{
    protected array $middleware = ['auth'];

    private ChatRoom    $rooms;
    private ChatMessage $messages;

    public function __construct()
    {
        $this->rooms    = new ChatRoom();
        $this->messages = new ChatMessage();
    }

    // ── GET /chat ─────────────────────────────────────────────────────────────

    public function index(): Response
    {
        $userId = $this->authId();
        $this->ensureTeamRooms($userId);
        $rooms = $this->rooms->forUser($userId);

        foreach ($rooms as &$room) {
            $room['unread'] = $this->messages->unreadCount((int)$room['id'], $userId);
        }
        unset($room);

        return view('chat/Index', array_merge($this->sharedData($userId), [
            'title'       => 'Mission Chat',
            'rooms'       => $rooms,
            'activeRoom'  => null,
            'messages'    => [],
            'pendingAcks' => [],
            'isLead'      => false,
        ]));
    }

    // ── GET /chat/{roomId} ────────────────────────────────────────────────────

    public function room(string $roomId): Response
    {
        $userId = $this->authId();
        $roomId = (int) $roomId;

        if (! $this->rooms->isParticipant($roomId, $userId)) {
            Flash::error('You are not a member of this chat room.');
            return redirect('/chat');
        }

        $this->ensureTeamRooms($userId);
        $rooms      = $this->rooms->forUser($userId);
        $activeRoom = $this->rooms->findWithParticipants($roomId);

        foreach ($rooms as &$room) {
            $room['unread'] = $this->messages->unreadCount((int)$room['id'], $userId);
        }
        unset($room);

        $history = $this->messages->forRoom($roomId, 60);
        $this->messages->markRoomRead($roomId, $userId);

        $pendingAcks = $this->messages->pendingAcks($roomId, $userId);

        return view('chat/room', array_merge($this->sharedData($userId), [
            'title'       => 'Chat: ' . htmlspecialchars($activeRoom['name'] ?? 'Room'),
            'rooms'       => $rooms,
            'activeRoom'  => $activeRoom,
            'messages'    => $history,
            'pendingAcks' => $pendingAcks,
            'isLead'      => $this->isTeamLead($userId, $activeRoom['team_id'] ?? null),
        ]));
    }

    // ── POST /chat/direct ─────────────────────────────────────────────────────

    public function startDirect(): Response
    {
        $userId   = $this->authId();
        $targetId = (int) $this->request->input('user_id', 0);

        if (! $targetId || $targetId === $userId) {
            Flash::error('Invalid user.');
            return redirect('/chat');
        }

        $roomId = $this->rooms->createDirect($userId, $targetId);

        return redirect("/chat/{$roomId}");
    }

    // ── POST /chat/command-room ───────────────────────────────────────────────

    public function openCommandRoom(): Response
    {
        if (! Gate::hasAnyRole(['super_admin', 'admin', 'manager'])) {
            Flash::error('Only commanders can open command channels.');
            return redirect('/chat');
        }

        $missionId   = (int) $this->request->input('mission_id', 0);
        $missionCode = trim($this->request->input('mission_code', 'MSN'));

        if (! $missionId) {
            Flash::error('Select a mission.');
            return redirect('/chat');
        }

        $roomId = $this->rooms->findOrCreateCommandRoom($missionId, $missionCode);
        $this->rooms->addParticipant($roomId, $this->authId(), true);

        return redirect("/chat/{$roomId}");
    }

    // ── Shared view data ──────────────────────────────────────────────────────

    private function sharedData($userId): array
    {
        $db          = Connection::getInstance();
        $isCommander = Gate::hasAnyRole(['super_admin', 'admin', 'manager']);

        // All active users for DM picker
        $allUsers = $db->select(
            'SELECT u.user_id, u.full_name, r.role_name
             FROM users u JOIN roles r ON r.id=u.role_id
             WHERE u.status=\'active\' AND u.user_id != ?
             ORDER BY u.full_name ASC',
            [$userId],
        );

        // Missions for command channel picker (commanders only)
        $missions = [];
        if ($isCommander) {
            $missions = $db->select(
                'SELECT id, m_code, title FROM missions WHERE deleted_at IS NULL ORDER BY title ASC',
            );
        }

        return [
            'userId'      => $userId,
            'allUsers'    => $allUsers,
            'missions'    => $missions,
            'isCommander' => $isCommander,
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function ensureTeamRooms($userId): void
    {
        $db    = Connection::getInstance();
        $teams = $db->select(
            'SELECT t.id, t.name, t.lead_id, tma.mission_id
             FROM teams t
             JOIN team_membership tm ON tm.team_id=t.id
             JOIN mission_team_assignments tma ON tma.team_id=t.id
             WHERE tm.user_id=? AND t.deleted_at IS NULL',
            [$userId],
        );

        foreach ($teams as $team) {
            $roomId = $this->rooms->findOrCreateTeamRoom(
                (int) $team['id'],
                $team['mission_id'] ? (int) $team['mission_id'] : null,
                '# ' . $team['name'],
            );

            $memberIds = array_column(
                $db->select('SELECT user_id FROM team_membership WHERE team_id=?', [(int)$team['id']]),
                'user_id',
            );
            $this->rooms->syncTeamParticipants($roomId, $memberIds);

            if ($team['lead_id']) {
                $this->rooms->addParticipant($roomId, (int) $team['lead_id'], true);
            }
        }
    }

    private function isTeamLead($userId, ?int $teamId): bool
    {
        if (! $teamId) return false;
        $row = Connection::getInstance()->selectOne(
            'SELECT id FROM teams WHERE id=? AND lead_id=? AND deleted_at IS NULL',
            [$teamId, $userId],
        );
        return $row !== null;
    }

    
}