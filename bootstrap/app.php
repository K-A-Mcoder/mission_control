<?php

/*
|--------------------------------------------------------------------------
| Bootstrap
|--------------------------------------------------------------------------
|
| This file wires together the framework's core objects and hands a
| fully configured Kernel back to the entry point (public/index.php).
|
*/

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use Etus\Framework\Http\Kernel;
use Etus\Framework\Routing\Router;
use Etus\Framework\Database\Connection;
use Etus\Framework\View\View;
use Etus\Framework\Http\Middleware\MiddlewareRegistry;
use Etus\Framework\Http\Middleware\CsrfMiddleware;
use Etus\Framework\Http\Middleware\RequestLoggerMiddleware;
use Etus\Framework\Http\Middleware\PermissionMiddleware;
use Etus\Framework\Http\Middleware\RoleMiddleware;
use Dotenv\Dotenv;

// Validate required paths before anything else
$requiredPaths = [
    CONFIG_PATH . '/database.php',
    CONFIG_PATH . '/helpers.php',
    VIEWS_PATH,
];

foreach ($requiredPaths as $path) {
    if (! file_exists($path)) {
        throw new \RuntimeException("Required path does not exist: [{$path}]");
    }
}

// Load environment variables
Dotenv::createImmutable(BASE_PATH)->load();

// Register view path
View::setViewPath(VIEWS_PATH);

// Register middleware aliases
MiddlewareRegistry::register('guest',  GuestMiddleware::class);
MiddlewareRegistry::register('auth',  AuthMiddleware::class);
MiddlewareRegistry::register('csrf',  CsrfMiddleware::class);
MiddlewareRegistry::register('log',   RequestLoggerMiddleware::class);
MiddlewareRegistry::register('role',  RoleMiddleware::class);
MiddlewareRegistry::register('permission', PermissionMiddleware::class);

// Boot database
$config = include CONFIG_PATH . '/database.php';

$connection = Connection::create(
    $config['connectionString'],
    $config['username'],
    $config['password'],
);

// Register routes
$router = new Router();

$registerRoutes = include BASE_PATH . '/routes/web.php';
$registerRoutes($router);

return new Kernel($router, $connection);
