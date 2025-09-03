<?php 
declare(strict_types=1);

namespace Jeffrey\Educore\Core;

use FastRoute\RouteCollector;
use FastRoute\Dispatcher;

class Router
{
    private $dispatcher;

    /**
     * Accept one or more route file paths.
     */
    public function __construct(string ...$routesFiles)
    {
        $this->dispatcher = \FastRoute\simpleDispatcher(function (RouteCollector $r) use ($routesFiles) {
            foreach ($routesFiles as $file) {
                if (file_exists($file)) {
                    // Make $r available inside the route file
                    $GLOBALS['r'] = $r;
                    require $file;
                } else {
                    throw new \RuntimeException("Routes file not found: {$file}");
                }
            }
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

                $result = call_user_func_array([$class, $method], $args);

                if ($result === true) {
                    $chain();
                } else {
                    http_response_code(401);
                    echo json_encode(['error' => 'Unauthorized']);
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