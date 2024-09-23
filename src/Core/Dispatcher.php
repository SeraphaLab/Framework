<?php
declare(strict_types=1);

namespace Serapha\Core;

use Serapha\Exception\DispatcherException;
use ReflectionClass;
use ReflectionParameter;

final class Dispatcher
{
    protected Container $container;
    private array $initializedClasses = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function resolve(string $class)
    {
        $reflector = new ReflectionClass($class);

        if (!$reflector->isInstantiable()) {
            throw new DispatcherException('Class {'.$class.'} is not instantiable.');
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

            // Check if the class has been initialized
            $className = $constructor->getDeclaringClass()->getName();
            if (isset($this->initializedClasses[$className])) {
                return;
            }
            $this->initializedClasses[$className] = $instance;

            // Resolve dependencies
            $dependencies = array_map(function (ReflectionParameter $param) {
                if ($param->getType() && !$param->getType()->isBuiltin()) {
                    return $this->container->get($param->getType()->getName());
                }

                if ($param->isDefaultValueAvailable()) {
                    return $param->getDefaultValue();
                }

                throw new DispatcherException('Cannot resolve the dependency {'.$param->name.'}');
            }, $parameters);

            $constructor->invokeArgs($instance, $dependencies);
        }
    }
}
