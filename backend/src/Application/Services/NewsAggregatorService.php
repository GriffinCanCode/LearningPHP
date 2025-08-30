<?php

declare(strict_types=1);

namespace NewsAggregator\Application\Services;

use NewsAggregator\Domain\News\Source;

interface NewsAggregatorService
{
    /**
     * Scrape articles from a specific source
     */
    public function scrapeSource(Source $source): array;
    
    /**
     * Scrape articles from all active sources
     */
    public function scrapeAllSources(): array;
    
    /**
     * Process and save articles with deduplication
     */
    public function processArticles(array $articles, Source $source): array;
    
    /**
     * Check if an article is a duplicate
     */
    public function isDuplicate(array $articleData): bool;
    
    /**
     * Clean up old articles based on retention policy
     */
    public function cleanupOldArticles(int $daysToKeep = 30): int;
    
    /**
     * Get aggregation statistics
     */
    public function getStatistics(): array;
}
