<?php

use App\Controllers\HomeController;
use App\Controllers\DashboardController;
use App\Controllers\MissionController;
use App\Controllers\Api\MissionApiController;
use App\Controllers\TeamController;
use App\Controllers\TaskController;
use App\Controllers\ReportController;
use App\Controllers\SettingsController;
use App\Controllers\NotificationController;
use App\Controllers\ChatController;
use App\Controllers\Admin\UserController    as AdminUserController;
use App\Controllers\Admin\RoleController    as AdminRoleController;
use App\Controllers\Admin\PermissionController as AdminPermController;
use App\Controllers\Api\TeamApiController;
use App\Controllers\Api\TaskApiController;
use App\Controllers\Api\NotificationApiController;
use App\Controllers\Api\ChatApiController;
use App\Controllers\Auth\LoginController;
use App\Controllers\Auth\LogoutController;
use App\Controllers\Auth\RegisterController;
use App\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Middleware tokens:
|   'auth'  — must be logged in
|   'guest' — must NOT be logged in
|   'csrf'  — validates _token on mutating requests
|--------------------------------------------------------------------------
*/

return function (Etus\Framework\Routing\Router $router): void {

    // ── Public ────────────────────────────────────────────────────────────────
    $router->get('/', [HomeController::class, 'index']);

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

    // ── Teams ─────────────────────────────────────────────────────────────────
    $router->get('/teams',                      [TeamController::class, 'index'],        ['auth']);
    $router->get('/teams/create',               [TeamController::class, 'create'],       ['auth']);
    $router->post('/teams',                     [TeamController::class, 'store'],        ['auth', 'csrf']);
    $router->get('/teams/{id}',                 [TeamController::class, 'show'],         ['auth']);
    $router->get('/teams/{id}/edit',            [TeamController::class, 'edit'],         ['auth']);
    $router->post('/teams/{id}/update',         [TeamController::class, 'update'],       ['auth', 'csrf']);
    $router->post('/teams/{id}/delete',         [TeamController::class, 'destroy'],      ['auth', 'csrf']);
    $router->post('/teams/{id}/members',        [TeamController::class, 'addMember'],    ['auth', 'csrf']);
    $router->post('/teams/{id}/members/remove', [TeamController::class, 'removeMember'], ['auth', 'csrf']);

    // ── Tasks ─────────────────────────────────────────────────────────────────
    $router->get('/tasks/kanban',                [TaskController::class, 'kanban'],       ['auth']);
    $router->get('/teams/{teamId}/tasks/create', [TaskController::class, 'create'],       ['auth']);
    $router->post('/teams/{teamId}/tasks',       [TaskController::class, 'store'],        ['auth', 'csrf']);
    $router->get('/tasks/{id}',                  [TaskController::class, 'show'],         ['auth']);
    $router->get('/tasks/{id}/edit',             [TaskController::class, 'edit'],         ['auth']);
    $router->post('/tasks/{id}/update',          [TaskController::class, 'update'],       ['auth', 'csrf']);
    $router->post('/tasks/{id}/status',          [TaskController::class, 'updateStatus'], ['auth', 'csrf']);
    $router->post('/tasks/{id}/comment',         [TaskController::class, 'addComment'],   ['auth', 'csrf']);
    $router->post('/tasks/{id}/delete',          [TaskController::class, 'destroy'],      ['auth', 'csrf']);

    // ── Missions ──────────────────────────────────────────────────────────────
    $router->get('/missions',                    [MissionController::class, 'index'],      ['auth']);
    $router->get('/missions/create',             [MissionController::class, 'create'],     ['auth']);
    $router->post('/missions',                   [MissionController::class, 'store'],      ['auth', 'csrf']);
    $router->get('/missions/{id}',               [MissionController::class, 'show'],       ['auth']);
    $router->get('/missions/{id}/edit',          [MissionController::class, 'edit'],       ['auth']);
    $router->post('/missions/{id}/update',       [MissionController::class, 'update'],     ['auth', 'csrf']);
    $router->post('/missions/{id}/delete',       [MissionController::class, 'destroy'],    ['auth', 'csrf']);
    $router->post('/missions/{id}/assign-team',  [MissionController::class, 'assignTeam'], ['auth', 'csrf']);

    // ── Reports ───────────────────────────────────────────────────────────────
    $router->get('/reports',                [ReportController::class, 'index'],    ['auth']);
    $router->get('/reports/create',         [ReportController::class, 'create'],   ['auth']);
    $router->post('/reports',               [ReportController::class, 'store'],    ['auth', 'csrf']);
    $router->get('/reports/{id}',           [ReportController::class, 'show'],     ['auth']);
    $router->get('/reports/{id}/edit',      [ReportController::class, 'edit'],     ['auth']);
    $router->post('/reports/{id}/update',   [ReportController::class, 'update'],   ['auth', 'csrf']);
    $router->post('/reports/{id}/review',   [ReportController::class, 'review'],   ['auth', 'csrf']);
    $router->post('/reports/{id}/escalate', [ReportController::class, 'escalate'], ['auth', 'csrf']);
    $router->post('/reports/{id}/delete',   [ReportController::class, 'destroy'],  ['auth', 'csrf']);

    // ── Chat (view layer) ─────────────────────────────────────────────────────
    $router->get('/chat',                  [ChatController::class, 'index'],          ['auth']);
    $router->get('/chat/{roomId}',         [ChatController::class, 'room'],           ['auth']);
    $router->post('/chat/direct',          [ChatController::class, 'startDirect'],    ['auth', 'csrf']);
    $router->post('/chat/command-room',    [ChatController::class, 'openCommandRoom'], ['auth', 'csrf']);

    // ── Notifications ─────────────────────────────────────────────────────────
    $router->get('/notifications', [NotificationController::class, 'index'], ['auth']);

    // ── Settings ──────────────────────────────────────────────────────────────
    $router->get('/settings',  [SettingsController::class, 'index'],  ['auth']);
    $router->post('/settings', [SettingsController::class, 'update'], ['auth', 'csrf']);

    // ── Admin — Users (super_admin only, enforced in controller) ─────────────
    $router->get('/admin/users',                  [AdminUserController::class, 'index'],     ['auth']);
    $router->get('/admin/users/create',           [AdminUserController::class, 'create'],    ['auth']);
    $router->post('/admin/users',                 [AdminUserController::class, 'store'],     ['auth', 'csrf']);
    $router->get('/admin/users/{id}',             [AdminUserController::class, 'show'],      ['auth']);
    $router->get('/admin/users/{id}/edit',        [AdminUserController::class, 'edit'],      ['auth']);
    $router->post('/admin/users/{id}/update',     [AdminUserController::class, 'update'],    ['auth', 'csrf']);
    $router->post('/admin/users/{id}/status',     [AdminUserController::class, 'setStatus'], ['auth', 'csrf']);
    $router->post('/admin/users/{id}/delete',     [AdminUserController::class, 'destroy'],   ['auth', 'csrf']);

    // ── Admin — Roles (super_admin only) ─────────────────────────────────────
    $router->get('/admin/roles',                        [AdminRoleController::class, 'index'],           ['auth']);
    $router->get('/admin/roles/create',                 [AdminRoleController::class, 'create'],          ['auth']);
    $router->post('/admin/roles',                       [AdminRoleController::class, 'store'],           ['auth', 'csrf']);
    $router->get('/admin/roles/{id}',                   [AdminRoleController::class, 'show'],            ['auth']);
    $router->post('/admin/roles/{id}/permissions',      [AdminRoleController::class, 'syncPermissions'], ['auth', 'csrf']);
    $router->post('/admin/roles/{id}/delete',           [AdminRoleController::class, 'destroy'],         ['auth', 'csrf']);

    // ── Admin — Permissions (super_admin only) ───────────────────────────────
    $router->get('/admin/permissions',                  [AdminPermController::class, 'index'],   ['auth']);
    $router->post('/admin/permissions',                 [AdminPermController::class, 'store'],   ['auth', 'csrf']);
    $router->post('/admin/permissions/{id}/update',     [AdminPermController::class, 'update'],  ['auth', 'csrf']);
    $router->post('/admin/permissions/{id}/delete',     [AdminPermController::class, 'destroy'], ['auth', 'csrf']);

    // ── API — Tasks ───────────────────────────────────────────────────────────
    $router->get('/api/tasks',              [TaskApiController::class, 'index'],        ['auth']);
    $router->get('/api/tasks/kanban',       [TaskApiController::class, 'kanban'],       ['auth']);
    $router->get('/api/tasks/stats',        [TaskApiController::class, 'stats'],        ['auth']);
    $router->post('/api/tasks/{id}/status', [TaskApiController::class, 'updateStatus'], ['auth']);
    $router->post('/api/tasks/bulk-status', [TaskApiController::class, 'bulkStatus'],   ['auth']);
    $router->post('/api/tasks/bulk-delete', [TaskApiController::class, 'bulkDelete'],   ['auth']);

    // ── API — Teams ───────────────────────────────────────────────────────────
    $router->get('/api/teams',                                [TeamApiController::class, 'index'],        ['auth']);
    $router->get('/api/teams/stats',                          [TeamApiController::class, 'stats'],        ['auth']);
    $router->get('/api/teams/{id}',                           [TeamApiController::class, 'show'],         ['auth']);
    $router->post('/api/teams/{id}/members',                  [TeamApiController::class, 'addMember'],    ['auth']);
    $router->post('/api/teams/{id}/members/{memberId}/remove', [TeamApiController::class, 'removeMember'], ['auth']);

    // ── API — Missions ────────────────────────────────────────────────────────
    $router->get('/api/missions',              [MissionApiController::class, 'index'],      ['auth']);
    $router->post('/api/missions',             [MissionApiController::class, 'store'],      ['auth', 'csrf']);
    $router->post('/api/missions/delete',      [MissionApiController::class, 'destroy'],    ['auth', 'csrf']);
    $router->post('/api/missions/assign-team', [MissionApiController::class, 'assignTeam'], ['auth', 'csrf']);

    // ── API — Notifications ───────────────────────────────────────────────────
    $router->get('/api/notifications',            [NotificationApiController::class, 'index'],       ['auth']);
    $router->get('/api/notifications/poll',       [NotificationApiController::class, 'poll'],        ['auth']);
    $router->post('/api/notifications/{id}/read', [NotificationApiController::class, 'markRead'],    ['auth']);
    $router->post('/api/notifications/read-all',  [NotificationApiController::class, 'markAllRead'], ['auth']);

    // ── API — Chat ────────────────────────────────────────────────────────────
    $router->get('/api/chat/rooms',               [ChatApiController::class, 'roomList'],    ['auth']);
    $router->post('/api/chat/{roomId}/send',      [ChatApiController::class, 'send'],        ['auth']);
    $router->get('/api/chat/{roomId}/poll',       [ChatApiController::class, 'poll'],        ['auth']);
    $router->post('/api/chat/{roomId}/read',      [ChatApiController::class, 'markRead'],    ['auth']);
    $router->post('/api/chat/messages/{id}/ack',  [ChatApiController::class, 'acknowledge'], ['auth']);
    $router->post('/api/chat/messages/{id}/delete', [ChatApiController::class, 'delete'],      ['auth']);
};
