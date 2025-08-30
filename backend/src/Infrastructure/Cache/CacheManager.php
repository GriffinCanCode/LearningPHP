<?php

declare(strict_types=1);

namespace NewsAggregator\Infrastructure\Cache;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\CacheItem;
use Psr\Cache\CacheItemPoolInterface;

final class CacheManager
{
    private CacheItemPoolInterface $cache;
    
    public function __construct(
        private readonly string $driver = 'file',
        private readonly int $defaultTtl = 3600
    ) {
        $this->cache = $this->createCacheAdapter();
    }
    
    public function get(string $key): mixed
    {
        $item = $this->cache->getItem($this->normalizeKey($key));
        
        return $item->isHit() ? $item->get() : null;
    }
    
    public function set(string $key, mixed $value, int $ttl = null): bool
    {
        $item = $this->cache->getItem($this->normalizeKey($key));
        $item->set($value);
        
        if ($ttl !== null) {
            $item->expiresAfter($ttl);
        } else {
            $item->expiresAfter($this->defaultTtl);
        }
        
        return $this->cache->save($item);
    }
    
    public function has(string $key): bool
    {
        return $this->cache->hasItem($this->normalizeKey($key));
    }
    
    public function delete(string $key): bool
    {
        return $this->cache->deleteItem($this->normalizeKey($key));
    }
    
    public function clear(): bool
    {
        return $this->cache->clear();
    }
    
    public function getMultiple(array $keys): array
    {
        $normalizedKeys = array_map([$this, 'normalizeKey'], $keys);
        $items = $this->cache->getItems($normalizedKeys);
        
        $results = [];
        foreach ($items as $key => $item) {
            /** @var CacheItem $item */
            if ($item->isHit()) {
                $originalKey = array_search($key, array_combine($keys, $normalizedKeys));
                $results[$originalKey] = $item->get();
            }
        }
        
        return $results;
    }
    
    public function setMultiple(array $values, int $ttl = null): bool
    {
        $success = true;
        
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    public function deleteMultiple(array $keys): bool
    {
        $normalizedKeys = array_map([$this, 'normalizeKey'], $keys);
        return $this->cache->deleteItems($normalizedKeys);
    }
    
    public function remember(string $key, callable $callback, int $ttl = null): mixed
    {
        $value = $this->get($key);
        
        if ($value === null) {
            $value = $callback();
            $this->set($key, $value, $ttl);
        }
        
        return $value;
    }
    
    public function tags(array $tags): TaggedCacheManager
    {
        return new TaggedCacheManager($this, $tags);
    }
    
    private function createCacheAdapter(): CacheItemPoolInterface
    {
        return match ($this->driver) {
            'array' => new ArrayAdapter($this->defaultTtl),
            'file' => new FilesystemAdapter(
                namespace: 'news_aggregator',
                defaultLifetime: $this->defaultTtl,
                directory: __DIR__ . '/../../../storage/cache'
            ),
            default => throw new \InvalidArgumentException("Unsupported cache driver: {$this->driver}")
        };
    }
    
    private function normalizeKey(string $key): string
    {
        // Remove invalid characters and ensure key length is within limits
        return preg_replace('/[^a-zA-Z0-9_.-]/', '_', $key);
    }
}

final class TaggedCacheManager
{
    public function __construct(
        private readonly CacheManager $cache,
        private readonly array $tags
    ) {}
    
    public function get(string $key): mixed
    {
        return $this->cache->get($this->taggedKey($key));
    }
    
    public function set(string $key, mixed $value, int $ttl = null): bool
    {
        $result = $this->cache->set($this->taggedKey($key), $value, $ttl);
        
        // Store tag mappings
        foreach ($this->tags as $tag) {
            $taggedKeys = $this->cache->get("tag_{$tag}") ?? [];
            $taggedKeys[] = $this->taggedKey($key);
            $this->cache->set("tag_{$tag}", array_unique($taggedKeys));
        }
        
        return $result;
    }
    
    public function flush(): bool
    {
        $success = true;
        
        foreach ($this->tags as $tag) {
            $taggedKeys = $this->cache->get("tag_{$tag}") ?? [];
            
            foreach ($taggedKeys as $key) {
                if (!$this->cache->delete($key)) {
                    $success = false;
                }
            }
            
            // Clear the tag mapping
            $this->cache->delete("tag_{$tag}");
        }
        
        return $success;
    }
    
    private function taggedKey(string $key): string
    {
        $tagString = implode('_', $this->tags);
        return "tagged_{$tagString}_{$key}";
    }
}
