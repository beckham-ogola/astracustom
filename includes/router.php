<?php
/** AstraCampus - Minimal Router */

class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        if ($base !== '' && $base !== '/' && strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }
        $uri = '/' . trim($uri, '/');
        if ($uri === '/index.php') { $uri = '/'; }

        $routes = $this->routes[$method] ?? [];

        // exact match first
        if (isset($routes[$uri])) {
            call_user_func($routes[$uri]);
            return;
        }

        // dynamic match: /students/{id}/edit
        foreach ($routes as $pattern => $handler) {
            $regex = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $pattern);
            $regex = '#^' . $regex . '$#';
            if (preg_match($regex, $uri, $matches)) {
                $params = array_filter($matches, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);
                call_user_func($handler, $params);
                return;
            }
        }

        http_response_code(404);
        require __DIR__ . '/../views/errors/404.php';
    }
}
