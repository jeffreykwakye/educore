<?php

namespace Jeffrey\Educore\Core;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Whoops\Run as WhoopsRun;
use Whoops\Handler\PrettyPageHandler;

class Router
{
    private $dispatcher;

    public function __construct(string $routesFile)
    {
        $this->dispatcher = \FastRoute\simpleDispatcher(function (RouteCollector $r) use ($routesFile) {
            require $routesFile;
        });
    }

    public function dispatch(string $httpMethod, string $uri)
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
                $rawHandler = $routeInfo[1];
                $vars = $routeInfo[2];

                if (is_array($rawHandler) && isset($rawHandler['handler'])) {
                    $middleware = $rawHandler['middleware'] ?? [];
                    $controllerHandler = $rawHandler['handler'];
                } else {
                    $middleware = [];
                    $controllerHandler = $rawHandler;
                }

                if (!is_string($controllerHandler) &&
                    !(is_array($controllerHandler) && count($controllerHandler) === 2) &&
                    !is_callable($controllerHandler)
                ) {
                    $this->handleError("Unsupported handler type.");
                    return;
                }

                $this->handleWithMiddleware($middleware, function() use ($controllerHandler, $vars) {
                    $this->dispatchToController($controllerHandler, $vars);
                });
                break;
        }
    }

    private function handleWithMiddleware(array $middleware, callable $callback)
    {
        if (empty($middleware)) {
            $callback();
            return;
        }

        $chain = $callback;
        foreach (array_reverse($middleware) as $middlewareClass) {
            $chain = function () use ($middlewareClass, $chain) {
                $middlewareInstance = new $middlewareClass();
                if ($middlewareInstance->handle()) {
                    $chain();
                }
            };
        }

        $chain();
    }

    private function dispatchToController($handler, array $vars)
    {
        if (is_string($handler) && strpos($handler, '@') !== false) {
            list($controllerName, $methodName) = explode('@', $handler, 2);
            $controllerClass = "Jeffrey\\Educore\\Controllers\\" . $controllerName;
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if (method_exists($controller, $methodName)) {
                    call_user_func_array([$controller, $methodName], $vars);
                } else {
                    $this->handleError("Method not found: {$methodName}");
                }
            } else {
                $this->handleError("Controller not found: {$controllerClass}");
            }
        } elseif (is_array($handler) && count($handler) === 2) {
            [$controllerClass, $methodName] = $handler;
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if (method_exists($controller, $methodName)) {
                    call_user_func_array([$controller, $methodName], $vars);
                } else {
                    $this->handleError("Method not found: {$methodName}");
                }
            } else {
                $this->handleError("Controller not found: {$controllerClass}");
            }
        } elseif (is_callable($handler)) {
            call_user_func_array($handler, $vars);
        } else {
            $this->handleError("Invalid handler format.");
        }
    }

    private function handleError(string $message)
    {
        http_response_code(500);
        $whoops = new WhoopsRun();
        $whoops->pushHandler(new PrettyPageHandler());
        $whoops->handleException(new \Exception($message));
        exit;
    }
}