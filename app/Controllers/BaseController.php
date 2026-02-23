<?php

namespace App\Controllers;

use Etus\Framework\Controllers\AbstractController;
use Etus\Framework\Http\Response;

/**
 * BaseController
 *
 * Application-level base controller. All app controllers extend this.
 * Provides access to the request object and any shared helpers needed
 * across the application layer.
 *
 * Framework-level concerns (middleware lists, request injection, response
 * dispatching) are handled by AbstractController in the framework namespace.
 */
abstract class BaseController extends AbstractController
{
    // ── Shared helpers available to every controller ──────────────────────────

    /**
     * Shortcut: redirect to a URL.
     */
    protected function redirect(string $uri, int $status = 302): \Etus\Framework\Http\Response
    {
        return new Response('', $status, ['Location' => $uri]);
    }

    /**
     * Shortcut: render a view (delegates to global view() helper).
     *
     * @param array<string, mixed> $data
     */
    protected function view(string $template, array $data = []): \Etus\Framework\Http\Response
    {
        return view($template, $data);
    }

    /**
     * Return the currently authenticated user's ID from the session.
     */
    protected function authId(): string|int
    {
        return ($_SESSION['user_id'] ?? 0);
    }

    /**
     * Return the currently authenticated user's role from the session.
     */
    protected function authRole(): string
    {
        return (string) ($_SESSION['role'] ?? '');
    }
}
