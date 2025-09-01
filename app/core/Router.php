<?php

namespace Jeffrey\Educore\Core;

use FastRoute\RouteCollector;
use FastRoute\Dispatcher;

class Router
{
    private $dispatcher;

    public function __construct(string $routesFile)
    {
        $this->dispatcher = \FastRoute\simpleDispatcher(function (RouteCollector $r) use ($routesFile) {
            require $routesFile;
        });
    }

    public function dispatch(string $httpMethod, string $uri): void
    {
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                http_response_code(404);
                echo "404 Not Found";
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:
                http_response_code(405);
                echo "405 Method Not Allowed";
                break;

            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                $middleware = $handler['middleware'] ?? [];
                $controllerHandler = $handler['handler'];

                $this->handleWithMiddleware($middleware, function () use ($controllerHandler, $vars) {
                    $this->dispatchToHandler($controllerHandler, $vars);
                });
                break;
        }
    }

    private function handleWithMiddleware(array $middleware, callable $callback): void
    {
        $chain = $callback;

        foreach (array_reverse($middleware) as $mw) {
            $chain = function () use ($mw, $chain) {
                $class = $mw['class'];
                $method = $mw['method'];
                $args = $mw['args'] ?? [];

                if (call_user_func_array([$class, $method], $args)) {
                    $chain();
                }
            };
        }

        $chain();
    }

    private function dispatchToHandler($handler, array $vars): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, array_values($vars));
        } elseif (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $controller = new $class();
            call_user_func_array([$controller, $method], array_values($vars));
        } else {
            http_response_code(500);
            echo "Invalid route handler.";
        }
    }
}