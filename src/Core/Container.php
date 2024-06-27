<?php
namespace Serapha\Core;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;
use Exception;

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
        } catch (Exception $e) {
            throw $e;
        }

        throw new Exception("No entry or class found for '{$id}'.");
    }

    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || isset($this->bindings[$id]);
    }

    public function resolve(string $abstract)
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

    public function build($concrete)
    {
        if ($concrete instanceof \Closure) {
            return $concrete($this);
        }

        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new Exception("Class {$concrete} is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete;
        }

        $parameters = $constructor->getParameters();
        $dependencies = array_map(function(ReflectionParameter $param) {
            if ($param->getType() && !$param->getType()->isBuiltin()) {
                return $this->resolve($param->getType()->getName());
            }
            if ($param->isDefaultValueAvailable()) {
                return $param->getDefaultValue();
            }

            throw new Exception("Cannot resolve the dependency {$param->name}");
        }, $parameters);

        return $reflector->newInstanceArgs($dependencies);
    }
}
