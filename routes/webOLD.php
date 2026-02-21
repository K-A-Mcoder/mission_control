<?php

use App\Controllers\Api\AuthController;
use Etus\Framework\Routing\Router;
use App\Controllers\HomeController;
use App\Controllers\Auth\LoginController;
use App\Controllers\Auth\LogoutController;
use App\Controllers\DashboardController;
use App\Controllers\MissionController;
use App\Controllers\Api\MissionController as MissionApiController;
use App\Controllers\Auth\RegisterController;
use App\Controllers\TeamController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Return a Router instance with all web routes registered on it.
| The Kernel will load this file and call match() on the router.
|
| For example, you can define a route like this:
|
| $router->get('/login', [LoginController::class, 'showLoginForm']);
|
*/

// return function (Router $router): void {
//     $router->get('/', [HomeController::class, 'index'], ['auth']);
//     $router->get('/dashboard', [HomeController::class, 'dashboard'], ['auth']);

//     $router->get('/login',  [LoginController::class, 'index'],   ['guest']);
//     $router->post('/login', [LoginController::class, 'doLogin'], ['guest', 'csrf']);
//     $router->post('/logout', [LogoutController::class, '__invoke'], ['auth', 'csrf']);

//     // ── Missions ──────────────────────────────────────────────────────────────
//     // GET  /missions            → list (role-filtered)
//     // POST /missions            → create (admin, manager)
//     // POST /missions/delete     → soft delete single or bulk (admin)
//     // POST /missions/assign-team → assign team + notify (admin, manager)
//     $router->get('/missions',              [MissionController::class, 'index'],      ['auth']);
//     $router->post('/missions',             [MissionController::class, 'store'],      ['auth', 'csrf']);
//     $router->post('/missions/delete',      [MissionController::class, 'destroy'],    ['auth', 'csrf']);
//     $router->post('/missions/assign-team', [MissionController::class, 'assignTeam'], ['auth', 'csrf']);
// };


return function (Etus\Framework\Routing\Router $router): void {

    // // Public
    // $router->get('/', [HomeController::class, 'index']);

    // // Guest-only
    // $router->get('/login',  [LoginController::class, 'index'],   ['guest']);
    // $router->post('/login', [LoginController::class, 'doLogin'], ['guest', 'csrf']);


    // // Dashboard
    // $router->get('/dashboard', [DashboardController::class, 'index'], ['auth']);

    // // ── Missions ──────────────────────────────────────────────────────────────
    // // GET  /missions            → list (role-filtered)
    // // POST /missions            → create (admin, manager)
    // // POST /missions/delete     → soft delete single or bulk (admin)
    // // POST /missions/assign-team → assign team + notify (admin, manager)
    // $router->get('/missions',              [MissionController::class, 'index'],      ['auth']);
    // $router->post('/missions',             [MissionController::class, 'store'],      ['auth', 'csrf']);
    // $router->post('/missions/delete',      [MissionController::class, 'destroy'],    ['auth', 'csrf']);
    // $router->post('/missions/assign-team', [MissionController::class, 'assignTeam'], ['auth', 'csrf']);


    // // Public
    // $router->get('/', [HomeController::class, 'index']);

    // // ── Auth: guest-only ──────────────────────────────────────────────────────
    // $router->get('/login',    [LoginController::class,    'index'],   ['guest']);
    // $router->post('/login',   [LoginController::class,    'doLogin'], ['guest', 'csrf']);
    // $router->get('/register', [RegisterController::class, 'index'],   ['guest']);
    // $router->post('/register', [RegisterController::class, 'store'],   ['guest', 'csrf']);


    // // ── Resend activation link ────────────────────────────────────────────────
    // $router->get('/auth/resend',   [AuthController::class, 'showResend'], ['guest']);
    // $router->post('/auth/resend',  [AuthController::class, 'resend'],     ['guest', 'csrf']);

    // // Logout
    // $router->post('/logout', [LogoutController::class, '__invoke'], ['auth', 'csrf']);

    // // Dashboard
    // $router->get('/dashboard', [DashboardController::class, 'index'], ['auth']);

    // // ── Missions (view-based) ─────────────────────────────────────────────────
    // $router->get('/missions',                    [MissionController::class, 'index'],      ['auth']);
    // $router->get('/missions/create',             [MissionController::class, 'create'],     ['auth']);
    // $router->post('/missions',                   [MissionController::class, 'store'],      ['auth', 'csrf']);
    // $router->get('/missions/{id}',               [MissionController::class, 'show'],       ['auth']);
    // $router->get('/missions/{id}/edit',          [MissionController::class, 'edit'],       ['auth']);
    // $router->post('/missions/{id}/update',       [MissionController::class, 'update'],     ['auth', 'csrf']);
    // $router->post('/missions/{id}/delete',       [MissionController::class, 'destroy'],    ['auth', 'csrf']);
    // $router->post('/missions/{id}/assign-team',  [MissionController::class, 'assignTeam'], ['auth', 'csrf']);

    // // ── Teams (view-based) ────────────────────────────────────────────────────
    // $router->get('/teams', [TeamController::class, 'index'], ['auth']);

    // // ── Missions API ──────────────────────────────────────────────────────────
    // $router->post('/api/missions',             [MissionApiController::class, 'store'],      ['auth', 'csrf']);
    // $router->post('/api/missions/delete',      [MissionApiController::class, 'destroy'],    ['auth', 'csrf']);
    // $router->post('/api/missions/assign-team', [MissionApiController::class, 'assignTeam'], ['auth', 'csrf']);

    // // ── Account activation — no middleware, token IS the credential ───────────
    // $router->get('/api/activate', [AuthController::class, 'activate']);
};
