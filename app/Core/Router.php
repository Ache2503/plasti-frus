<?php
namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middleware = [];
    private array $groupMiddleware = [];

    public function get(string $uri, string $action): void
    {
        $this->routes['GET'][$uri] = $action;
    }

    public function post(string $uri, string $action): void
    {
        $this->routes['POST'][$uri] = $action;
    }

    public function put(string $uri, string $action): void
    {
        $this->routes['PUT'][$uri] = $action;
    }

    public function delete(string $uri, string $action): void
    {
        $this->routes['DELETE'][$uri] = $action;
    }

    public function addMiddleware(string $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function group(array $attributes, callable $callback): void
    {
        $previousGroupMiddleware = $this->groupMiddleware;

        if (isset($attributes['middleware'])) {
            $middlewares = is_array($attributes['middleware'])
                ? $attributes['middleware']
                : [$attributes['middleware']];
            $this->groupMiddleware = array_merge($this->groupMiddleware, $middlewares);
        }

        $callback($this);

        $this->groupMiddleware = $previousGroupMiddleware;
    }

    public function dispatch(string $uri, string $method): void
    {
        try {
            $this->handleDispatch($uri, $method);
        } catch (\Throwable $e) {
            \App\Exceptions\Handler::handle($e);
        }
    }

    private function handleDispatch(string $uri, string $method): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath)) ?: '/';
        }

        $method = strtoupper($method);

        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        if (!isset($this->routes[$method])) {
            $this->notFound();
            return;
        }

        if (isset($this->routes[$method][$uri])) {
            $this->executeAction($this->routes[$method][$uri]);
            return;
        }

        foreach ($this->routes[$method] as $route => $action) {
            if (!str_contains($route, '{')) {
                continue;
            }
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

        $namespaces = [
            "App\\Http\\Controllers\\{$controller}",
            "App\\Controllers\\{$controller}",
        ];

        $instance = null;
        $controllerClass = null;

        foreach ($namespaces as $ns) {
            if (class_exists($ns)) {
                $controllerClass = $ns;
                $instance = new $ns();
                break;
            }
        }

        if ($instance === null) {
            throw new \Exception("Controlador no encontrado: {$controller}");
        }

        if (!method_exists($instance, $method)) {
            throw new \Exception("Método no encontrado: {$controller}@{$method}");
        }

        if ($instance instanceof \App\Core\Controller) {
            foreach ($this->groupMiddleware as $middleware) {
                $instance->applyMiddleware($middleware, $params);
            }
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
