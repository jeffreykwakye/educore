<?php
declare(strict_types=1);

namespace Jeffrey\Educore\Core;

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use FastRoute\Dispatcher;

class Router
{
    private $dispatcher;

    public function __construct(string $routesFile)
    {
        $this->dispatcher = simpleDispatcher(function (RouteCollector $r) use ($routesFile) {
            if (file_exists($routesFile)) {
                require $routesFile;
            } else {
                throw new \Exception("Routes file not found at: {$routesFile}");
            }
        });
    }

    public function dispatch(string $httpMethod, string $uri)
    {
        // Strip query string and decode URI
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
                // Handle 405 Method Not Allowed
                http_response_code(405);
                echo "405 Method Not Allowed";
                break;
             case Dispatcher::FOUND:
                $routeHandler = $routeInfo[1];
                $vars = $routeInfo[2];

                // Check if a middleware is defined
                if (is_array($routeHandler) && isset($routeHandler['middleware'])) {
                    $handler = $routeHandler['handler'];
                    $middlewares = $routeHandler['middleware'];
                    
                    // Run the middleware stack
                    $pipeline = array_reverse($middlewares);
                    $next = function () use ($handler, $vars) {
                        return $this->dispatchToController($handler, $vars);
                    };

                    foreach ($pipeline as $middleware) {
                        $instance = new $middleware();
                        $next = function () use ($instance, $next) {
                            return $instance->handle($next);
                        };
                    }
                    
                    $next();

                } else {
                    // No middleware, dispatch directly to the controller
                    $this->dispatchToController($routeHandler, $vars);
                }

                break;
        }
    }


    /**
     * Dispatches the request to the controller method.
     */
    private function dispatchToController(string $handler, array $vars)
    {
        list($controllerName, $method) = explode('@', $handler);
        $controllerClass = "Jeffrey\\Educore\\Controllers\\{$controllerName}";

        if (!class_exists($controllerClass)) {
            http_response_code(500);
            echo "Internal Server Error: Controller not found.";
            return;
        }

        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            http_response_code(500);
            echo "Internal Server Error: Method not found.";
            return;
        }

        call_user_func_array([$controller, $method], $vars);
    }
}