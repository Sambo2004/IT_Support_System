<?php
// public/index.php - Front Controller & Routing Engine

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/Helpers/Session.php';
require_once BASE_PATH . '/app/Helpers/Csrf.php';
require_once BASE_PATH . '/app/Helpers/Flash.php';
require_once BASE_PATH . '/app/Helpers/Validator.php';
require_once BASE_PATH . '/app/Middleware/AuthMiddleware.php';
require_once BASE_PATH . '/app/Middleware/CsrfMiddleware.php';
require_once BASE_PATH . '/app/Middleware/RateLimitMiddleware.php';

// Class Autoloader
spl_autoload_register(function ($class) {
    $directories = [
        BASE_PATH . '/app/Controllers/',
        BASE_PATH . '/app/Models/',
        BASE_PATH . '/app/Middleware/',
        BASE_PATH . '/app/Helpers/'
    ];
    foreach ($directories as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

Session::start();

class Router {
    private array $routes = [];

    public function get(string $path, array $handler, array $middleware = []): void {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): void {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, array $handler, array $middleware): void {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    public function dispatch(): void {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Normalize path by stripping base script dir
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/' && $scriptName !== '\\' && str_starts_with($requestUri, $scriptName)) {
            $requestUri = substr($requestUri, strlen($scriptName));
        }

        $requestUri = '/' . trim($requestUri, '/');
        if ($requestUri === '//') $requestUri = '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            // Convert route format /tickets/{id} into regex
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $requestUri, $matches)) {
                // Filter named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run route middleware
                $this->runMiddleware($route['middleware']);

                [$controllerClass, $method] = $route['handler'];
                if (!class_exists($controllerClass)) {
                    http_response_code(500);
                    die("Controller [{$controllerClass}] does not exist.");
                }

                $controller = new $controllerClass();
                if (!method_exists($controller, $method)) {
                    http_response_code(500);
                    die("Method [{$method}] not found in [{$controllerClass}].");
                }

                call_user_func_array([$controller, $method], array_values($params));
                return;
            }
        }

        // Route Not Found (404)
        http_response_code(404);
        view('errors.404');
    }

    private function runMiddleware(array $middlewares): void {
        foreach ($middlewares as $mw) {
            if ($mw === 'csrf') {
                CsrfMiddleware::handle();
            } elseif ($mw === 'auth') {
                AuthMiddleware::handle();
            } elseif ($mw === 'guest') {
                if (Session::isLoggedIn()) {
                    redirect('/');
                }
            } elseif (str_starts_with($mw, 'role:')) {
                $roles = explode(',', substr($mw, 5));
                AuthMiddleware::handle($roles);
            }
        }
    }
}

$router = new Router();
require_once BASE_PATH . '/config/routes.php';
$router->dispatch();

