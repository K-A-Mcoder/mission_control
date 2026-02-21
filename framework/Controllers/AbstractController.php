<?php

// namespace Etus\Framework\Controllers;

// use Etus\Framework\Http\Request;

// class AbstractController
// {
//     protected ?Request $request = null;

//     public function setRequest(Request $request): void
//     {
//         $this->request = $request;
//     }
// }


namespace Etus\Framework\Controllers;

use Etus\Framework\Http\Request;

abstract class AbstractController
{
    protected ?Request $request = null;

    /**
     * Middleware applied to every method on this controller.
     *
     * Override in your controller:
     *   protected array $middleware = ['auth'];
     *
     * @var array<int, string>
     */
    protected array $middleware = [];

    /**
     * Per-method middleware overrides.
     *
     * Override in your controller:
     *   protected array $middlewareOnly = [
     *       'auth' => ['store', 'update', 'destroy'],
     *   ];
     *
     * @var array<string, array<int, string>>
     */
    protected array $middlewareOnly = [];

    /**
     * Per-method middleware exclusions.
     *
     * Override in your controller:
     *   protected array $middlewareExcept = [
     *       'guest' => ['index', 'show'],
     *   ];
     *
     * @var array<string, array<int, string>>
     */
    protected array $middlewareExcept = [];

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * Resolve the middleware that applies to a specific method.
     *
     * @return array<int, string>
     */
    public function getMiddlewareForMethod(string $method): array
    {
        $resolved = [];

        foreach ($this->middleware as $alias) {
            // Skip if this method is excluded.
            if (isset($this->middlewareExcept[$alias])
                && in_array($method, $this->middlewareExcept[$alias], strict: true)) {
                continue;
            }

            // Skip if an `only` restriction exists and this method is not in it.
            if (isset($this->middlewareOnly[$alias])
                && ! in_array($method, $this->middlewareOnly[$alias], strict: true)) {
                continue;
            }

            $resolved[] = $alias;
        }

        return $resolved;
    }
}

