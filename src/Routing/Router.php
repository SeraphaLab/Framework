<?php
namespace Serapha\Routing;

use Serapha\Core\Container;
use Serapha\Controller\ControllerDispatcher;

final class Router
{
    private Container $container;

    public function __construct(Container $container, string $routePath)
    {
        $this->container = $container;
        $this->loadRoutes($routePath);
        Route::setControllerDispatcher(new ControllerDispatcher($this->container));
    }

    public function get(string $uri, string|array $action): void
    {
        Route::get($uri, $action);
    }

    public function post(string $uri, string|array $action): void
    {
        Route::post($uri, $action);
    }

    public function put(string $uri, string|array $action): void
    {
        Route::put($uri, $action);
    }

    public function delete(string $uri, string|array $action): void
    {
        Route::delete($uri, $action);
    }

    public function middleware(array|string $middleware): Route
    {
        return Route::middleware($middleware);
    }

    public function prefix(string $prefix): Route
    {
        return Route::prefix($prefix);
    }

    public function group(array $attributes, \Closure $callback): void
    {
        if (isset($attributes['prefix'])) {
            $this->prefix($attributes['prefix']);
        }

        if (isset($attributes['middleware'])) {
            $this->middleware($attributes['middleware']);
        }

        Route::group($callback);
    }

    public function handleRequest(): void
    {
        Route::dispatch($this->container);
    }

    private function loadRoutes(string $routePath): void
    {
        $routeFiles = glob($routePath);
        foreach ($routeFiles as $file) {
            require $file;
        }
    }
}
