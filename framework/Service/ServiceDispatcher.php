<?php
namespace Serapha\Service;

use Serapha\Core\Container;
use Serapha\Core\Dispatcher;

class ServiceDispatcher
{
    protected Dispatcher $dispatcher;
    private static Container $container;

    public function __construct(Container $container)
    {
        $this->dispatcher = new Dispatcher($container);
        self::$container = $container;
    }

    public static function resolve(string $service)
    {
        return self::$container->get($service);
    }
}
