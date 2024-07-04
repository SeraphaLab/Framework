<?php
declare(strict_types=1);

namespace Serapha\Service;

use Serapha\Core\Container;
use Serapha\Core\Dispatcher;

final class ServiceLocator
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
