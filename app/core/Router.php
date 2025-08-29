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
        // Strip query string and decode URL
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                // Handle 404 Not Found
                http_response_code(404);
                echo "404 Not Found";
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                http_response_code(405);
                echo "405 Method Not Allowed";
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $middleware = $handler['middleware'] ?? [];
                $controllerHandler = $handler['handler'];

                $this->handleWithMiddleware($middleware, function() use ($controllerHandler, $vars) {
                    $this->dispatchToController($controllerHandler, $vars);
                });
                break;
        }
    }

    private function handleWithMiddleware(array $middleware, callable $callback)
    {
        // Recursively handle middleware chain
        if (empty($middleware)) {
            $callback();
            return;
        }

        $currentMiddlewareClass = array_shift($middleware);
        $currentMiddleware = new $currentMiddlewareClass();

        $currentMiddleware->handle(function() use ($middleware, $callback) {
            $this->handleWithMiddleware($middleware, $callback);
        });
    }

    private function dispatchToController($handler, array $vars)
    {
        // Handle the string format: 'Controller@method'
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
        }
        // Handle the new array format: [Controller::class, 'method']
        elseif (is_array($handler) && count($handler) === 2) {
            $controllerClass = $handler[0];
            $methodName = $handler[1];

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