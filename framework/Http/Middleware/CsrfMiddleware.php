<?php

namespace Etus\Framework\Http\Middleware;

use Etus\Framework\Http\Request;
use Etus\Framework\Http\Response;

class CsrfMiddleware implements MiddlewareInterface
{
    /** HTTP methods that mutate state and require a CSRF token. */
    private const GUARDED_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, callable $next): Response
    {
        if (in_array($request->getMethod(), self::GUARDED_METHODS, strict: true)) {
            $token        = $request->input('_token') ?? $request->server('HTTP_X_CSRF_TOKEN');
            $sessionToken = $_SESSION['_csrf_token'] ?? null;

            if (! $token || ! $sessionToken || ! hash_equals($sessionToken, $token)) {
                return new Response('CSRF token mismatch.', 419);
            }
        }

        // Rotate the token after every validated request.
        $this->regenerateToken();

        return $next();
    }

    /**
     * Generate and store a new CSRF token, returning it.
     * Call this from your layout to embed the token in forms.
     */
    public static function generateToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            self::regenerateToken();
        }

        return $_SESSION['_csrf_token'];
    }

    private static function regenerateToken(): void
    {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
}
