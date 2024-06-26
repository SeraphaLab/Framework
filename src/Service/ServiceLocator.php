<?php
namespace Serapha\Service;

use Serapha\Core\Container;
use Serapha\Core\Dispatcher;

class ServiceLocator
{
    protected static Container $container;

    public static function setContainer(Container $container)
    {
        self::$container = $container;
    }

    public static function get(string $serviceName)
    {
        $dispatcher = new Dispatcher(self::$container);

        return $dispatcher->resolve($serviceName);
    }
}
