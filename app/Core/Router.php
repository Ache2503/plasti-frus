<?php
namespace App\Core;

class Router
{
    private $routes = [];

    public function get(string $uri, string $action): void
    {
        $this->routes['GET'][$uri] = $action;
    }

    public function post(string $uri, string $action): void
    {
        $this->routes['POST'][$uri] = $action;
    }

    public function dispatch(string $uri, string $method): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath)) ?: '/';
        }

        $method = strtoupper($method);

        if (!isset($this->routes[$method])) {
            $this->notFound();
            return;
        }

        foreach ($this->routes[$method] as $route => $action) {
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->executeAction($action, $params);
                return;
            }
        }

        $this->notFound();
    }

    private function executeAction(string $action, array $params = []): void
    {
        $parts = explode('@', $action);
        if (count($parts) !== 2) {
            throw new \InvalidArgumentException("Formato de acción inválido: {$action}");
        }

        [$controller, $method] = $parts;
        $controllerClass = "App\\Controllers\\{$controller}";

        if (!class_exists($controllerClass)) {
            throw new \Exception("Controlador no encontrado: {$controllerClass}");
        }

        $instance = new $controllerClass();
        if (!method_exists($instance, $method)) {
            throw new \Exception("Método no encontrado: {$controller}@{$method}");
        }

        call_user_func_array([$instance, $method], [$params]);
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo "<h1>404 - Página no encontrada</h1>";
        exit;
    }
}
