<?php

declare(strict_types=1);

namespace NewsAggregator\Infrastructure\Http\Controllers;

use NewsAggregator\Domain\News\ArticleRepository;
use NewsAggregator\Domain\News\SourceRepository;
use NewsAggregator\Application\Services\NewsAggregatorService;
use NewsAggregator\Infrastructure\Logger\Logger;

final readonly class NewsController
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private SourceRepository $sourceRepository,
        private NewsAggregatorService $aggregatorService,
        private Logger $logger
    ) {}
    
    public function index(array $params): array
    {
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = max(1, min(100, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        
        try {
            $articles = $this->articleRepository->findLatest($limit, $offset);
            $total = $this->articleRepository->countTotal();
            
            return [
                'articles' => array_map(fn($article) => $article->jsonSerialize(), $articles),
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ],
                'total' => $total
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to fetch articles', ['error' => $e->getMessage()]);
            return ['error' => 'Failed to fetch articles', 'articles' => [], 'total' => 0];
        }
    }
    
    public function show(array $params): array
    {
        $articleId = $params['id'] ?? '';
        
        if (empty($articleId)) {
            http_response_code(400);
            return ['error' => 'Article ID is required'];
        }
        
        try {
            $article = $this->articleRepository->findById($articleId);
            
            if (!$article) {
                http_response_code(404);
                return ['error' => 'Article not found'];
            }
            
            return $article->jsonSerialize();
        } catch (\Exception $e) {
            $this->logger->error('Failed to fetch article', [
                'article_id' => $articleId,
                'error' => $e->getMessage()
            ]);
            
            http_response_code(500);
            return ['error' => 'Failed to fetch article'];
        }
    }
    
    public function byCategory(array $params): array
    {
        $category = $params['category'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = max(1, min(100, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        
        if (empty($category)) {
            http_response_code(400);
            return ['error' => 'Category is required'];
        }
        
        try {
            $articles = $this->articleRepository->findByCategory($category, $limit, $offset);
            $total = $this->articleRepository->countByCategory($category);
            
            return [
                'articles' => array_map(fn($article) => $article->jsonSerialize(), $articles),
                'category' => $category,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ],
                'total' => $total
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to fetch articles by category', [
                'category' => $category,
                'error' => $e->getMessage()
            ]);
            
            return ['error' => 'Failed to fetch articles', 'articles' => [], 'total' => 0];
        }
    }
    
    public function bySource(array $params): array
    {
        $sourceName = $params['source'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = max(1, min(100, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        
        if (empty($sourceName)) {
            http_response_code(400);
            return ['error' => 'Source is required'];
        }
        
        try {
            $articles = $this->articleRepository->findBySource($sourceName, $limit, $offset);
            $total = $this->articleRepository->countBySource($sourceName);
            
            return [
                'articles' => array_map(fn($article) => $article->jsonSerialize(), $articles),
                'source' => $sourceName,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ],
                'total' => $total
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to fetch articles by source', [
                'source' => $sourceName,
                'error' => $e->getMessage()
            ]);
            
            return ['error' => 'Failed to fetch articles', 'articles' => [], 'total' => 0];
        }
    }
    
    public function search(array $params): array
    {
        $query = $params['query'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = max(1, min(100, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        
        if (empty($query) || strlen($query) < 2) {
            http_response_code(400);
            return ['error' => 'Search query must be at least 2 characters long'];
        }
        
        try {
            $articles = $this->articleRepository->search($query, $limit, $offset);
            $total = $this->articleRepository->countSearch($query);
            
            return [
                'articles' => array_map(fn($article) => $article->jsonSerialize(), $articles),
                'query' => $query,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ],
                'total' => $total
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to search articles', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            
            return ['error' => 'Search failed', 'articles' => [], 'total' => 0];
        }
    }
    
    public function scrapeAll(array $params): array
    {
        try {
            $sources = $this->sourceRepository->findAllActive();
            $results = [];
            
            foreach ($sources as $source) {
                $result = $this->aggregatorService->scrapeSource($source);
                $results[] = [
                    'source' => $source->name,
                    'success' => $result['success'],
                    'articles_found' => $result['articles_found'] ?? 0,
                    'articles_new' => $result['articles_new'] ?? 0,
                    'error' => $result['error'] ?? null
                ];
            }
            
            return [
                'message' => 'Scraping completed for all sources',
                'results' => $results,
                'total_sources' => count($sources)
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to scrape all sources', ['error' => $e->getMessage()]);
            
            http_response_code(500);
            return ['error' => 'Failed to initiate scraping'];
        }
    }
    
    public function scrapeSource(array $params): array
    {
        $sourceId = $params['id'] ?? '';
        
        if (empty($sourceId)) {
            http_response_code(400);
            return ['error' => 'Source ID is required'];
        }
        
        try {
            $source = $this->sourceRepository->findById($sourceId);
            
            if (!$source) {
                http_response_code(404);
                return ['error' => 'Source not found'];
            }
            
            $result = $this->aggregatorService->scrapeSource($source);
            
            if ($result['success']) {
                return [
                    'message' => 'Scraping completed successfully',
                    'source' => $source->name,
                    'articles_found' => $result['articles_found'],
                    'articles_new' => $result['articles_new']
                ];
            } else {
                http_response_code(500);
                return [
                    'error' => 'Scraping failed',
                    'message' => $result['error'] ?? 'Unknown error'
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to scrape source', [
                'source_id' => $sourceId,
                'error' => $e->getMessage()
            ]);
            
            http_response_code(500);
            return ['error' => 'Failed to scrape source'];
        }
    }
}
