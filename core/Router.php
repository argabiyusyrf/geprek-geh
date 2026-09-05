<?php
class Router {
    private $routes = [];

    public function get($path, $handler) {
        $this->routes['GET'][$path] = $handler;
    }

    public function post($path, $handler) {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch() {
        $url = trim($_GET['url'] ?? '', '/');
        $method = $_SERVER['REQUEST_METHOD'];

        // exact match
        if (isset($this->routes[$method]['/' . $url])) {
            return $this->call($this->routes[$method]['/' . $url]);
        }

        // pattern match (e.g., products/{slug})
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            if (preg_match($pattern, '/' . $url, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return $this->call($handler, $params);
            }
        }

        http_response_code(404);
        require __DIR__ . '/../views/layouts/404.php';
    }

    private function call($handler, $params = []) {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            return call_user_func_array([$controller, $method], $params);
        }
        return call_user_func_array($handler, $params);
    }
}
