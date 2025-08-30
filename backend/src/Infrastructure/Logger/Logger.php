<?php

declare(strict_types=1);

namespace NewsAggregator\Infrastructure\Logger;

use Monolog\Logger as MonologLogger;

final class Logger extends MonologLogger
{
    public function logScrapingStart(string $sourceName, string $sourceId): void
    {
        $this->info('Scraping started', [
            'source_name' => $sourceName,
            'source_id' => $sourceId,
            'type' => 'scraping_start'
        ]);
    }
    
    public function logScrapingSuccess(string $sourceName, string $sourceId, int $articlesFound, int $articlesNew): void
    {
        $this->info('Scraping completed successfully', [
            'source_name' => $sourceName,
            'source_id' => $sourceId,
            'articles_found' => $articlesFound,
            'articles_new' => $articlesNew,
            'type' => 'scraping_success'
        ]);
    }
    
    public function logScrapingFailure(string $sourceName, string $sourceId, \Throwable $exception): void
    {
        $this->error('Scraping failed', [
            'source_name' => $sourceName,
            'source_id' => $sourceId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'type' => 'scraping_failure'
        ]);
    }
    
    public function logApiCall(string $method, string $endpoint, int $responseCode, float $duration): void
    {
        $level = $responseCode >= 400 ? 'warning' : 'info';
        
        $this->log($level, 'API call completed', [
            'method' => $method,
            'endpoint' => $endpoint,
            'response_code' => $responseCode,
            'duration_ms' => round($duration * 1000, 2),
            'type' => 'api_call'
        ]);
    }
    
    public function logDuplicateArticle(string $articleTitle, string $reason): void
    {
        $this->debug('Duplicate article detected', [
            'title' => $articleTitle,
            'reason' => $reason,
            'type' => 'duplicate_detection'
        ]);
    }
    
    public function logCacheOperation(string $operation, string $key, bool $success): void
    {
        $this->debug('Cache operation', [
            'operation' => $operation,
            'key' => $key,
            'success' => $success,
            'type' => 'cache_operation'
        ]);
    }
    
    public function logDatabaseQuery(string $query, array $params, float $duration): void
    {
        $this->debug('Database query executed', [
            'query' => $query,
            'params' => $params,
            'duration_ms' => round($duration * 1000, 2),
            'type' => 'database_query'
        ]);
    }
    
    public function logRateLimit(string $source, int $delay): void
    {
        $this->debug('Rate limit applied', [
            'source' => $source,
            'delay_seconds' => $delay,
            'type' => 'rate_limit'
        ]);
    }
}
