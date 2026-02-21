<?php

namespace Etus\Framework\Http\Middleware;

use Etus\Framework\Auth\Gate;
use Etus\Framework\Http\Request;
use Etus\Framework\Http\Response;
use Etus\Framework\Http\Flash;

class PermissionMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly string $permission) {}

    public function handle(Request $request, callable $next): Response
    {
        if (Gate::cannot($this->permission)) {
            Flash::error('You do not have permission to access that page.');

            return new Response('', 302, ['Location' => '/']);
        }

        return $next();
    }
}
