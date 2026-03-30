<?php

namespace App\Middleware;

use Etus\Framework\Http\Middleware\MiddlewareInterface;
use Etus\Framework\Http\Request;
use Etus\Framework\Http\Response;


class GuestMiddleware implements MiddlewareInterface
{
    /**
     * Redirect to home if the user is already authenticated.
     * Useful for routes like /login and /register.
     */
    public function handle(Request $request, callable $next): Response
    {
        // Treat user as guest if user_id is absent, zero, or session was
        // just freshly created after a logout (just_logged_out flag).
        $isAuthenticated = isset($_SESSION['user_id'])
            && (int) $_SESSION['user_id'] > 0
            && empty($_SESSION['just_logged_out']);  // ← guard against race

        if ($isAuthenticated) {
            return new Response('', 302, ['Location' => '/dashboard']);
        }

        return $next();
    }
}