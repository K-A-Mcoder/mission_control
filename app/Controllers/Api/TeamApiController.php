<?php

namespace App\Controllers\Api;

use App\Models\Team;
use App\Controllers\BaseController;
use Etus\Framework\Auth\Gate;
use Etus\Framework\Http\JsonResponse;

class TeamApiController extends BaseController
{
    private Team $team;

    public function __construct()
    {
        $this->team = new Team();
    }

    // ── GET /api/teams ────────────────────────────────────────────────────────

    public function index(): JsonResponse
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);

        try {
            $teams = Gate::hasAnyRole(['admin', 'manager', 'super_admin'])
                ? $this->team->allActive()
                : $this->team->forUser($userId);

            return JsonResponse::success('OK', ['data' => $teams, 'total' => count($teams)]);
        } catch (\Throwable $e) {
            return JsonResponse::serverError($e->getMessage());
        }
    }

    // ── GET /api/teams/{id} ───────────────────────────────────────────────────

    public function show(string $id): JsonResponse
    {
        $team = $this->team->findActive((int) $id);

        if (! $team) return JsonResponse::notFound('Team not found.');

        $userId = (int) ($_SESSION['user_id'] ?? 0);

        if (
            ! Gate::hasAnyRole(['admin', 'manager', 'super_admin'])
            && ! $this->team->hasMember((int) $id, $userId)
        ) {
            return JsonResponse::forbidden();
        }

        try {
            return JsonResponse::success('OK', [
                'data'      => $team,
                'members'   => $this->team->members((int) $id),
                'tasks'     => $this->team->tasks((int) $id),
                'taskStats' => $this->team->taskStats((int) $id),
                'missions'  => $this->team->missions((int) $id),
            ]);
        } catch (\Throwable $e) {
            return JsonResponse::serverError($e->getMessage());
        }
    }

    // ── GET /api/teams/stats ──────────────────────────────────────────────────

    public function stats(): JsonResponse
    {
        $userId      = (int) ($_SESSION['user_id'] ?? 0);
        $scopeUserId = Gate::hasAnyRole(['admin', 'manager', 'super_admin']) ? null : $userId;

        try {
            return JsonResponse::success('OK', ['stats' => $this->team->stats($scopeUserId)]);
        } catch (\Throwable $e) {
            return JsonResponse::serverError($e->getMessage());
        }
    }

    // ── POST /api/teams/{id}/members ──────────────────────────────────────────

    public function addMember(string $id): JsonResponse
    {
        if (! Gate::hasAnyRole(['admin', 'manager', 'super_admin'])) {
            return JsonResponse::forbidden();
        }

        $userId  = (int) ($_SESSION['user_id'] ?? 0);
        $memberId = (int) $this->request->input('user_id', 0);
        $role     = $this->request->input('role', 'member');

        if (! $memberId) return JsonResponse::error('user_id is required.');
        if (! in_array($role, ['member', 'lead'], true)) $role = 'member';

        try {
            $this->team->addMember((int) $id, $memberId, $userId, $role);
            return JsonResponse::success('Member added.');
        } catch (\Throwable $e) {
            return JsonResponse::serverError($e->getMessage());
        }
    }

    // ── DELETE /api/teams/{id}/members/{userId} ───────────────────────────────

    public function removeMember(string $id, string $memberId): JsonResponse
    {
        if (! Gate::hasAnyRole(['admin', 'manager', 'super_admin'])) {
            return JsonResponse::forbidden();
        }

        try {
            $this->team->removeMember((int) $id, (int) $memberId);
            return JsonResponse::success('Member removed.');
        } catch (\Throwable $e) {
            return JsonResponse::serverError($e->getMessage());
        }
    }
}
