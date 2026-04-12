<?php

namespace App\Controllers\Api;

use App\Models\Task;
use App\Controllers\Api\ApiMasterController;
use Etus\Framework\Auth\Gate;
use Etus\Framework\Http\JsonResponse;

class TaskApiController extends ApiMasterController
{
    private Task $task;

    public function __construct()
    {
        $this->task = new Task();
    }

    // ── GET /api/tasks ────────────────────────────────────────────────────────
    // Query params: search, status[], priority[], team_id, assigned_to,
    //               mission_id, due_from, due_to

    public function index(): JsonResponse
    {
        $userId = ($this->authId() ?? 0);
        $role   = $_SESSION['role'] ?? '';

        $filters = $this->extractFilters();

        // Non-admins/managers can only see their own visible tasks
        if (! Gate::hasAnyRole(['admin', 'manager', 'super_admin'])) {
            $filters['scope_user'] = $userId;
        }

        try {
            $tasks = $this->task->filter($filters);
            return JsonResponse::success('OK', ['data' => $tasks, 'total' => count($tasks)]);
        } catch (\Throwable $e) {
            return JsonResponse::serverError($e->getMessage());
        }
    }

    // ── GET /api/tasks/kanban ─────────────────────────────────────────────────
    // Returns tasks grouped by status — exactly what the kanban board needs.

    public function kanban(): JsonResponse
    {
        $userId  = ($this->authId() ?? 0);
        $filters = $this->extractFilters();

        if (! Gate::hasAnyRole(['admin', 'manager'])) {
            $filters['scope_user'] = $userId;
        }

        try {
            $all     = $this->task->filter($filters);
            $columns = array_fill_keys(Task::STATUSES, []);

            foreach ($all as $task) {
                $columns[$task['status']][] = $task;
            }

            return JsonResponse::success('OK', ['columns' => $columns]);
        } catch (\Throwable $e) {
            return JsonResponse::serverError($e->getMessage());
        }
    }

    // ── PATCH /api/tasks/{id}/status ──────────────────────────────────────────
    // Body: { status: "in_progress" }
    // Used by the kanban drag-and-drop to update a single card's status.

    public function updateStatus(string $id, $status): JsonResponse
    {
        $task = $this->task->findActive((int) $id);

        if (! $task) {
            return JsonResponse::notFound('Task not found.');
        }

        if (! $this->canAccessTask($task)) {
            return JsonResponse::forbidden();
        }

        // $status = trim($this->request->input('status', ''));
        // $this->request->input('status');
        // var_dump($this->request->);
        // exit;
        if (! in_array($status, Task::STATUSES, true)) {
            return JsonResponse::error("Invalid status. Allowed: " . implode(', ', Task::STATUSES));
        }

        try {
            $this->task->transition((int) $id, $status);
            $updated = $this->task->findActive((int) $id);
            return JsonResponse::success('Task updated.', ['task' => $updated]);
        } catch (\Throwable $e) {
            return JsonResponse::serverError($e->getMessage());
        }
    }

    // ── POST /api/tasks/bulk-status ───────────────────────────────────────────
    // Body: { ids: [1, 2, 3], status: "closed" }

    public function bulkStatus(): JsonResponse
    {
        if (! Gate::hasAnyRole(['admin', 'manager', 'super_admin'])) {
            return JsonResponse::forbidden('Only admins and managers can bulk-update tasks.');
        }

        $ids    = $this->request->input('ids', []);
        $status = trim($this->request->input('status', ''));

        if (empty($ids) || ! is_array($ids)) {
            return JsonResponse::error('ids[] is required.');
        }

        if (! in_array($status, Task::STATUSES, true)) {
            return JsonResponse::error("Invalid status. Allowed: " . implode(', ', Task::STATUSES));
        }

        $ids = array_map('intval', $ids);

        try {
            $affected = $this->task->bulkUpdateStatus($ids, $status);
            return JsonResponse::success(
                "{$affected} task(s) updated to '{$status}'.",
                ['affected' => $affected],
            );
        } catch (\Throwable $e) {
            return JsonResponse::serverError($e->getMessage());
        }
    }

    // ── POST /api/tasks/bulk-delete ───────────────────────────────────────────
    // Body: { ids: [1, 2, 3] }

    public function bulkDelete(): JsonResponse
    {
        if (! Gate::hasAnyRole(['admin', 'super_admin'])) {
            return JsonResponse::forbidden('Only admins can bulk-delete tasks.');
        }

        $ids    = $this->request->input('ids', []);
        $userId = ($this->authId() ?? 0);

        if (empty($ids) || ! is_array($ids)) {
            return JsonResponse::error('ids[] is required.');
        }

        $ids = array_map('intval', $ids);

        try {
            $affected = $this->task->bulkDelete($ids, $userId);
            return JsonResponse::success("{$affected} task(s) deleted.", ['affected' => $affected]);
        } catch (\Throwable $e) {
            return JsonResponse::serverError($e->getMessage());
        }
    }

    // ── GET /api/tasks/stats ──────────────────────────────────────────────────

    public function stats(): JsonResponse
    {
        $userId = ($this->authId() ?? 0);

        $scopeUserId = Gate::hasAnyRole(['admin', 'manager', 'super_admin']) ? null : $userId;

        try {
            return JsonResponse::success('OK', ['stats' => $this->task->stats($scopeUserId)]);
        } catch (\Throwable $e) {
            return JsonResponse::serverError($e->getMessage());
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function extractFilters(): array
    {
        return array_filter([
            'search'      => $this->request->input('search'),
            'status'      => $this->request->input('status'),
            'priority'    => $this->request->input('priority'),
            'team_id'     => $this->request->input('team_id'),
            'assigned_to' => $this->request->input('assigned_to'),
            'mission_id'  => $this->request->input('mission_id'),
            'due_from'    => $this->request->input('due_from'),
            'due_to'      => $this->request->input('due_to'),
        ], fn($v) => $v !== null && $v !== '');
    }

    private function canAccessTask(array $task): bool
    {
        if (Gate::hasAnyRole(['admin', 'manager', 'super_admin'])) return true;

        $userId = ($this->authId() ?? 0);

        return (int) $task['assigned_to'] === $userId
            || ($task['team_id'] && (new \App\Models\Team)->hasMember((int) $task['team_id'], $userId));
    }
}
