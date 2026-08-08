<?php
declare(strict_types=1);

namespace Serapha\Core;

use Serapha\Exception\DispatcherException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

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
            throw new DispatcherException('Class {'.$class.'} is not instantiable.');
        }

        $instance = $reflector->newInstanceWithoutConstructor();
        $initializedClasses = [];

        // Initialize properties defined by the parent class
        $this->initializeClass($class, $instance, $initializedClasses);

        return $instance;
    }

    protected function initializeClass(string $class, object $instance, array &$initializedClasses): void
    {
        $reflector = new ReflectionClass($class);

        if ($reflector->getParentClass()) {
            $parentClass = $reflector->getParentClass()->getName();
            if ($parentClass) {
                $this->initializeClass($parentClass, $instance, $initializedClasses);
            }
        }

        if ($reflector->hasMethod('__construct')) {
            $constructor = $reflector->getConstructor();
            $parameters = $constructor->getParameters();

            // Check if the class has been initialized
            $className = $constructor->getDeclaringClass()->getName();
            if (isset($initializedClasses[$className])) {
                return;
            }
            $initializedClasses[$className] = true;

            // Resolve dependencies
            $dependencies = array_map(function (ReflectionParameter $param) {
                $paramType = $param->getType();

                if ($paramType instanceof ReflectionUnionType) {
                    foreach ($paramType->getTypes() as $unionedType) {
                        if (!$unionedType->isBuiltin()) {
                            return $this->container->get($unionedType->getName());
                        }
                    }
                } elseif ($paramType instanceof ReflectionNamedType && !$paramType->isBuiltin()) {
                    return $this->container->get($paramType->getName());
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
