<?php

namespace Etus\Framework\Http\Middleware;

use Etus\Framework\Http\Request;
use Etus\Framework\Http\Response;

interface MiddlewareInterface
{
    /**
     * Handle the request, optionally passing it to the next middleware.
     *
     * @param callable(): Response $next
     */
    public function handle(Request $request, callable $next): Response;
}
