<?php

declare(strict_types=1);

namespace NewsAggregator\Infrastructure\Container;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

final class Container implements ContainerInterface
{
    private array $bindings = [];
    private array $instances = [];
    private array $singletons = [];
    
    public function bind(string $abstract, callable|string $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }
    
    public function singleton(string $abstract, callable|string $concrete): void
    {
        $this->bind($abstract, $concrete);
        $this->singletons[$abstract] = true;
    }
    
    public function get(string $abstract): mixed
    {
        // Return existing singleton instance if available
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        
        $concrete = $this->bindings[$abstract] ?? $abstract;
        
        $instance = $this->resolve($concrete);
        
        // Store singleton instances
        if (isset($this->singletons[$abstract])) {
            $this->instances[$abstract] = $instance;
        }
        
        return $instance;
    }
    
    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || class_exists($abstract);
    }
    
    private function resolve(callable|string $concrete): mixed
    {
        if (is_callable($concrete)) {
            return $concrete($this);
        }
        
        if (is_string($concrete)) {
            return $this->build($concrete);
        }
        
        throw new InvalidArgumentException("Cannot resolve {$concrete}");
    }
    
    private function build(string $class): object
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new InvalidArgumentException("Class {$class} not found", 0, $e);
        }
        
        if (!$reflection->isInstantiable()) {
            throw new InvalidArgumentException("Class {$class} is not instantiable");
        }
        
        $constructor = $reflection->getConstructor();
        
        if ($constructor === null) {
            return new $class();
        }
        
        $parameters = $constructor->getParameters();
        $dependencies = $this->resolveDependencies($parameters);
        
        return $reflection->newInstanceArgs($dependencies);
    }
    
    private function resolveDependencies(array $parameters): array
    {
        return array_map(
            fn(ReflectionParameter $parameter) => $this->resolveParameter($parameter),
            $parameters
        );
    }
    
    private function resolveParameter(ReflectionParameter $parameter): mixed
    {
        $type = $parameter->getType();
        
        if ($type === null) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }
            
            throw new InvalidArgumentException(
                "Cannot resolve parameter {$parameter->getName()} without type hint"
            );
        }
        
        if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
            return $this->get($type->getName());
        }
        
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }
        
        throw new InvalidArgumentException(
            "Cannot resolve parameter {$parameter->getName()}"
        );
    }
}
