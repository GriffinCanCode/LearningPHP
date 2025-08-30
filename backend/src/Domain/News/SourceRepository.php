<?php

declare(strict_types=1);

namespace NewsAggregator\Domain\News;

interface SourceRepository
{
    public function save(Source $source): void;
    
    public function findById(string $id): ?Source;
    
    public function findByName(string $name): ?Source;
    
    public function findAll(): array;
    
    public function findAllActive(): array;
    
    public function findByType(SourceType $type): array;
    
    public function delete(string $id): void;
    
    public function updateLastScrapedAt(string $id, \DateTimeImmutable $timestamp): void;
    
    public function count(): int;
    
    public function countByType(SourceType $type): int;
    
    public function countActive(): int;
}
