<?php

namespace App\Controllers;

use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Controllers\BaseController;
use Etus\Framework\Auth\Gate;
use Etus\Framework\Http\Flash;
use Etus\Framework\Http\Response;
use Etus\Framework\Database\Connection;

class TaskController extends BaseController
{
    protected array $middleware = ['auth'];

    private Task $task;

    public function __construct()
    {
        $this->task = new Task();
    }

    // ── GET /teams/{teamId}/tasks/create ──────────────────────────────────────

    public function create(string $teamId): Response
    {
        $team = $this->resolveTeam((int) $teamId);

        if ($team instanceof Response) {
            return $team;
        }

        if (! Gate::hasAnyRole(['admin', 'manager'])) {
            Flash::error('You are not authorized to create tasks.');
            return redirect("/teams/{$teamId}");
        }

        // Only show members of this team as assignee options
        $members = (new Team)->members((int) $teamId);

        return view('tasks.create', [
            'title'      => 'New Task — ' . $team['name'],
            'team'       => $team,
            'members'    => $members,
            'statuses'   => Task::STATUSES,
            'priorities' => Task::PRIORITIES,
        ]);
    }

    // ── POST /teams/{teamId}/tasks ────────────────────────────────────────────

    public function store(string $teamId): Response
    {
        $team = $this->resolveTeam((int) $teamId);

        if ($team instanceof Response) {
            return $team;
        }

        if (! Gate::hasAnyRole(['admin', 'manager'])) {
            Flash::error('You are not authorized to create tasks.');
            return redirect("/teams/{$teamId}");
        }

        $userId      = (int) ($_SESSION['user_id'] ?? 0);
        $title       = trim($this->request->input('title', ''));
        $description = trim($this->request->input('description', ''));
        $assignedTo  = (int) $this->request->input('assigned_to', 0);
        $priority    = $this->request->input('priority', 'medium');
        $status      = $this->request->input('status', 'open');
        $dueDate     = $this->request->input('due_date');
        $missionId   = (int) $this->request->input('mission_id', 0);

        // ── Validation ────────────────────────────────────────────────────────
        $errors = [];

        if (empty($title)) {
            $errors[] = 'Task title is required.';
        }

        if (! in_array($priority, Task::PRIORITIES, strict: true)) {
            $errors[] = 'Invalid priority.';
        }

        if (! in_array($status, Task::STATUSES, strict: true)) {
            $errors[] = 'Invalid status.';
        }

        if ($errors) {
            Flash::error(implode(' ', $errors));
            return redirect("/teams/{$teamId}/tasks/create");
        }

        try {
            $taskId = (int) $this->task->create([
                'team_id'     => (int) $teamId,
                'mission_id'  => $missionId ?: null,
                'assigned_to' => $assignedTo ?: null,
                'title'       => $title,
                'description' => $description,
                'status'      => $status,
                'priority'    => $priority,
                'due_date'    => $dueDate ?: null,
                'created_by'  => $userId,
            ]);

            // Notify assignee
            if ($assignedTo && $assignedTo !== $userId) {
                $this->notifyUser(
                    userId: $assignedTo,
                    senderId: $userId,
                    title: 'New task assigned to you',
                    body: "You have been assigned: {$title}",
                    url: "/tasks/{$taskId}",
                );
            }

            Flash::success("Task \"{$title}\" created.");
            return redirect("/tasks/{$taskId}");
        } catch (\Throwable) {
            Flash::error('Something went wrong. Please try again.');
            return redirect("/teams/{$teamId}/tasks/create");
        }
    }

    // ── GET /tasks/{id} ───────────────────────────────────────────────────────

    public function show(string $id): Response
    {
        $task = $this->resolveOrAbort((int) $id);

        if ($task instanceof Response) {
            return $task;
        }

        $comments = $this->task->comments((int) $id);
        $members  = $task['team_id']
            ? (new Team)->members((int) $task['team_id'])
            : [];

        return view('tasks.show', [
            'title'     => $task['title'],
            'task'      => $task,
            'comments'  => $comments,
            'members'   => $members,
            'statuses'  => Task::STATUSES,
            'priorities' => Task::PRIORITIES,
        ]);
    }

    // ── GET /tasks/{id}/edit ──────────────────────────────────────────────────

    public function edit(string $id): Response
    {
        if (! Gate::hasAnyRole(['admin', 'manager'])) {
            Flash::error('You are not authorized to edit tasks.');
            return redirect('/teams');
        }

        $task = $this->resolveOrAbort((int) $id);

        if ($task instanceof Response) {
            return $task;
        }

        $members = $task['team_id']
            ? (new Team)->members((int) $task['team_id'])
            : [];

        return view('tasks.edit', [
            'title'      => 'Edit — ' . $task['title'],
            'task'       => $task,
            'members'    => $members,
            'statuses'   => Task::STATUSES,
            'priorities' => Task::PRIORITIES,
        ]);
    }

    // ── POST /tasks/{id}/update ───────────────────────────────────────────────

