<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use NewsAggregator\Application\App;
use NewsAggregator\Infrastructure\Http\Router;
use NewsAggregator\Infrastructure\Container\Container;
use Dotenv\Dotenv;

// Load environment variables
try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (Exception $e) {
    // Environment file is optional in production
}

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', $_ENV['APP_DEBUG'] ?? false ? '1' : '0');

// Set timezone
date_default_timezone_set('UTC');

try {
    // Initialize dependency injection container
    $container = new Container();
    
    // Initialize application
    $app = new App($container);
    
    // Initialize router
    $router = new Router($container);
    
    // Load routes
    require_once __DIR__ . '/../config/routes.php';
    
    // Handle the request
    $router->dispatch();
    
} catch (Throwable $e) {
    http_response_code(500);
    
    if ($_ENV['APP_DEBUG'] ?? false) {
        echo json_encode([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    } else {
        echo json_encode(['error' => 'Internal Server Error']);
    }
}
