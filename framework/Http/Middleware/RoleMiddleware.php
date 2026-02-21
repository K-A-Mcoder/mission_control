<?php

namespace Etus\Framework\Http\Middleware;

use Etus\Framework\Auth\Gate;
use Etus\Framework\Http\Request;
use Etus\Framework\Http\Response;
use Etus\Framework\Http\Flash;

class RoleMiddleware implements MiddlewareInterface
{
    /** @param array<int, string>|string $roles */
    public function __construct(private readonly array|string $roles) {}

    public function handle(Request $request, callable $next): Response
    {
        $roles = is_array($this->roles) ? $this->roles : [$this->roles];

        if (! Gate::hasAnyRole($roles)) {
            Flash::error('You do not have the required role to access that page.');

            return new Response('', 302, ['Location' => '/']);
        }

        return $next();
    }
}
