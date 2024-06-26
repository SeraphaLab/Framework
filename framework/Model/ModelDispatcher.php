<?php
namespace Serapha\Model;

use Serapha\Core\Container;
use Serapha\Core\Dispatcher;

class ModelDispatcher
{
    protected Dispatcher $dispatcher;

    public function __construct(Container $container)
    {
        $this->dispatcher = new Dispatcher($container);
    }

    public function resolve(string $model)
    {
        return $this->dispatcher->resolve($model);
    }
}
