# Etus Framework

> A lightweight, expressive PHP framework for developers who want full control without the weight of a full-stack monolith.

![Version](https://img.shields.io/badge/version-1.0.0-c84b2f)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777bb4)
![License](https://img.shields.io/badge/license-MIT-27c93f)

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Directory Structure](#directory-structure)
- [Configuration](#configuration)
- [Routing](#routing)
- [Middleware](#middleware)
- [Controllers](#controllers)
- [Views](#views)
- [Models](#models)
- [Database](#database)
- [Helpers](#helpers)
- [Activity Logging](#activity-logging)
- [Changelog](#changelog)

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | `^8.2` |
| nikic/fast-route | `^1.3` |
| vlucas/phpdotenv | `^5.6` |
| symfony/var-dumper | `^8.0` |

---

## Installation

```bash
git clone https://github.com/etus/framework.git my-project
cd my-project
composer install
cp .env.example .env
```

Start the development server:

```bash
composer run start
# Listening at http://localhost:8500
```

---

## Directory Structure

```
project/
├── app/                        # Your application code
│   ├── config/
│   │   ├── database.php        # Database configuration (reads from .env)
│   │   └── helpers.php         # Global helper functions
│   ├── Controllers/            # Application controllers
│   ├── Models/                 # Application models
│   └── views/                  # PHP templates
│       └── layouts/            # Layout wrappers
├── framework/                  # Framework internals — do not edit
│   ├── Controllers/
│   │   └── AbstractController.php
│   ├── Database/
│   │   ├── Connection.php
│   │   ├── Model.php
│   │   ├── QueryBuilder.php
│   │   └── ActivityLogger.php
│   ├── Http/
│   │   ├── Kernel.php
│   │   ├── Request.php
│   │   ├── Response.php
│   │   └── Middleware/
│   │       ├── MiddlewareInterface.php
│   │       ├── MiddlewarePipeline.php
│   │       ├── MiddlewareRegistry.php
│   │       ├── AuthMiddleware.php
│   │       ├── GuestMiddleware.php
│   │       ├── CsrfMiddleware.php
│   │       └── RequestLoggerMiddleware.php
│   ├── Routing/
│   │   ├── Router.php
│   │   ├── MatchedRoute.php
│   │   └── ControllerResolver.php
│   ├── View/
│   │   └── View.php
│   └── Exceptions/
│       ├── HttpNotFoundException.php
│       ├── HttpMethodNotAllowedException.php
│       └── ViewNotFoundException.php
├── bootstrap/
│   └── app.php                 # Wires everything together
├── database/
│   └── migrations/             # SQL migration files
├── routes/
│   └── web.php                 # Route definitions
├── public/
│   └── index.php               # Entry point
├── storage/
│   └── logs/                   # Request logs written here
├── .env                        # Environment values (never commit)
├── .env.example                # Safe template to commit
└── composer.json
```

---

## Configuration

All configuration is driven by `.env`. Copy `.env.example` and fill in your values:

```ini
# Application
APP_NAME="My App"
APP_ENV=local
APP_DEBUG=true

# Database
DB_DRIVER=sqlite          # sqlite | mysql | pgsql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=database/db.sqlite
DB_USERNAME=
DB_PASSWORD=
```

Constants available everywhere after bootstrap:

| Constant | Path |
|---|---|
| `BASE_PATH` | Project root |
| `APP_PATH` | `{root}/app` |
| `CONFIG_PATH` | `{root}/app/config` |
| `VIEWS_PATH` | `{root}/app/views` |
| `STORAGE_PATH` | `{root}/storage` |

---

## Routing

Routes are defined in `routes/web.php` as a closure that receives a `Router` instance:

```php
<?php

use App\Controllers\HomeController;
use App\Controllers\DashboardController;

return function (Etus\Framework\Routing\Router $router): void {

    // Basic route
    $router->get('/', [HomeController::class, 'index']);

    // Route with middleware
    $router->get('/dashboard', [DashboardController::class, 'index'], ['auth']);

    // Route with multiple middleware
    $router->post('/login', [AuthController::class, 'store'], ['guest', 'csrf']);

    // Route with a URI parameter
    $router->get('/users/{id}', [UserController::class, 'show']);

    // All HTTP methods supported
    $router->post('/users',        [UserController::class, 'store']);
    $router->put('/users/{id}',    [UserController::class, 'update']);
    $router->delete('/users/{id}', [UserController::class, 'destroy']);
};
```

URI parameters are passed as arguments to the controller method in the order they appear:

```php
public function show(string $id): Response
{
    $user = (new User)->find($id);
    return view('users.show', ['user' => $user]);
}
```

---

## Middleware

Middleware can be applied at four levels, executed in this order:

```
Global → Route → Controller → Method → Controller action runs
```

### Built-in middleware

| Alias | Class | Description |
|---|---|---|
| `auth` | `AuthMiddleware` | Redirects unauthenticated users to `/login` |
| `guest` | `GuestMiddleware` | Redirects authenticated users to `/` |
| `csrf` | `CsrfMiddleware` | Validates CSRF token on POST/PUT/PATCH/DELETE |
| `log` | `RequestLoggerMiddleware` | Logs method, URI, status, and duration to `storage/logs/requests.log` |

### 1. Global — every request

Add to `Kernel::$middleware` in `framework/Http/Kernel.php`:

```php
protected array $middleware = ['log'];
```

### 2. Per route

Third argument in `routes/web.php`:

```php
$router->get('/dashboard', [DashboardController::class, 'index'], ['auth', 'log']);
```

### 3. Per controller

All methods on the controller are protected:

```php
class DashboardController extends BaseController
{
    protected array $middleware = ['auth'];
}
```

### 4. Per method

Fine-grained control using `$middlewareOnly` and `$middlewareExcept`:

```php
class PostController extends BaseController
{
    protected array $middleware = ['auth'];

    // Only run 'auth' on these methods
    protected array $middlewareOnly = [
        'auth' => ['store', 'update', 'destroy'],
    ];

    // Run 'log' everywhere except index
    protected array $middlewareExcept = [
        'log' => ['index'],
    ];
}
```

### Creating custom middleware

Implement `MiddlewareInterface` and register an alias in `bootstrap/app.php`:

```php
// framework/Http/Middleware/ThrottleMiddleware.php
class ThrottleMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        // Before the controller...
        $response = $next();
        // After the controller...
        return $response;
    }
}

// bootstrap/app.php
MiddlewareRegistry::register('throttle', ThrottleMiddleware::class);
```

---

## Controllers

Controllers live in `app/Controllers/` and extend `BaseController`:

```php
<?php

namespace App\Controllers;

use App\Models\User;
use Etus\Framework\Http\Response;

class UserController extends BaseController
{
    protected array $middleware = ['auth'];

    public function index(): Response
    {
        return view('users.index', [
            'title' => 'Users',
            'users' => (new User)->all(),
        ]);
    }

    public function show(string $id): Response
    {
        $user = (new User)->findOrFail($id);
        return view('users.show', ['user' => $user]);
    }

    public function store(): Response
    {
        (new User)->create([
            'name'  => $this->request->input('name'),
            'email' => $this->request->input('email'),
        ]);

        return redirect('/users');
    }
}
```

### Request methods

```php
$this->request->input('name');           // POST body value
$this->request->query('page');           // GET query param
$this->request->file('avatar');          // Uploaded file
$this->request->cookie('token');         // Cookie value
$this->request->server('REMOTE_ADDR');  // Server variable
$this->request->getMethod();             // HTTP method
$this->request->getUri();                // Request URI
```

---

## Views

Templates live in `app/views/`. Dot notation maps to directory separators:

```php
view('home.index')       // → app/views/home/index.php
view('users.show')       // → app/views/users/show.php
view('welcome')          // → app/views/welcome.php
```

### Basic template

Declare a layout with `$layout`. The template's output becomes `$content` in the layout:

```php
<?php $layout = 'app'; ?>

<h1>Hello, <?= htmlspecialchars($name) ?>!</h1>
<p>Welcome back.</p>
```

### Layout file — `app/views/layouts/app.php`

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= htmlspecialchars($title ?? 'App') ?></title>
</head>
<body>
    <?= $content ?>
</body>
</html>
```

### Passing data

```php
return view('users.index', [
    'title' => 'All Users',
    'users' => $users,
]);
```

Each key in the array becomes a local variable in the template (`$title`, `$users`).

### No layout

Omit `$layout` entirely to render without a wrapper:

```php
<?php // No $layout declaration ?>
<p>Plain output, no layout.</p>
```

---

## Models

Models live in `app/Models/` and extend `Etus\Framework\Database\Model`:

```php
<?php

namespace App\Models;

use Etus\Framework\Database\Model;

class User extends Model
{
    protected string $table      = 'users';
    protected string $primaryKey = 'id';
    protected bool   $timestamps  = true;   // created_at / updated_at
    protected bool   $auditFields = true;   // created_by / updated_by
    protected bool   $logging     = true;   // activity_logs table

    // Custom query methods
    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    public function active(): array
    {
        return $this->where('is_active', 1)->get();
    }
}
```

### CRUD

```php
$user = new User;

// Read
$all   = $user->all();
$one   = $user->find(1);
$one   = $user->findOrFail(1);   // throws if not found

// Create
$id = $user->create([
    'name'  => 'Mark',
    'email' => 'mark@example.com',
]);

// Update
$user->update(1, ['name' => 'Mark Arnold']);

// Delete
$user->delete(1);
```

### Query builder

```php
(new User)
    ->where('is_active', 1)
    ->where('created_at', '>', '2025-01-01')
    ->orderBy('name', 'ASC')
    ->limit(10)
    ->offset(20)
    ->get();

// First result only
(new User)->where('email', 'mark@example.com')->first();

// Count
(new User)->where('is_active', 1)->count();
```

---

## Database

### Switching drivers

Change `DB_DRIVER` in `.env` — everything else is automatic:

```ini
# SQLite
DB_DRIVER=sqlite
DB_DATABASE=database/db.sqlite

# MySQL
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mydb
DB_USERNAME=root
DB_PASSWORD=secret
```

### Raw queries

Use `Connection::getInstance()` directly when you need raw SQL:

```php
use Etus\Framework\Database\Connection;

$db = Connection::getInstance();

$rows = $db->select('SELECT * FROM users WHERE role = ?', ['admin']);
$row  = $db->selectOne('SELECT * FROM users WHERE id = ?', [1]);
$id   = $db->insert('INSERT INTO users (name, email) VALUES (?, ?)', ['Mark', 'mark@example.com']);
$n    = $db->execute('UPDATE users SET active = 1 WHERE id = ?', [1]);
```

### Migrations

SQL migration files live in `database/migrations/`. Run them manually with sqlite3 or your DB client:

```bash
sqlite3 database/db.sqlite < database/migrations/001_create_users_and_activity_logs.sql
```

---

## Helpers

Global functions available everywhere via Composer's `files` autoload:

| Function | Description |
|---|---|
| `view(string $template, array $data, int $status)` | Render a template and return a Response |
| `redirect(string $uri, int $status)` | Return a redirect Response |
| `csrf_token()` | Return the current CSRF token string |
| `csrf_field()` | Return a hidden `<input>` HTML element with the CSRF token |
| `env(string $key, mixed $default)` | Read a typed value from the environment |

### Usage in views

```php
<form method="POST" action="/users">
    <?= csrf_field() ?>
    <input type="text" name="name">
    <button type="submit">Save</button>
</form>
```

### Usage in controllers

```php
return redirect('/dashboard');
return view('home.index', ['title' => 'Home']);
```

---

## Activity Logging

When `$logging = true` on a model, every `create`, `update`, and `delete` is written to the `activity_logs` table automatically.

The `activity_logs` table schema:

```sql
CREATE TABLE activity_logs (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    action     TEXT NOT NULL,       -- 'create' | 'update' | 'delete'
    model      TEXT NOT NULL,       -- fully qualified class name
    record_id  TEXT,                -- primary key of the affected row
    payload    TEXT,                -- JSON snapshot of written data
    user_id    INTEGER,             -- who performed the action (from $_SESSION)
    created_at TEXT NOT NULL
);
```

To disable logging on a specific model:

```php
protected bool $logging = false;
```

---

## Changelog

### v1.0.0 — 2026-02-20

Initial release.

**Framework core**
- `Kernel` — thin HTTP kernel with try/catch HTTP error handling
- `Request` — singleton capturing all superglobals with typed accessors
- `Response` — side-effect-free until `send()` is called
- `Router` — FastRoute-backed with per-route middleware support
- `MatchedRoute` — value object carrying handler, vars, and middleware
- `ControllerResolver` — assembles and runs the full middleware pipeline

**Middleware**
- `MiddlewareInterface` — single `handle()` contract
- `MiddlewarePipeline` — chain-of-responsibility runner via `array_reduce`
- `MiddlewareRegistry` — alias-to-class map for clean string references
- `AuthMiddleware` — redirects unauthenticated users
- `GuestMiddleware` — redirects authenticated users
- `CsrfMiddleware` — token validation with automatic rotation
- `RequestLoggerMiddleware` — logs every request to `storage/logs/requests.log`

**Database**
- `Connection` — singleton PDO wrapper with driver-specific options
- `Model` — abstract base with CRUD, timestamps, audit fields, and logging flags
- `QueryBuilder` — fluent `where/orderBy/limit/offset/get/first/count` API
- `ActivityLogger` — writes audit records to `activity_logs`

**Views**
- `View` — PHP template renderer with layout injection via `$layout` variable
- `view()` — global helper returning a `Response`

**Helpers**
- `env()` — typed environment value reader
- `view()` — template renderer helper
- `redirect()` — redirect Response helper
- `csrf_token()` / `csrf_field()` — CSRF token helpers

---

*Built by ETUS[(Ephraitech Unified Solutions Ltd)](htttps://ephraitech) — info@ephraitech.com*
