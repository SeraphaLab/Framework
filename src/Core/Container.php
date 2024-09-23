<?php
declare(strict_types=1);

namespace Serapha\Core;

use Serapha\Exception\ContainerException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;
use ReflectionUnionType;

final class Container implements ContainerInterface
{
    private array $bindings = [];
    private array $instances = [];

    public function bind(string $abstract, $concrete = null, $shared = false)
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    public function singleton(string $abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }

    public function get(string $id)
    {
        try {
            return $this->resolve($id);
        } catch (\Exception $e) {
            throw new ContainerException('No entry or class found for \'{'.$id.'}\'.', 0, $e);
        }
    }

    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || isset($this->bindings[$id]);
    }

    private function resolve(string $abstract)
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $this->bindings[$abstract]['concrete'] ?? $abstract;
        $object = $this->build($concrete);

        if (isset($this->bindings[$abstract]['shared']) && $this->bindings[$abstract]['shared']) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    private function build($concrete)
    {
        if ($concrete instanceof \Closure) {
            return $concrete($this);
        }

        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new ContainerException('Class {'.$concrete.'} is not instantiable.');
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete;
        }

        $parameters = $constructor->getParameters();
        $dependencies = array_map(function (ReflectionParameter $param) {
            $paramType = $param->getType();
            if ($paramType) {
                // Check if the parameter type is a UnionType
                if ($paramType instanceof ReflectionUnionType) {
                    // Loop through each type in the UnionType
                    foreach ($paramType->getTypes() as $unionedType) {
                        if (!$unionedType->isBuiltin()) {
                            return $this->resolve($unionedType->getName());
                        }
                    }
                } elseif (!$paramType->isBuiltin()) {
                    return $this->resolve($paramType->getName());
                }
            }

            if ($param->isDefaultValueAvailable()) {
                return $param->getDefaultValue();
            }

            throw new ContainerException('Cannot resolve the dependency {'.$param->name.'}');
        }, $parameters);

        return $reflector->newInstanceArgs($dependencies);
    }
}
