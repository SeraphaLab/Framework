<?php
declare(strict_types=1);

namespace Serapha\Routing;

use carry0987\I18n\I18n;
use Serapha\Core\Container;
use Serapha\Controller\ControllerDispatcher;
use Exception;

final class Route
{
    private static ControllerDispatcher $controllerDispatcher;
    private static array $routes = [];
    private static array $groupStack = [];
    private static array $routesWheres = [];
    private static ?string $currentRoute = null;

    // Define HTTP methods as constants
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const PATCH = 'PATCH';
    const DELETE = 'DELETE';
    const OPTIONS = 'OPTIONS';

    public static function setControllerDispatcher(ControllerDispatcher $controllerDispatcher): void
    {
        self::$controllerDispatcher = $controllerDispatcher;
    }

    public static function supportedMethods(): array
    {
        return [self::GET, self::POST, self::PUT, self::PATCH, self::DELETE, self::OPTIONS];
    }

    public static function group(array $attributes, callable $callback): void
    {
        self::$groupStack[] = $attributes;

        $callback();

        array_pop(self::$groupStack);
    }

    public static function where(string $name, string $expression): self
    {
        if (empty($name) || empty($expression)) {
            return new self;
        }

        if (self::$currentRoute !== null) {
            if (!isset(self::$routesWheres[self::$currentRoute])) {
                self::$routesWheres[self::$currentRoute] = [];
            }
            self::$routesWheres[self::$currentRoute][$name] = $expression;
        }

        return new self;
    }

    public static function prefix(string $uri): RouteRegistrar
    {
        return (new RouteRegistrar)->prefix($uri);
    }

    public static function middleware(string $middleware): RouteRegistrar
    {
        return (new RouteRegistrar)->middleware($middleware);
    }

    public static function get(string $uri, array|string|callable|null $action = null): Route
    {
        return self::addRoute(self::GET, $uri, $action);
    }

    public static function post(string $uri, array|string|callable|null $action = null): Route
    {
        return self::addRoute(self::POST, $uri, $action);
    }

    public static function put(string $uri, array|string|callable|null $action = null): Route
    {
        return self::addRoute(self::PUT, $uri, $action);
    }

    public static function patch(string $uri, array|string|callable|null $action = null): Route
    {
        return self::addRoute(self::PATCH, $uri, $action);
    }

    public static function delete(string $uri, array|string|callable|null $action = null): Route
    {
        return self::addRoute(self::DELETE, $uri, $action);
    }

    public static function options(string $uri, array|string|callable|null $action = null): Route
    {
        return self::addRoute(self::OPTIONS, $uri, $action);
    }

    public static function addRoute(string|array $methods, string $uri, array|string|callable|null $action = null): Route
    {
        $methods = (array) $methods;

        // Process group stack attributes
        if (!empty(self::$groupStack)) {
            $groupAttributes = end(self::$groupStack);
            foreach ($methods as $method) {
                $groupUri = ($groupAttributes['prefix'] ?? '') . $uri;
                self::$routes[$method][$groupUri] = [
                    'controller' => $action['uses'] ?? $action,
                    'middleware' => array_merge($groupAttributes['middleware'] ?? [], $action['middleware'] ?? [])
                ];
                self::$currentRoute = $groupUri;
            }
        } else {
            foreach ($methods as $method) {
                self::$routes[$method][$uri] = [
                    'controller' => $action['uses'] ?? $action,
                    'middleware' => $action['middleware'] ?? []
                ];
                self::$currentRoute = $uri;
            }
        }

        return self::where('', '');
    }

    public static function dispatch(Container $container, string $query): void
    {
        // Get query parameter from request
        $query = empty($query) ? '/' : $query;
        $method = $_SERVER['REQUEST_METHOD'];

        // Get route information
        [$controller, $middleware, $params] = self::getRouteInfo($method, $query);

        if ($controller !== null) {
            $request = new Request();
            $response = new Response();

            $response = self::processMiddleware($container, $middleware, $request, $response, function($req, $res) use ($controller, $params) {
                self::invokeController($controller, $params);
                return $res;
            });

            echo $response->getBody();
        } else {
            // Route not found
            self::notFound($container->get(I18n::class));
        }
    }

    private static function getRouteInfo(string $method, string $uri): array
    {
        if (isset(self::$routes[$method])) {
            foreach (self::$routes[$method] as $routeUri => $routeInfo) {
                $wheres = self::$routesWheres[$routeUri] ?? [];
                $pattern = preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function ($matches) use ($wheres) {
                    $param = $matches[1];
                    return isset($wheres[$param]) ? "({$wheres[$param]})" : '([a-zA-Z0-9_]+)';
                }, $routeUri);
                $pattern = "#^{$pattern}$#";

                if (preg_match($pattern, $uri, $matches)) {
                    array_shift($matches); // Remove full match
                    return [
                        $routeInfo['controller'],
                        $routeInfo['middleware'],
                        $matches
                    ];
                }
            }
        }

        return [null, [], []];
    }

    private static function parseController(string|array $controller): array
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

    private static function processMiddleware(Container $container, array $middleware, Request $request, Response $response, callable $next): Response
    {
        foreach (array_reverse($middleware) as $mwClass) {
            $next = function($req, $res) use ($container, $mwClass, $next) {
                $middlewareInstance = $container->get($mwClass);
                return $middlewareInstance->process($req, $res, new Handler($next));
            };
        }

        return $next($request, $response);
    }

    private static function notFound(I18n $i18n): void
    {
        http_response_code(404);
        echo $i18n->fetch('error.page_not_found');
    }
}
