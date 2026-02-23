<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiMasterController;
use Etus\Framework\Http\JsonResponse;

class NotificationApiController extends ApiMasterController
{

    public function __construct()
    {
        parent::__construct();
    }

    // ── GET /api/notifications ────────────────────────────────────────────────
    // Returns all notifications for the current user.

    public function index(): JsonResponse
    {
        $userId = ($this->authId() ?? 0);

        try {
            $notifications = $this->notif->forUser($userId, 30);
            $unreadCount   = $this->notif->unreadCount($userId);

            return JsonResponse::success('OK', [
                'notifications' => $notifications,
                'unread_count'  => $unreadCount,
            ]);
        } catch (\Throwable $e) {
            return JsonResponse::serverError($e->getMessage());
        }
    }

    // ── GET /api/notifications/poll?since=2024-01-01+12:00:00 ────────────────
    // Lightweight poll — returns only new unread notifications since a timestamp.
    // Used by the bell to check for new items without fetching everything.

    public function poll(): JsonResponse
    {
        $userId = ($this->authId() ?? 0);
        $since  = $this->request->query('since', date('Y-m-d H:i:s', strtotime('-1 minute')));

        try {
            $new         = $this->notif->since($userId, $since);
            $unreadCount = $this->notif->unreadCount($userId);

            return JsonResponse::success('OK', [
                'new'          => $new,
                'unread_count' => $unreadCount,
                'timestamp'    => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            return JsonResponse::serverError($e->getMessage());
        }
    }

    // ── POST /api/notifications/{id}/read ─────────────────────────────────────

    public function markRead(string $id): JsonResponse
    {
        $userId = ($this->authId() ?? 0);

        try {
            $this->notif->markRead((int) $id, $userId);
            $unreadCount = $this->notif->unreadCount($userId);

            return JsonResponse::success('Marked as read.', ['unread_count' => $unreadCount]);
        } catch (\Throwable $e) {
            return JsonResponse::serverError($e->getMessage());
        }
    }

    // ── POST /api/notifications/read-all ──────────────────────────────────────

    public function markAllRead(): JsonResponse
    {
        $userId = ($this->authId() ?? 0);

        try {
            $this->notif->markAllRead($userId);
            return JsonResponse::success('All marked as read.', ['unread_count' => 0]);
        } catch (\Throwable $e) {
            return JsonResponse::serverError($e->getMessage());
        }
    }
}
