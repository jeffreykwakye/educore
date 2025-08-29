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
                // Dispatch to the controller
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                // Explode handler to get controller and method
                list($controllerName, $method) = explode('@', $handler);
                
                // Add the namespace to the controller name
                $controllerClass = "Jeffrey\\Educore\\Controllers\\{$controllerName}";

                // Check if the controller class exists
                if (!class_exists($controllerClass)) {
                    // Log a warning and show an error page
                    // We'll update this to a custom 500 page later
                    http_response_code(500);
                    echo "Internal Server Error: Controller not found.";
                    return;
                }

                // Create an instance of the controller and call the method
                $controller = new $controllerClass();
                
                // Check if the method exists
                if (!method_exists($controller, $method)) {
                     // Log a warning and show an error page
                    http_response_code(500);
                    echo "Internal Server Error: Method not found.";
                    return;
                }

                // Call the controller method with the route variables
                call_user_func_array([$controller, $method], $vars);
                break;
        }
    }
}