<?php
namespace Serapha\Routing;

use Serapha\Core\Container;
use Serapha\Controller\ControllerDispatcher;
use Serapha\Middleware\MiddlewareInterface;

final class Router
{
    private Container $container;
    /** @var MiddlewareInterface[] */
    private array $middleware = [];
    private string $prefix = '';
    private array $groupStack = [];

    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

    public function __construct(Container $container)
    {
        $this->container = $container;
        Route::setControllerDispatcher(new ControllerDispatcher($this->container));
    }

    public function get(string $uri, string|array $action): void
    {
        $this->addRoute(self::GET, $uri, $action);
    }

    public function post(string $uri, string|array $action): void
    {
        $this->addRoute(self::POST, $uri, $action);
    }

    public function put(string $uri, string|array $action): void
    {
        $this->addRoute(self::PUT, $uri, $action);
    }

    public function delete(string $uri, string|array $action): void
    {
        $this->addRoute(self::DELETE, $uri, $action);
    }

    public function addRoute(string $method, string $uri, string|array $controller): void
    {
        $uri = $this->prefix . $uri;
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

        // Call the middleware stack, passing the final route dispatching as the last callable
        $finalHandler = function($request, $response) {
            Route::dispatch($this->container);
            return $response;
        };

        $this->callMiddleware($request, $response, $finalHandler);
    }

    public function group(array $attributes, callable $callback): void
    {
        $this->updateGroupAttributes($attributes);

        call_user_func($callback, $this);

        $this->restoreGroupAttributes();
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

    private function updateGroupAttributes(array $attributes): void
    {
        if (isset($attributes['prefix'])) {
            $prefix = trim($attributes['prefix'], '/');
            $this->prefix .= '/' . $prefix;
        }

        if (isset($attributes['middleware'])) {
            foreach ($attributes['middleware'] as $middleware) {
                $this->addMiddleware($middleware);
            }
        }

        $this->groupStack[] = $attributes;
    }

    private function restoreGroupAttributes(): void
    {
        array_pop($this->groupStack);

        $this->prefix = '';
        $this->middleware = [];

        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $this->prefix .= '/' . trim($group['prefix'], '/');
            }

            if (isset($group['middleware'])) {
                foreach ($group['middleware'] as $middleware) {
                    $this->addMiddleware($middleware);
                }
            }
        }
    }
}
