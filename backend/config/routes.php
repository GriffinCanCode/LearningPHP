<?php

declare(strict_types=1);

use NewsAggregator\Infrastructure\Http\Controllers\NewsController;
use NewsAggregator\Infrastructure\Http\Controllers\SourceController;
use NewsAggregator\Infrastructure\Http\Controllers\HealthController;

// Health check
$router->get('/health', HealthController::class . '@check');

// News API endpoints
$router->get('/api/news', NewsController::class . '@index');
$router->get('/api/news/{id}', NewsController::class . '@show');
$router->get('/api/news/category/{category}', NewsController::class . '@byCategory');
$router->get('/api/news/source/{source}', NewsController::class . '@bySource');
$router->get('/api/news/search/{query}', NewsController::class . '@search');

// Sources management
$router->get('/api/sources', SourceController::class . '@index');
$router->post('/api/sources', SourceController::class . '@store');
$router->get('/api/sources/{id}', SourceController::class . '@show');
$router->put('/api/sources/{id}', SourceController::class . '@update');
$router->delete('/api/sources/{id}', SourceController::class . '@destroy');

// Scraping triggers
$router->post('/api/scrape/all', NewsController::class . '@scrapeAll');
$router->post('/api/scrape/source/{id}', NewsController::class . '@scrapeSource');
