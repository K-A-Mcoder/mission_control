<?php

namespace App\Controllers;

use App\Models\Task;
use App\Models\Team;
use App\Models\Mission;
use App\Models\User;
use App\Controllers\BaseController;
use Etus\Framework\Auth\Gate;
use Etus\Framework\Http\Response;
use Etus\Framework\Database\Connection;

class DashboardController extends BaseController
{
    protected array $middleware = ['auth'];

    // ── GET /dashboard ────────────────────────────────────────────────────────

    public function index(): Response
    {
        $userId = (auth_id() ?? 0);
        $role   = auth_role() ?? '';

        $task    = new Task();
        $team    = new Team();
        $mission = new Mission();

        // Scope: super_admin / admin see everything. Others scoped to their access.
        $scopeUserId = Gate::hasAnyRole(['super_admin', 'admin']) ? null : $userId;
        $isSuperAdmin = Gate::hasRole('super_admin');

        // ── Shared stats ──────────────────────────────────────────────────────
        $taskStats = $task->stats($scopeUserId);
        $teamStats = $team->stats($scopeUserId);

        // ── Recent tasks ──────────────────────────────────────────────────────
        $recentTasks = array_slice(
            $task->filter(array_filter(['scope_user' => $scopeUserId])),
            0,
            10
        );

        // ── Recent activity (last 10 audit logs) ──────────────────────────────
        $recentActivity = $this->recentActivity($isSuperAdmin ? null : $userId);

        // ── Missions summary ──────────────────────────────────────────────────
        $missions = match (true) {
            Gate::hasRole('admin'), Gate::hasRole('super_admin') => $mission->allWithTaskCounts(),
            Gate::hasRole('manager')                             => $mission->nonSecretWithTaskCounts(),
            default                                              => $mission->forUser($userId),
        };
        $missionStats = [
            'total'  => count($missions),
            'active' => count(array_filter($missions, fn($m) => empty($m['deleted_at']))),
        ];

        // ── Super admin extras ────────────────────────────────────────────────
        $allUsers       = [];
        $userTaskStats  = [];
        $filterUserId   = null;

        if ($isSuperAdmin) {
            $allUsers = (new User)->active();

            // Allow super_admin to filter dashboard by user
            $filterUserId = (int) $this->request->query('user_id', 0) ?: null;

            if ($filterUserId) {
                $taskStats   = $task->stats($filterUserId);
                $teamStats   = $team->stats($filterUserId);
                $recentTasks = array_slice($task->filter(['scope_user' => $filterUserId]), 0, 10);
            }

            // Per-user task counts for the super admin table
            foreach ($allUsers as $u) {
                $s = $task->stats((int) $u['user_id']);
                $userTaskStats[$u['user_id']] = $s;
            }
        }

        return view('dashboard/Index', [
            'title'          => 'Dashboard',
            'taskStats'      => $taskStats,
            'teamStats'      => $teamStats,
            'missionStats'   => $missionStats,
            'recentTasks'    => $recentTasks,
            'recentActivity' => $recentActivity,
            'missions'       => array_slice($missions, 0, 5),
            'isSuperAdmin'   => $isSuperAdmin,
            'allUsers'       => $allUsers,
            'userTaskStats'  => $userTaskStats,
            'filterUserId'   => $filterUserId,
            'role'           => $role,
        ]);
    }

    // ── GET /dashboard/teams ──────────────────────────────────────────────────

    public function teams(): Response
    {
        $userId      = (int) ($_SESSION['user_id'] ?? 0);
        $team        = new Team();
        $scopeUserId = Gate::hasAnyRole(['super_admin', 'admin']) ? null : $userId;

        $teams     = Gate::hasAnyRole(['admin', 'super_admin', 'manager'])
            ? $team->allActive()
            : $team->forUser($userId);

        $teamStats = $team->stats($scopeUserId);

        // Compute task stats per team
        $teamsWithStats = array_map(function ($t) use ($team) {
            $t['task_stats'] = $team->taskStats((int) $t['id']);
            return $t;
        }, $teams);

        return view('dashboard.teams', [
            'title'     => 'Teams Overview',
            'teams'     => $teamsWithStats,
            'teamStats' => $teamStats,
        ]);
    }

    // ── GET /dashboard/tasks ──────────────────────────────────────────────────

    public function tasks(): Response
    {
        $userId      = (int) ($_SESSION['user_id'] ?? 0);
        $task        = new Task();
        $team        = new Team();
        $scopeUserId = Gate::hasAnyRole(['super_admin', 'admin']) ? null : $userId;

        $taskStats = $task->stats($scopeUserId);

        // Load filter options
        $teams = Gate::hasAnyRole(['admin', 'super_admin', 'manager'])
            ? $team->allActive()
            : $team->forUser($userId);

        $assignees = Gate::hasAnyRole(['admin', 'super_admin', 'manager'])
            ? (new User)->active()
            : [];

        // Initial task list (can be refreshed by DataTables via API)
        $filters = [];
        if ($scopeUserId) $filters['scope_user'] = $scopeUserId;

        $tasks = $task->filter($filters);

        return view('dashboard.tasks', [
            'title'      => 'Tasks Overview',
            'taskStats'  => $taskStats,
            'tasks'      => $tasks,
            'teams'      => $teams,
            'assignees'  => $assignees,
            'statuses'   => Task::STATUSES,
            'priorities' => Task::PRIORITIES,
        ]);
    }

    // ── GET /dashboard/missions ───────────────────────────────────────────────

    public function missions(): Response
    {
        $userId  = (int) ($_SESSION['user_id'] ?? 0);
        $mission = new Mission();

        $missions = match (true) {
            Gate::hasAnyRole(['super_admin', 'admin']) => $mission->allWithTaskCounts(),
            Gate::hasRole('manager')                   => $mission->nonSecretWithTaskCounts(),
            default                                    => $mission->forUser($userId),
        };

        $missionStats = [
            'total'        => count($missions),
            'with_tasks'   => count(array_filter($missions, fn($m) => (int)($m['total_tasks'] ?? 0) > 0)),
            'completed'    => count(array_filter(
                $missions,
                fn($m) =>
                (int)($m['total_tasks'] ?? 0) > 0 &&
                    (int)($m['total_tasks'] ?? 0) === (int)($m['completed_tasks'] ?? 0)
            )),
        ];

        return view('dashboard.missions', [
            'title'        => 'Missions Overview',
            'missions'     => $missions,
            'missionStats' => $missionStats,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function recentActivity($userId = null): array
    {
        $db     = Connection::getInstance();
        $where  = '';
        $params = [];

        if ($userId !== null) {
            $where  = 'WHERE al.user_id = ?';
            $params = [$userId];
        }

        return $db->select(
            "SELECT al.*, u.full_name AS actor_name
             FROM   activity_logs al
             LEFT JOIN users u ON u.user_id = al.user_id
             {$where}
             ORDER  BY al.created_at DESC
             LIMIT  10",
            $params,
        ) ?? [];
    }
}
