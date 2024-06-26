<?php
namespace Serapha\Service;

class ServiceLocator
{
    protected static $container;

    public static function setContainer($container)
    {
        self::$container = $container;
    }

    public static function get($serviceName)
    {
        return self::$container->get($serviceName);
    }
}
