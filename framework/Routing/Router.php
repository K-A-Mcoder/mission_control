<?php

namespace Etus\Framework\Routing;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Etus\Framework\Http\Request;
use Etus\Framework\Exceptions\HttpNotFoundException;
use Etus\Framework\Exceptions\HttpMethodNotAllowedException;

use function FastRoute\simpleDispatcher;

class Router
{
    protected array $routes = [];

    public function get(string $uri, array $handler, array $middleware = []): void
    {
        $this->add('GET', $uri, $handler, $middleware);
    }

    public function post(string $uri, array $handler, array $middleware = []): void
    {
        $this->add('POST', $uri, $handler, $middleware);
    }

    public function put(string $uri, array $handler, array $middleware = []): void
    {
        $this->add('PUT', $uri, $handler, $middleware);
    }

    public function delete(string $uri, array $handler, array $middleware = []): void
    {
        $this->add('DELETE', $uri, $handler, $middleware);
    }

    public function add(string $method, string $uri, array $handler, array $middleware = []): void
    {
        $this->routes[] = [$method, $uri, ['handler' => $handler, 'middleware' => $middleware]];
    }

    public function matchOLD(Request $request): MatchedRoute
    {
        $dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) {
            foreach ($this->routes as $route) {
                $routeCollector->addRoute(...$route);
            }
        });

        $routeInfo = $dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri(),
        );

        return match ($routeInfo[0]) {
            Dispatcher::NOT_FOUND          => throw new HttpNotFoundException(),
            Dispatcher::METHOD_NOT_ALLOWED => throw new HttpMethodNotAllowedException(),
            Dispatcher::FOUND              => new MatchedRoute(
                handler: $routeInfo[1]['handler'],
                vars: $routeInfo[2],
                middleware: $routeInfo[1]['middleware'],
            ),
        };
    }

    public function match(Request $request): MatchedRoute
    {
        $dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) {
            foreach ($this->routes as $route) {
                $routeCollector->addRoute(...$route);
            }
        });

        // 👇 Strip query string
        $uri = $request->getUri();

        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        $uri = rawurldecode($uri);

        $routeInfo = $dispatcher->dispatch(
            $request->getMethod(),
            $uri
        );

        return match ($routeInfo[0]) {
            Dispatcher::NOT_FOUND          => throw new HttpNotFoundException(),
            Dispatcher::METHOD_NOT_ALLOWED => throw new HttpMethodNotAllowedException(),
            Dispatcher::FOUND              => new MatchedRoute(
                handler: $routeInfo[1]['handler'],
                vars: $routeInfo[2],
                middleware: $routeInfo[1]['middleware'],
            ),
        };
    }
}

// namespace Etus\Framework\Routing;

// use FastRoute\Dispatcher;
// use FastRoute\RouteCollector;
// use Etus\Framework\Http\Request;
// use Etus\Framework\Exceptions\HttpNotFoundException;
// use Etus\Framework\Exceptions\HttpMethodNotAllowedException;

// use function FastRoute\simpleDispatcher;

// class Router
// {
//     protected array $routes = [];

//     public function get(string $uri, array $handler): void
//     {
//         $this->add('GET', $uri, $handler);
//     }

//     public function post(string $uri, array $handler): void
//     {
//         $this->add('POST', $uri, $handler);
//     }

//     public function put(string $uri, array $handler): void
//     {
//         $this->add('PUT', $uri, $handler);
//     }

//     public function delete(string $uri, array $handler): void
//     {
//         $this->add('DELETE', $uri, $handler);
//     }

//     public function add(string $method, string $uri, array $handler): void
//     {
//         $this->routes[] = [$method, $uri, $handler];
//     }

//     public function match(Request $request): MatchedRoute
//     {
//         $dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) {
//             foreach ($this->routes as $route) {
//                 $routeCollector->addRoute(...$route);
//             }
//         });

//         $routeInfo = $dispatcher->dispatch(
//             $request->getMethod(),
//             $request->getUri(),
//         );

//         return match ($routeInfo[0]) {
//             Dispatcher::NOT_FOUND       => throw new HttpNotFoundException(),
//             Dispatcher::METHOD_NOT_ALLOWED => throw new HttpMethodNotAllowedException(),
//             Dispatcher::FOUND           => new MatchedRoute($routeInfo[1], $routeInfo[2]),
//         };
//     }
// }
