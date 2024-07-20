<?php
declare(strict_types=1);

namespace Serapha\Core;

use ReflectionClass;
use ReflectionParameter;
use Exception;

final class Dispatcher
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function resolve(string $class)
    {
        $reflector = new ReflectionClass($class);

        if (!$reflector->isInstantiable()) {
            throw new Exception("Class {$class} is not instantiable.");
        }

        $instance = $reflector->newInstanceWithoutConstructor();

        // Initialize properties defined by the parent class
        $this->initializeClass($class, $instance);

        return $instance;
    }

    protected function initializeClass(string $class, $instance)
    {
        $reflector = new ReflectionClass($class);

        if ($reflector->getParentClass()) {
            $parentClass = $reflector->getParentClass()->getName();
            if ($parentClass) {
                $this->initializeClass($parentClass, $instance);
            }
        }

        if ($reflector->hasMethod('__construct')) {
            $constructor = $reflector->getConstructor();
            $parameters = $constructor->getParameters();
            $dependencies = array_map(function (ReflectionParameter $param) {
                if ($param->getType() && !$param->getType()->isBuiltin()) {
                    return $this->container->get($param->getType()->getName());
                }

                if ($param->isDefaultValueAvailable()) {
                    return $param->getDefaultValue();
                }

                throw new Exception("Cannot resolve the dependency {$param->name}");
            }, $parameters);

            $constructor->invokeArgs($instance, $dependencies);
        }
    }
}
