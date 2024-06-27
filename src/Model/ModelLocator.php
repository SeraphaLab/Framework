<?php
namespace Serapha\Model;

use Serapha\Core\Container;
use Serapha\Core\Dispatcher;

final class ModelLocator
{
    protected static Container $container;

    public static function setContainer(Container $container)
    {
        self::$container = $container;
    }

    public static function get(string $modelName)
    {
        $dispatcher = new Dispatcher(self::$container);

        return $dispatcher->resolve($modelName);
    }
}
