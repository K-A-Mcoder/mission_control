<?php

use Etus\Framework\View\View;
use Etus\Framework\Http\Response;
use Etus\Framework\Http\Flash;
use Etus\Framework\Auth\Auth;
use Etus\Framework\Auth\Gate;
use Etus\Framework\Http\Middleware\CsrfMiddleware;

// ── Views ────────────────────────────────────────────────────────────────────

if (! function_exists('view')) {
    /**
     * Render a full page template and return a Response.
     *   view('home.index') → app/views/home/index.php
     *
     * @param array<string, mixed> $data
     */
    function view(string $template, array $data = [], int $status = 200): Response
    {
        return new Response(View::render($template, $data), $status);
    }
}

if (! function_exists('partial')) {
    /**
     * Render a partial template and return its HTML string.
     *   <?= partial('partials.logout-btn') ?>
     *   <?= partial('partials.nav', ['user' => auth_user()]) ?>
     *
     * @param array<string, mixed> $data
     */
    function partial(string $template, array $data = []): string
    {
        return View::render($template, $data);
    }
}

// ── Auth ─────────────────────────────────────────────────────────────────────

if (! function_exists('auth_check')) {
    /** Returns true if a user is logged in. */
    function auth_check(): bool
    {
        return Auth::check();
    }
}

if (! function_exists('auth_guest')) {
    /** Returns true if no user is logged in. */
    function auth_guest(): bool
    {
        return Auth::guest();
    }
}

if (! function_exists('auth_user')) {
    /**
     * Return the current user's session data as an array, or null.
     *
     * @return array{user_id: int, name: string, email: string, role: string}|null
     */
    function auth_user(): ?array
    {
        return Auth::user();
    }
}

if (! function_exists('auth_id')) {
    /** Return the current user's ID, or null. */
    function auth_id(): ?int
    {
        return Auth::id();
    }
}

if (! function_exists('auth_role')) {
    /** Return the current user's role name, or null. */
    function auth_role(): ?string
    {
        return Auth::role();
    }
}

// ── Permissions ───────────────────────────────────────────────────────────────

if (! function_exists('can')) {
    /**
     * Check if the current user has a permission.
     *   @if (can('edit-users')) ... @endif
     *   <?php if (can('view-reports')): ?> ... <?php endif; ?>
     */
    function can(string $permission): bool
    {
        return Gate::can($permission);
    }
}

if (! function_exists('cannot')) {
    /** Inverse of can(). */
    function cannot(string $permission): bool
    {
        return Gate::cannot($permission);
    }
}

if (! function_exists('can_any')) {
    /**
     * Check if the current user has ANY of the given permissions.
     *   <?php if (can_any(['edit-users', 'view-users'])): ?>
     *
     * @param array<int, string> $permissions
     */
    function can_any(array $permissions): bool
    {
        return Gate::canAny($permissions);
    }
}

if (! function_exists('can_all')) {
    /**
     * Check if the current user has ALL of the given permissions.
     *
     * @param array<int, string> $permissions
     */
    function can_all(array $permissions): bool
    {
        return Gate::canAll($permissions);
    }
}

// ── Roles ─────────────────────────────────────────────────────────────────────

if (! function_exists('has_role')) {
    /**
     * Check if the current user has a specific role.
     *   <?php if (has_role('admin')): ?>
     */
    function has_role(string $role): bool
    {
        return Gate::hasRole($role);
    }
}

if (! function_exists('has_any_role')) {
    /**
     * Check if the current user has any of the given roles.
     *   <?php if (has_any_role(['admin', 'manager'])): ?>
     *
     * @param array<int, string> $roles
     */
    function has_any_role(array $roles): bool
    {
        return Gate::hasAnyRole($roles);
    }
}

// ── Flash messages ────────────────────────────────────────────────────────────

if (! function_exists('flash')) {
    /**
     * Set a flash message.
     *   flash('error',   'Something went wrong.');
     *   flash('success', 'Saved!');
     *   flash('info',    'Please note this.');
     */
    function flash(string $type, string $message): void
    {
        Flash::set($type, $message);
    }
}

if (! function_exists('flash_success')) {
    function flash_success(string $message): void
    {
        Flash::success($message);
    }
}

if (! function_exists('flash_error')) {
    function flash_error(string $message): void
    {
        Flash::error($message);
    }
}

if (! function_exists('flash_info')) {
    function flash_info(string $message): void
    {
        Flash::info($message);
    }
}

if (! function_exists('get_flash')) {
    /**
     * Read and immediately destroy the current flash message.
     * Returns null if nothing is waiting.
     *
     * @return array{type: string, msg: string}|null
     */
    function get_flash(): ?array
    {
        return Flash::get();
    }
}

// ── CSRF ──────────────────────────────────────────────────────────────────────

if (! function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return CsrfMiddleware::generateToken();
    }
}

if (! function_exists('csrf_field')) {
    /** Return a hidden <input> with the CSRF token. Use inside any form. */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

// ── HTTP ──────────────────────────────────────────────────────────────────────

if (! function_exists('redirect')) {
    /**
     * Return a redirect Response.
     *   return redirect('/dashboard');
     */
    function redirect(string $uri, int $status = 302): Response
    {
        return new Response('', $status, ['Location' => $uri]);
    }
}

// ── Environment ───────────────────────────────────────────────────────────────

if (! function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        return match (strtolower((string) $value)) {
            'true',  '(true)'  => true,
            'false', '(false)' => false,
            'null',  '(null)'  => null,
            'empty', '(empty)' => '',
            default            => $value,
        };
    }
}


// ── uuid ────────────────────────────────────────────────────────────────────

if (! function_exists('uuid')) {
    /**
     * Generate a UUID v4
     *
     * @return string
     */
    function uuid(): string
    {
        // Generate 16 random bytes
        $data = random_bytes(16);

        // Set version to 0100 (UUID v4)
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);

        // Set variant to 10xx
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        // Format as UUID string
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

if (! function_exists('log_error')) {
    /**
     * Log an error message to a file.
     *
     * @param string $message The error message to log
     * @param string|null $file Optional file name (defaults to 'app.log')
     */
    function log_error(string $message, ?string $file = null): void
    {
        $logFile = __DIR__ . '/../../logs/' . ($file ?? 'app.log');
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[$timestamp] ERROR: $message" . PHP_EOL;
        file_put_contents($logFile, $formattedMessage, FILE_APPEND);
    }
}

if(! function_exists('setting')) {
    /**
     * Get a setting value by key, with optional default.
     *
     * @param string $key The setting key (e.g. 'app.name')
     * @param mixed $default The default value if the setting is not found
     * @return mixed The setting value or default
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return \App\Models\Setting::get($key, $default);
    }
}
