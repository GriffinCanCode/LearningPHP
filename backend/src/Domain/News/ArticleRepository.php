<?php

declare(strict_types=1);

namespace NewsAggregator\Domain\News;

interface ArticleRepository
{
    public function save(Article $article): void;
    
    public function findById(string $id): ?Article;
    
    public function findByUrl(string $url): ?Article;
    
    public function findByContentHash(string $hash): ?Article;
    
    public function findLatest(int $limit = 20, int $offset = 0): array;
    
    public function findByCategory(string $category, int $limit = 20, int $offset = 0): array;
    
    public function findBySource(string $sourceName, int $limit = 20, int $offset = 0): array;
    
    public function search(string $query, int $limit = 20, int $offset = 0): array;
    
    public function countTotal(): int;
    
    public function countByCategory(string $category): int;
    
    public function countBySource(string $sourceName): int;
    
    public function countSearch(string $query): int;
    
    public function delete(string $id): void;
    
    public function deleteOlderThan(\DateTimeImmutable $date): int;
    
    public function findDuplicates(Article $article): array;
}
