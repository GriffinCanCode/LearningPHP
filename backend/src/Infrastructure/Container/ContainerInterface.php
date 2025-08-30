<?php

declare(strict_types=1);

namespace NewsAggregator\Infrastructure\Container;

interface ContainerInterface
{
    /**
     * Register a service in the container
     */
    public function bind(string $abstract, callable|string $concrete): void;
    
    /**
     * Register a singleton service in the container
     */
    public function singleton(string $abstract, callable|string $concrete): void;
    
    /**
     * Resolve a service from the container
     */
    public function get(string $abstract): mixed;
    
    /**
     * Check if a service is bound in the container
     */
    public function has(string $abstract): bool;
}
