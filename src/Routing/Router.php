<?php
declare(strict_types=1);

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