    public function update(string $id): Response
    {
        if (! Gate::hasAnyRole(['admin', 'manager'])) {
            Flash::error('You are not authorized to edit tasks.');
            return redirect('/teams');
        }

        $task = $this->resolveOrAbort((int) $id);

        if ($task instanceof Response) {
            return $task;
        }

        $userId      = (int) ($_SESSION['user_id'] ?? 0);
        $title       = trim($this->request->input('title', ''));
        $description = trim($this->request->input('description', ''));
        $assignedTo  = (int) $this->request->input('assigned_to', 0);
        $priority    = $this->request->input('priority', $task['priority']);
        $status      = $this->request->input('status', $task['status']);
        $dueDate     = $this->request->input('due_date');

        if (empty($title)) {
            Flash::error('Task title is required.');
            return redirect("/tasks/{$id}/edit");
        }

        try {
            $completedAt = ($status === 'closed' && $task['status'] !== 'closed')
                ? date('Y-m-d H:i:s')
                : ($task['status'] === 'closed' && $status !== 'closed' ? null : $task['completed_at']);

            $this->task->update((int) $id, [
                'title'        => $title,
                'description'  => $description,
                'assigned_to'  => $assignedTo ?: null,
                'priority'     => $priority,
                'status'       => $status,
                'due_date'     => $dueDate ?: null,
                'completed_at' => $completedAt,
            ]);

            // Notify new assignee if changed
            if ($assignedTo && $assignedTo !== (int) $task['assigned_to'] && $assignedTo !== $userId) {
                $this->notifyUser(
                    userId: $assignedTo,
                    senderId: $userId,
                    title: 'Task reassigned to you',
                    body: "You have been assigned: {$title}",
                    url: "/tasks/{$id}",
                );
            }

            Flash::success("Task \"{$title}\" updated.");
            return redirect("/tasks/{$id}");
        } catch (\Throwable) {
            Flash::error('Something went wrong while saving.');
            return redirect("/tasks/{$id}/edit");
        }
    }

    // ── POST /tasks/{id}/status ───────────────────────────────────────────────

    /**
     * Quick status change — used from the task card / kanban buttons.
     */
    public function updateStatus(string $id): Response
    {
        $task = $this->resolveOrAbort((int) $id);

        if ($task instanceof Response) {
            return $task;
        }

        $status = $this->request->input('status', '');

        if (! in_array($status, Task::STATUSES, strict: true)) {
            Flash::error('Invalid status.');
            return redirect("/tasks/{$id}");
        }

        $this->task->transition((int) $id, $status);

        Flash::success('Task status updated.');
        return redirect("/tasks/{$id}");
    }

    // ── POST /tasks/{id}/comment ──────────────────────────────────────────────

    /**
     * Add a comment to a task.
     */
    public function addComment(string $id): Response
    {
        $task = $this->resolveOrAbort((int) $id);

        if ($task instanceof Response) {
            return $task;
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $body   = trim($this->request->input('body', ''));

        if (empty($body)) {
            Flash::error('Comment cannot be empty.');
            return redirect("/tasks/{$id}");
        }

        $this->task->addComment((int) $id, $userId, $body);

        Flash::success('Comment added.');
        return redirect("/tasks/{$id}#comments");
    }


    // ── GET /tasks/kanban ─────────────────────────────────────────────────────

    /**
     * Render the kanban board view.
     * The board itself is populated via /api/tasks/kanban (JavaScript fetch).
     */
    public function kanban(): Response
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $team   = new Team();

        $teams = Gate::hasAnyRole(['admin', 'manager', 'super_admin'])
            ? $team->allActive()
            : $team->forUser($userId);

        return view('tasks.kanban', [
            'title'  => 'Kanban Board',
            'teams'  => $teams,
        ]);
    }

    // ── POST /tasks/{id}/delete ───────────────────────────────────────────────

    public function destroy(string $id): Response
    {
        if (! Gate::hasAnyRole(['admin', 'manager'])) {
            Flash::error('You are not authorized to delete tasks.');
            return redirect('/teams');
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $task   = $this->task->findActive((int) $id);

        if (! $task) {
            Flash::error('Task not found.');
            return redirect('/teams');
        }

        $redirectTo = $task['team_id'] ? "/teams/{$task['team_id']}" : '/teams';

        try {
            $this->task->softDelete((int) $id, $userId);
            Flash::success("Task \"{$task['title']}\" deleted.");
        } catch (\Throwable) {
            Flash::error('Something went wrong while deleting.');
        }

        return redirect($redirectTo);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * @return array<string, mixed>|Response
     */
    private function resolveOrAbort(int $id): array|Response
    {
        $task   = $this->task->findActive($id);
        $userId = (int) ($_SESSION['user_id'] ?? 0);

        if (! $task) {
            Flash::error('Task not found.');
            return redirect('/teams');
        }

        // Regular users can only see tasks assigned to them
        // or tasks in a team they belong to
        if (! Gate::hasAnyRole(['admin', 'manager'])) {
            $isMine = (int) $task['assigned_to'] === $userId;
            $inTeam = $task['team_id'] && (new Team)->hasMember((int) $task['team_id'], $userId);

            if (! $isMine && ! $inTeam) {
                Flash::error('You do not have access to this task.');
                return redirect('/teams');
            }
        }

        return $task;
    }

    /**
     * @return array<string, mixed>|Response
     */
    private function resolveTeam(int $teamId): array|Response
    {
        $team   = (new Team)->findActive($teamId);
        $userId = (int) ($_SESSION['user_id'] ?? 0);

        if (! $team) {
            Flash::error('Team not found.');
            return redirect('/teams');
        }

        if (! Gate::hasAnyRole(['admin', 'manager'])) {
            if (! (new Team)->hasMember($teamId, $userId)) {
                Flash::error('You do not have access to this team.');
                return redirect('/teams');
            }
        }

        return $team;
    }

    private function notifyUser(
        int $userId,
        int $senderId,
        string $title,
        string $body,
        string $url,
    ): void {
        Connection::getInstance()->execute(
            'INSERT INTO notifications (user_id, sender_id, title, body, url, is_read, created_at)
             VALUES (?, ?, ?, ?, ?, 0, ?)',
            [$userId, $senderId, $title, $body, $url, date('Y-m-d H:i:s')],
        );
    }
}
