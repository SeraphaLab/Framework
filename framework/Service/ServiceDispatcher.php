<?php
namespace Serapha\Service;

use Serapha\Core\Container;
use Serapha\Core\Dispatcher;

class ServiceDispatcher
{
    protected Dispatcher $dispatcher;

    public function __construct(Container $container)
    {
        $this->dispatcher = new Dispatcher($container);
    }

    public function resolve(string $service)
    {
        return $this->dispatcher->resolve($service);
    }
}
