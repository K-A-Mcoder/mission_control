<?php

namespace Etus\Framework\Routing;

class MatchedRoute
{
    public function __construct(
        public readonly array $handler,
        public readonly array $vars,
        public readonly array $middleware = [],
    ) {}
}
