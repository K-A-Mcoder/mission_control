<?php

namespace App\Controllers\Api;

use App\Models\Mission;
use App\Controllers\BaseController;
use Etus\Framework\Auth\Gate;
use Etus\Framework\Http\JsonResponse;
use Etus\Framework\Http\Response;
use Etus\Framework\Database\Connection;

class MissionApiController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    // ── GET /missions ─────────────────────────────────────────────────────────

    /**
     * Return all missions visible to the current user, based on their role.
     * Admin → all | Manager → non-SECRET | User → team-linked only.
     */
    public function index(): JsonResponse
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $role   = $_SESSION['role'] ?? '';

        try {
            $missions = match (true) {
                $role === 'admin'   => $this->mission->allWithTaskCounts(),
                $role === 'manager' => $this->mission->nonSecretWithTaskCounts(),
                default             => $this->mission->forUser($userId),
            };

            return JsonResponse::success('OK', ['data' => $missions]);
        } catch (\Throwable $e) {
            return JsonResponse::serverError($e->getMessage());
        }
    }

    // ── POST /missions ────────────────────────────────────────────────────────

    /**
     * Create a new mission.
     * Requires role: admin or manager.
     */
    public function store(): JsonResponse
    {
        if (! Gate::hasAnyRole(['admin', 'manager'])) {
            return JsonResponse::forbidden();
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);

        $codeName       = $this->request->input('code_name');
        $title          = $this->request->input('title');
        $description    = $this->request->input('description');
        $startTime      = $this->request->input('start_time');
        $endTime        = $this->request->input('end_time');
        $classification = $this->request->input('classification', 'PUBLIC');

        if (! $codeName || ! $title) {
            return JsonResponse::error('code_name and title are required.');
        }

        try {
            $id = $this->mission->create([
                'm_code'         => $codeName,
                'title'          => $title,
                'description'    => $description,
                'start_time'     => $startTime,
                'end_time'       => $endTime,
                'classification' => $classification,
                'created_by'     => $userId,
            ]);

            return JsonResponse::success('Mission created successfully.', ['id' => $id], 201);
        } catch (\Throwable $e) {
            return JsonResponse::serverError('Something went wrong.');
        }
    }

    // ── POST /missions/delete ─────────────────────────────────────────────────

    /**
     * Soft delete one or many missions.
     * Requires role: admin.
     * Send ?  id=1          for single delete.
     * Send ?  ids[]=1&ids[]=2  for bulk delete.
     */
    public function destroy(): JsonResponse
    {
        if (! Gate::hasRole('admin')) {
            return JsonResponse::forbidden('Only admins can delete missions.');
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $ip     = $this->request->server('REMOTE_ADDR', 'UNKNOWN');
        $db     = Connection::getInstance();

        // ── Bulk delete ───────────────────────────────────────────────────────
        $ids = $this->request->input('ids');

        if (! empty($ids) && is_array($ids)) {
            $ids = array_map('intval', $ids);

            try {
                $db->execute('BEGIN');

                $deleted = $this->mission->softDeleteMany($ids, $userId);

                foreach ($ids as $missionId) {
                    $this->logAudit($db, $userId, 'soft_delete_mission_bulk', $ip, ['mission_id' => $missionId]);
                }

                $db->execute('COMMIT');

                return JsonResponse::success(
                    'Selected missions deleted successfully.',
                    ['deleted_count' => $deleted],
                );
            } catch (\Throwable $e) {
                $db->execute('ROLLBACK');
                return JsonResponse::serverError($e->getMessage());
            }
        }

        // ── Single delete ─────────────────────────────────────────────────────
        $id = (int) $this->request->input('id', 0);

        if (! $id) {
            return JsonResponse::error('Mission ID required.', 400);
        }

        $exists = $this->mission->findActive($id);

        if (! $exists) {
            return JsonResponse::notFound('Mission not found or already deleted.');
        }

        try {
            $db->execute('BEGIN');

            $this->mission->softDelete($id, $userId);
            $this->logAudit($db, $userId, 'soft_delete_mission', $ip, ['mission_id' => $id]);

            $db->execute('COMMIT');

            return JsonResponse::success('Mission deleted successfully.');
        } catch (\Throwable $e) {
            $db->execute('ROLLBACK');
            return JsonResponse::serverError($e->getMessage());
        }
    }

    // ── POST /missions/assign-team ────────────────────────────────────────────

    /**
     * Assign a team to a mission and notify all team members.
     * Requires role: admin or manager.
     */
    public function assignTeam(): JsonResponse
    {
        if (! Gate::hasAnyRole(['admin', 'manager'])) {
            return JsonResponse::forbidden();
        }

        $userId    = (int) ($_SESSION['user_id'] ?? 0);
        $missionId = (int) $this->request->input('mission_id', 0);
        $teamId    = (int) $this->request->input('team_id', 0);

        if (! $missionId || ! $teamId) {
            return JsonResponse::error('mission_id and team_id are required.', 400);
        }

        try {
            $this->mission->assignTeam($missionId, $teamId, $userId);

            // Notify all members of the assigned team
            $memberIds = $this->mission->teamMemberIds($teamId);

            if (! empty($memberIds)) {
                $this->notifyUsers(
                    userIds: $memberIds,
                    senderId: $userId,
                    title: 'Team assigned to mission',
                    body: "Your team was assigned to a mission (ID: {$missionId})",
                    url: "/missions/{$missionId}",
                );
            }

            return JsonResponse::success('Team assigned to mission successfully.');
        } catch (\Throwable $e) {
            return JsonResponse::serverError($e->getMessage());
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Write an audit log entry directly.
     *
     * @param array<string, mixed> $meta
     */
    private function logAudit(
        Connection $db,
        int $userId,
        string $action,
        string $ip,
        array $meta = [],
    ): void {
        $db->execute(
            'INSERT INTO activity_logs (action, model, record_id, payload, user_id, created_at)
             VALUES (?, ?, ?, ?, ?, ?)',
            [
                $action,
                Mission::class,
                $meta['mission_id'] ?? null,
                json_encode(array_merge($meta, ['ip' => $ip])),
                $userId,
                date('Y-m-d H:i:s'),
            ],
        );
    }

    /**
     * Insert in-app notifications for a list of user IDs.
     *
     * @param array<int, int> $userIds
     */
    private function notifyUsers(
        array $userIds,
        int $senderId,
        string $title,
        string $body,
        string $url,
    ): void {
        $db  = Connection::getInstance();
        $now = date('Y-m-d H:i:s');

        foreach ($userIds as $uid) {
            $db->execute(
                'INSERT INTO notifications (user_id, sender_id, title, body, url, is_read, created_at)
                 VALUES (?, ?, ?, ?, ?, 0, ?)',
                [$uid, $senderId, $title, $body, $url, $now],
            );
        }
    }
}
