<?php

// namespace Etus\Framework\Http;

// use Etus\Framework\Routing\Router;
// use Etus\Framework\Routing\ControllerResolver;
// use Etus\Framework\Database\Connection;
// use Etus\Framework\Exceptions\HttpNotFoundException;
// use Etus\Framework\Exceptions\HttpMethodNotAllowedException;

// class Kernel
// {
//     public function __construct(
//         private readonly Router $router,
//         private readonly Connection $connection,
//     ) {}

//     public function handle(Request $request): Response
//     {
//         try {
//             $route = $this->router->match($request);

//             return ControllerResolver::dispatch($route, $request);
//         } catch (HttpNotFoundException $e) {
//             return new Response('Not Found', 404);
//         } catch (HttpMethodNotAllowedException $e) {
//             return new Response('Method Not Allowed', 405);
//         }
//     }
// }


namespace Etus\Framework\Http;

use Etus\Framework\Routing\Router;
use Etus\Framework\Routing\ControllerResolver;
use Etus\Framework\Database\Connection;
use Etus\Framework\Exceptions\HttpNotFoundException;
use Etus\Framework\Exceptions\HttpMethodNotAllowedException;

class Kernel
{
    /**
     * Global middleware applied to every request.
     * Add aliases registered in MiddlewareRegistry here.
     *
     * @var array<int, string>
     */
    protected array $middleware = [];

    public function __construct(
        private readonly Router $router,
        private readonly Connection $connection,
    ) {}

    public function handle(Request $request): Response
    {
        try {
            $route = $this->router->match($request);

            return ControllerResolver::dispatch($route, $request, $this->middleware);
        } catch (HttpNotFoundException $e) {
            return new Response('Not Found', 404);
        } catch (HttpMethodNotAllowedException $e) {
            return new Response('Method Not Allowed', 405);
        }
    }
}