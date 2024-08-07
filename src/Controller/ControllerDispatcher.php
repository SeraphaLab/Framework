<?php
declare(strict_types=1);

namespace Serapha\Controller;

use Serapha\Core\Container;
use Serapha\Core\Dispatcher;

final class ControllerDispatcher
{
    protected Dispatcher $dispatcher;

    public function __construct(Container $container)
    {
        $this->dispatcher = new Dispatcher($container);
    }

    public function dispatch(string $controller, string $method, array $parameters)
    {
        $instance = $this->dispatcher->resolve($controller);

        return $instance->$method(...$parameters);
    }
}
