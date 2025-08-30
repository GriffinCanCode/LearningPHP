<?php

declare(strict_types=1);

namespace NewsAggregator\Infrastructure\Http;

use NewsAggregator\Infrastructure\Container\ContainerInterface;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

final class Router
{
    private array $routes = [];
    
    public function __construct(
        private readonly ContainerInterface $container
    ) {}
    
    public function get(string $route, string $handler): void
    {
        $this->addRoute('GET', $route, $handler);
    }
    
    public function post(string $route, string $handler): void
    {
        $this->addRoute('POST', $route, $handler);
    }
    
    public function put(string $route, string $handler): void
    {
        $this->addRoute('PUT', $route, $handler);
    }
    
    public function delete(string $route, string $handler): void
    {
        $this->addRoute('DELETE', $route, $handler);
    }
    
    public function patch(string $route, string $handler): void
    {
        $this->addRoute('PATCH', $route, $handler);
    }
    
    private function addRoute(string $method, string $route, string $handler): void
    {
        $this->routes[] = [$method, $route, $handler];
    }
    
    public function dispatch(): void
    {
        $dispatcher = simpleDispatcher(function (RouteCollector $r) {
            foreach ($this->routes as [$method, $route, $handler]) {
                $r->addRoute($method, $route, $handler);
            }
        });
        
        // Get HTTP method and URI
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        
        // Strip query string and decode
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        
        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $this->sendJsonResponse(['error' => 'Not Found'], 404);
                break;
                
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                $this->sendJsonResponse([
                    'error' => 'Method Not Allowed',
                    'allowed_methods' => $allowedMethods
                ], 405);
                break;
                
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $this->callHandler($handler, $vars);
                break;
        }
    }
    
    private function callHandler(string $handler, array $vars): void
    {
        [$class, $method] = explode('@', $handler);
        
        $controller = $this->container->get($class);
        $response = $controller->$method($vars);
        
        if (is_array($response) || is_object($response)) {
            $this->sendJsonResponse($response);
        } else {
            echo $response;
        }
    }
    
    private function sendJsonResponse(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_THROW_ON_ERROR);
    }
}
