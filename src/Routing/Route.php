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

    public static function setControllerDispatcher(ControllerDispatcher $controllerDispatcher): void
    {
        self::$controllerDispatcher = $controllerDispatcher;
    }

    public static function add(string $method, string $uri, string $controller): void
    {
        self::$routes[$method][$uri] = $controller;
    }

    public static function dispatch(Container $container): void
    {
        // Get query parameter from request
        $query = $_GET['query'] ?? '/';
        $query = empty($query) ? '/' : $query;

        $method = $_SERVER['REQUEST_METHOD'];

        // Find matched route
        foreach (self::$routes[$method] as $routeUri => $controller) {
            $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $routeUri);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $query, $matches)) {
                array_shift($matches); // Remove full match
                self::invokeController($controller, $matches);
                return;
            }
        }

        // Route not found
        self::notFound($container->get(I18n::class));
    }

    private static function invokeController(string $controller, array $params): void
    {
        $parts = explode('@', $controller);
        $controllerName = 'App\\Controller\\' . $parts[0];
        $action = $parts[1] ?? 'index';

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
