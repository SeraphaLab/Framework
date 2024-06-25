<?php
namespace Serapha\Controller;

use Serapha\Core\Container;
use ReflectionClass;
use Exception;

class ControllerDispatcher
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function dispatch(string $controller, string $method, array $parameters)
    {
        $instance = $this->resolveController($controller);

        return $instance->$method(...$parameters);
    }

    protected function resolveController(string $controller)
    {
        $reflector = new ReflectionClass($controller);

        if (!$reflector->isInstantiable()) {
            throw new Exception("Class {$controller} is not instantiable.");
        }

        $instance = $reflector->newInstanceWithoutConstructor();

        // Initialize properties defined by the parent class
        $this->initializeClass($controller, $instance);

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
            $dependencies = array_map(function($param) {
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
