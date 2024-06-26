<?php
namespace Serapha\Routing;

use Serapha\Core\Container;
use Serapha\Controller\ControllerDispatcher;
use Serapha\Middleware\MiddlewareInterface;

class Router
{
    private Container $container;
    /**
     * @var MiddlewareInterface[]
     */
    private array $middleware = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
        Route::setControllerDispatcher(new ControllerDispatcher($this->container));
    }

    public function addRoute(string $method, string $uri, string $controller): void
    {
        Route::add($method, $uri, $controller);
    }

    public function addMiddleware(MiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function dispatch(string $query): void
    {
        $request = new Request(['query' => $query]);
        $response = new Response();

        $this->callMiddlewareStack($request, $response, function ($request, $response) {
            Route::dispatch($this->container);
            return $response;
        });
    }

    public function runMiddleware(MiddlewareInterface $middleware, ?Request $request = null, ?Response $response = null, ?callable $next = null): Response
    {
        $request = $request ?? new Request();
        $response = $response ?? new Response();
        $next = $next ?? function ($request, $response) {
            return $response;
        };

        return $middleware->handle($request, $response, $next);
    }

    public function callMiddleware(?Request $request = null, ?Response $response = null, ?callable $last = null): Response
    {
        $request = $request ?? new Request();
        $response = $response ?? new Response();
        $last = $last ?? function ($request, $response) {
            return $response;
        };

        return $this->callMiddlewareStack($request, $response, $last);
    }

    private function callMiddlewareStack(Request $request, Response $response, callable $last): Response
    {
        $middlewareStack = $this->middleware;

        $nextMiddleware = function (Request $request, Response $response) use (&$middlewareStack, $last, &$nextMiddleware): Response {
            if (empty($middlewareStack)) {
                return $last($request, $response);
            }

            $currentMiddleware = array_shift($middlewareStack);

            return $currentMiddleware->handle($request, $response, $nextMiddleware);
        };

        return $nextMiddleware($request, $response);
    }
}
