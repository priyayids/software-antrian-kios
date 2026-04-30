<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private string $basePath = '';

    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
    }

    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function any(string $path, callable $handler): void
    {
        $this->addRoute('ANY', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable $handler): void
    {
        $normalizedPath = $path === '/' ? '/' : rtrim($path, '/');
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $this->basePath . $normalizedPath,
            'handler' => $handler,
        ];
    }

    public function dispatch(): void
    {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        $requestUri = rtrim($requestUri, '/');
        if ($requestUri === '') {
            $requestUri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== 'ANY' && $route['method'] !== $requestMethod) {
                continue;
            }

            $pattern = $this->convertPathToRegex($route['path']);
            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches);
                call_user_func_array($route['handler'], $matches);
                return;
            }
        }

        http_response_code(404);
        $this->render404();
    }

    private function convertPathToRegex(string $path): string
    {
        $path = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $path);
        return '#^' . $path . '$#';
    }

    private function render404(): void
    {
        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not Found', 'message' => 'The requested resource was not found.']);
        } else {
            require_once __DIR__ . '/../../templates/layouts/error.php';
        }
    }

    private function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
