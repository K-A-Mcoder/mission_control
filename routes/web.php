<?php

use App\Controllers\HomeController;
use App\Controllers\DashboardController;
use App\Controllers\MissionController;
use App\Controllers\Api\MissionApiController;
use App\Controllers\TeamController;
use App\Controllers\TaskController;
use App\Controllers\Api\TeamApiController;
use App\Controllers\Api\TaskApiController;
use App\Controllers\Auth\LoginController;
use App\Controllers\Auth\LogoutController;
use App\Controllers\Auth\RegisterController;
use App\Controllers\Api\AuthController;
use App\Controllers\Api\NotificationApiController;
use App\Controllers\NotificationController;
use App\Controllers\ReportController;
use App\Controllers\SettingsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

return function (Etus\Framework\Routing\Router $router): void {

    // ── Public ────────────────────────────────────────────────────────────────
    $router->get('/', [HomeController::class, 'framework_info']);
    // $router->get('/framework', [HomeController::class, 'framework_info']);

    // ── Auth (guest only) ─────────────────────────────────────────────────────
    $router->get('/login',     [LoginController::class,    'index'],   ['guest']);
    $router->post('/login',    [LoginController::class,    'doLogin'], ['guest', 'csrf']);
    $router->get('/register',  [RegisterController::class, 'index'],   ['guest']);
    $router->post('/register', [RegisterController::class, 'store'],   ['guest', 'csrf']);

    // ── Account activation ────────────────────────────────────────────────────
    $router->get('/auth/activate', [AuthController::class, 'activate']);
    $router->get('/auth/resend',   [AuthController::class, 'showResend'], ['guest']);
    $router->post('/auth/resend',  [AuthController::class, 'resend'],     ['guest', 'csrf']);

    // ── Session ───────────────────────────────────────────────────────────────
    $router->post('/logout', [LogoutController::class, '__invoke'], ['auth', 'csrf']);

    // ── Dashboards ────────────────────────────────────────────────────────────
    $router->get('/dashboard',          [DashboardController::class, 'index'],    ['auth']);
    $router->get('/dashboard/teams',    [DashboardController::class, 'teams'],    ['auth']);
    $router->get('/dashboard/tasks',    [DashboardController::class, 'tasks'],    ['auth']);
    $router->get('/dashboard/missions', [DashboardController::class, 'missions'], ['auth']);

    // ── Teams (view) ──────────────────────────────────────────────────────────
    $router->get('/teams',                      [TeamController::class, 'index'],        ['auth']);
    $router->get('/teams/create',               [TeamController::class, 'create'],       ['auth']);
    $router->post('/teams',                     [TeamController::class, 'store'],        ['auth', 'csrf']);
    $router->get('/teams/{id}',                 [TeamController::class, 'show'],         ['auth']);
    $router->get('/teams/{id}/edit',            [TeamController::class, 'edit'],         ['auth']);
    $router->post('/teams/{id}/update',         [TeamController::class, 'update'],       ['auth', 'csrf']);
    $router->post('/teams/{id}/delete',         [TeamController::class, 'destroy'],      ['auth', 'csrf']);
    $router->post('/teams/{id}/members',        [TeamController::class, 'addMember'],    ['auth', 'csrf']);
    $router->post('/teams/{id}/members/remove', [TeamController::class, 'removeMember'], ['auth', 'csrf']);

    // ── Tasks (view) ──────────────────────────────────────────────────────────
    $router->get('/tasks/kanban',               [TaskController::class, 'kanban'],       ['auth']);
    $router->get('/teams/{teamId}/tasks/create', [TaskController::class, 'create'],       ['auth']);
    $router->post('/teams/{teamId}/tasks',      [TaskController::class, 'store'],        ['auth', 'csrf']);
    $router->get('/tasks/{id}',                 [TaskController::class, 'show'],         ['auth']);
    $router->get('/tasks/{id}/edit',            [TaskController::class, 'edit'],         ['auth']);
    $router->post('/tasks/{id}/update',         [TaskController::class, 'update'],       ['auth', 'csrf']);
    $router->post('/tasks/{id}/status',         [TaskController::class, 'updateStatus'], ['auth', 'csrf']);
    $router->post('/tasks/{id}/comment',        [TaskController::class, 'addComment'],   ['auth', 'csrf']);
    $router->post('/tasks/{id}/delete',         [TaskController::class, 'destroy'],      ['auth', 'csrf']);

    // ── Missions (view) ───────────────────────────────────────────────────────
    $router->get('/missions',                   [MissionController::class, 'index'],      ['auth']);
    $router->get('/missions/create',            [MissionController::class, 'create'],     ['auth']);
    $router->post('/missions',                  [MissionController::class, 'store'],      ['auth', 'csrf']);
    $router->get('/missions/{id}',              [MissionController::class, 'show'],       ['auth']);
    $router->get('/missions/{id}/edit',         [MissionController::class, 'edit'],       ['auth']);
    $router->post('/missions/{id}/update',      [MissionController::class, 'update'],     ['auth', 'csrf']);
    $router->post('/missions/{id}/delete',      [MissionController::class, 'destroy'],    ['auth', 'csrf']);
    $router->post('/missions/{id}/assign-team', [MissionController::class, 'assignTeam'], ['auth', 'csrf']);

    // ── Reports ───────────────────────────────────────────────────────────────
    // All authenticated users; access scoped inside controller by role.
    $router->get('/reports',                    [ReportController::class, 'index'],    ['auth']);
    $router->get('/reports/create',             [ReportController::class, 'create'],   ['auth']);
    $router->post('/reports',                   [ReportController::class, 'store'],    ['auth', 'csrf']);
    $router->get('/reports/{id}',               [ReportController::class, 'show'],     ['auth']);
    $router->get('/reports/{id}/edit',          [ReportController::class, 'edit'],     ['auth']);
    $router->post('/reports/{id}/update',       [ReportController::class, 'update'],   ['auth', 'csrf']);
    $router->post('/reports/{id}/review',       [ReportController::class, 'review'],   ['auth', 'csrf']);
    $router->post('/reports/{id}/escalate',     [ReportController::class, 'escalate'], ['auth', 'csrf']);
    $router->post('/reports/{id}/delete',       [ReportController::class, 'destroy'],  ['auth', 'csrf']);

    // ── Notifications (full page) ─────────────────────────────────────────────
    $router->get('/notifications', [NotificationController::class, 'index'], ['auth']);

    // ── System Settings (admin only, enforced inside controller) ─────────────
    $router->get('/settings',   [SettingsController::class, 'index'],  ['auth']);
    $router->post('/settings',  [SettingsController::class, 'update'], ['auth', 'csrf']);

    // ── API — Tasks ───────────────────────────────────────────────────────────
    $router->get('/api/tasks',             [TaskApiController::class, 'index'],       ['auth']);
    $router->get('/api/tasks/kanban',      [TaskApiController::class, 'kanban'],      ['auth']);
    $router->get('/api/tasks/stats',       [TaskApiController::class, 'stats'],       ['auth']);
    $router->post('/api/tasks/{id}/status', [TaskApiController::class, 'updateStatus'], ['auth']);
    $router->post('/api/tasks/bulk-status', [TaskApiController::class, 'bulkStatus'],  ['auth']);
    $router->post('/api/tasks/bulk-delete', [TaskApiController::class, 'bulkDelete'],  ['auth']);

    // ── API — Teams ───────────────────────────────────────────────────────────
    $router->get('/api/teams',                               [TeamApiController::class, 'index'],        ['auth']);
    $router->get('/api/teams/stats',                         [TeamApiController::class, 'stats'],        ['auth']);
    $router->get('/api/teams/{id}',                          [TeamApiController::class, 'show'],         ['auth']);
    $router->post('/api/teams/{id}/members',                 [TeamApiController::class, 'addMember'],    ['auth']);
    $router->post('/api/teams/{id}/members/{memberId}/remove', [TeamApiController::class, 'removeMember'], ['auth']);

    // ── API — Missions ────────────────────────────────────────────────────────
    $router->get('/api/missions',              [MissionApiController::class, 'index'],      ['auth']);
    $router->post('/api/missions',             [MissionApiController::class, 'store'],      ['auth', 'csrf']);
    $router->post('/api/missions/delete',      [MissionApiController::class, 'destroy'],    ['auth', 'csrf']);
    $router->post('/api/missions/assign-team', [MissionApiController::class, 'assignTeam'], ['auth', 'csrf']);

    // ── API — Notifications ───────────────────────────────────────────────────
    // Lightweight polling endpoint — no CSRF (GET + safe POST with XHR header check)
    $router->get('/api/notifications',           [NotificationApiController::class, 'index'],       ['auth']);
    $router->get('/api/notifications/poll',      [NotificationApiController::class, 'poll'],        ['auth']);
    $router->post('/api/notifications/{id}/read', [NotificationApiController::class, 'markRead'],    ['auth']);
    $router->post('/api/notifications/read-all', [NotificationApiController::class, 'markAllRead'], ['auth']);
};
