<?php

namespace App\Middleware;

use Etus\Framework\Http\Middleware\MiddlewareInterface;
use Etus\Framework\Http\Request;
use Etus\Framework\Http\Response;

class AuthMiddleware implements MiddlewareInterface
{
    /**
     * Redirect to login if the user is not authenticated.
     * Adjust the session key and redirect path to match your auth system.
     */
    public function handle(Request $request, callable $next): Response
    {
        if (empty($_SESSION['user_id'])) {
            return new Response('', 302, ['Location' => '/login']);
        }

        return $next();
    }
}
