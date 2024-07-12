<?php
declare(strict_types=1);

namespace Serapha\Routing;

class RouteRegistrar
{
    protected array $attributes = [];

    public function prefix(string $prefix): self
    {
        $this->attributes['prefix'] = '/' . trim($prefix, '/');

        return $this;
    }

    public function middleware(string $middleware): self
    {
        $this->attributes['middleware'][] = $middleware;

        return $this;
    }

    public function group(callable $callback): void
    {
        $attributes = $this->attributes;

        Route::group($attributes, $callback);
    }

    public function __call(string $method, array $parameters)
    {
        if (in_array($method, ['get', 'post', 'put', 'patch', 'delete', 'options'])) {
            return Route::addRoute($method, $parameters[0], array_merge($this->attributes, ['uses' => $parameters[1]]));
        }

        throw new \BadMethodCallException("Method {$method} does not exist.");
    }
}
