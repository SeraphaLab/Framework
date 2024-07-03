<?php
namespace Serapha\Routing;

use carry0987\I18n\I18n;
use Serapha\Core\Container;
use Serapha\Controller\ControllerDispatcher;
use Exception;

final class Route
{
    private static array $routes = [];
    private static ControllerDispatcher $controllerDispatcher;
    private static array $currentGroupAttributes = [
        'prefix' => '',
        'middleware' => []
    ];

    public static function setControllerDispatcher(ControllerDispatcher $controllerDispatcher): void
    {
        self::$controllerDispatcher = $controllerDispatcher;
    }

    public static function middleware(array|string $middleware): self
    {
        self::$currentGroupAttributes['middleware'] = array_merge(
            self::$currentGroupAttributes['middleware'],
            (array) $middleware
        );

        return new self;
    }

    public static function prefix(string $prefix): self
    {
        self::$currentGroupAttributes['prefix'] = trim($prefix, '/');

        return new self;
    }

    public function group(callable $callback): void
    {
        // Save previous state
        $previousGroupAttributes = self::$currentGroupAttributes;

        $callback();

        // Reset to previous state
        self::$currentGroupAttributes = $previousGroupAttributes;
    }

    public static function add(string $method, string $uri, string|array|callable $controller): void
    {
        $uri = '/' . trim(self::$currentGroupAttributes['prefix'] . '/' . trim($uri, '/'), '/');
        self::$routes[$method][$uri] = [
            'controller' => $controller,
            'middleware' => self::$currentGroupAttributes['middleware'],
        ];
    }

    public static function get(string $uri, string|array|callable $controller): void
    {
        self::add('GET', $uri, $controller);
    }

    // Add similar methods for POST, PUT, DELETE
    public static function post(string $uri, string|array|callable $controller): void
    {
        self::add('POST', $uri, $controller);
    }

    public static function put(string $uri, string|array|callable $controller): void
    {
        self::add('PUT', $uri, $controller);
    }

    public static function delete(string $uri, string|array|callable $controller): void
    {
        self::add('DELETE', $uri, $controller);
    }

    public static function dispatch(Container $container): void
    {
        // Get query parameter from request
        $query = $_GET['query'] ?? '/';
        $query = empty($query) ? '/' : $query;
        $method = $_SERVER['REQUEST_METHOD'];

        // Get route information
        $routeInfo = self::getRouteInfo($method, $query);

        if ($routeInfo) {
            $controller = $routeInfo['controller'];
            $middleware = $routeInfo['middleware'];
            $params = $routeInfo['params'];

            // Process middleware
            $request = new Request();
            $response = new Response();

            foreach ($middleware as $mwClass) {
                $middlewareInstance = $container->get($mwClass);
                $response = $middlewareInstance->process($request, $response, new Handler(function($req) use($controller, $params) {
                    self::invokeController($controller, $params);
                    return new Response();
                }));
            }

            // Invoke controller
            self::invokeController($controller, $params);
        } else {
            // Route not found
            self::notFound($container->get(I18n::class));
        }
    }

    public static function getRouteInfo(string $method, string $uri): ?array
    {
        if (isset(self::$routes[$method])) {
            foreach (self::$routes[$method] as $routeUri => $routeInfo) {
                $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $routeUri);
                $pattern = "#^{$pattern}$#";

                if (preg_match($pattern, $uri, $matches)) {
                    array_shift($matches); // Remove full match
                    return [
                        'controller' => $routeInfo['controller'],
                        'middleware' => $routeInfo['middleware'],
                        'params' => $matches
                    ];
                }
            }
        }

        return null;
    }

    private static function parseController($controller): array
    {
        if (is_string($controller)) {
            $parts = explode('@', $controller);
            $controllerName = 'App\\Controller\\' . $parts[0];
            if (!class_exists($controllerName)) {
                $controllerName = $controller;
            }
            $action = $parts[1] ?? 'index';
        } elseif (is_array($controller)) {
            $controllerName = $controller[0];
            $action = $controller[1] ?? 'index';
        } else {
            throw new Exception("Invalid controller definition");
        }

        return [$controllerName, $action];
    }

    private static function invokeController(string|array $controller, array $params): void
    {
        [$controllerName, $action] = self::parseController($controller);

        if (!class_exists($controllerName)) {
            throw new Exception("Controller [$controllerName] not found");
        }

        self::$controllerDispatcher->dispatch($controllerName, $action, $params);
    }

    private static function notFound(I18n $i18n): void
    {
        http_response_code(404);
        echo $i18n->fetch('error.page_not_found');
    }
}
