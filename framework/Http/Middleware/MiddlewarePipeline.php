<?php

namespace Etus\Framework\Http\Middleware;

use Etus\Framework\Http\Request;
use Etus\Framework\Http\Response;

class MiddlewarePipeline
{
    /** @var array<int, MiddlewareInterface> */
    private array $middleware = [];

    /**
     * Add a middleware instance to the pipeline.
     */
    public function pipe(MiddlewareInterface $middleware): static
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    /**
     * Add multiple middleware instances at once.
     *
     * @param array<int, MiddlewareInterface> $middleware
     */
    public function pipeMany(array $middleware): static
    {
        foreach ($middleware as $m) {
            $this->pipe($m);
        }

        return $this;
    }

    /**
     * Run the pipeline, passing the request through each middleware in order,
     * and finally calling $destination to produce the response.
     *
     * @param callable(): Response $destination
     */
    public function run(Request $request, callable $destination): Response
    {
        $chain = array_reduce(
            array_reverse($this->middleware),
            fn(callable $carry, MiddlewareInterface $middleware)
            => fn() => $middleware->handle($request, $carry),
            $destination,
        );

        return $chain();
    }
}
