<?php

// namespace Etus\Framework\Routing;

// use Etus\Framework\Http\Request;
// use Etus\Framework\Http\Response;
// use Etus\Framework\Controllers\AbstractController;

// class ControllerResolver
// {
//     public static function dispatch(MatchedRoute $route, Request $request): Response
//     {
//         [$controllerClass, $method] = $route->handler;

//         $controller = new $controllerClass;

//         if ($controller instanceof AbstractController) {
//             $controller->setRequest($request);
//         }

//         return call_user_func_array([$controller, $method], $route->vars);
//     }
// }


namespace Etus\Framework\Routing;

use Etus\Framework\Http\Request;
use Etus\Framework\Http\Response;
use Etus\Framework\Controllers\AbstractController;
use Etus\Framework\Http\Middleware\MiddlewarePipeline;
use Etus\Framework\Http\Middleware\MiddlewareRegistry;

class ControllerResolver
{
    /**
     * @param array<int, string> $globalMiddleware
     */
    public static function dispatch(
        MatchedRoute $route,
        Request $request,
        array $globalMiddleware = [],
    ): Response {
        [$controllerClass, $method] = $route->handler;

        $controller = new $controllerClass;

        if ($controller instanceof AbstractController) {
            $controller->setRequest($request);
        }

        // Merge middleware in order: global → route → controller → method
        $controllerMiddleware = $controller instanceof AbstractController
            ? $controller->getMiddlewareForMethod($method)
            : [];

        $allMiddleware = array_merge(
            $globalMiddleware,
            $route->middleware,
            $controllerMiddleware,
        );

        $pipeline = new MiddlewarePipeline();
        $pipeline->pipeMany(MiddlewareRegistry::resolveMany($allMiddleware));

        return $pipeline->run(
            $request,
            fn () => call_user_func_array([$controller, $method], $route->vars),
        );
    }
}