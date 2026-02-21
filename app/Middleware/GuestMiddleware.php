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
        if (!empty($_SESSION['user_id'])) {
            return new Response('', 302, ['Location' => '/']);
        }

        $response = $next();

        // After the controller runs...

        return $response;
    }
}
