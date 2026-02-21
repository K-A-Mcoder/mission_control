<?php

use Etus\Framework\View\View;
use Etus\Framework\Http\Response;
use Etus\Framework\Http\Middleware\CsrfMiddleware;

if (! function_exists('view')) {
    /**
     * Render a view template and return a Response.
     *
     * Dot notation maps to directory separators:
     *   view('home.index') → app/views/home/index.php
     *
     * @param  array<string, mixed> $data
     */
    function view(string $template, array $data = [], int $status = 200): Response
    {
        $content = View::render($template, $data);

        return new Response($content, $status);
    }
}

if (! function_exists('csrf_token')) {
    /**
     * Return the current CSRF token, generating one if it doesn't exist.
     * Use inside forms: <input type="hidden" name="_token" value="<?= csrf_token () ?>">
     * @example Use inside forms `<input type="hidden" name="_token" value="<?= csrf_token () ?>">`
     */
    function csrf_token(): string
    {
        return CsrfMiddleware::generateToken();
    }
}

if (! function_exists('csrf_field')) {
    /**
     * Return a hidden HTML input field containing the CSRF token.
     * Use inside forms: <?= csrf_field() ?>
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (! function_exists('redirect')) {
    /**
     * Return a redirect Response.
     *
     * Usage: return redirect('/dashboard');
     */
    function redirect(string $uri, int $status = 302): Response
    {
        return new Response('', $status, ['Location' => $uri]);
    }
}

if (! function_exists('env')) {
    /**
     * Get a value from the environment, with an optional default.
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        return match (strtolower($value)) {
            'true',  '(true)'  => true,
            'false', '(false)' => false,
            'null',  '(null)'  => null,
            'empty', '(empty)' => '',
            default            => $value,
        };
    }
}
