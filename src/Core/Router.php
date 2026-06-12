<?php

declare(strict_types=1);

namespace PedalPal\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, callable|array $handler, array $middleware): self
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        $this->routes[] = [
            'method' => $method,
            'pattern' => '#^' . $pattern . '$#',
            'handler' => $handler,
            'middleware' => $middleware,
        ];
        return $this;
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, fn($key) => is_string($key), ARRAY_FILTER_USE_KEY);

                foreach ($route['middleware'] as $middleware) {
                    $middleware();
                }

                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$class, $methodName] = $handler;
                    $instance = new $class();
                    echo json_encode($instance->$methodName($params));
                } else {
                    echo json_encode($handler($params));
                }
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Not found', 'path' => $uri]);
    }
}
