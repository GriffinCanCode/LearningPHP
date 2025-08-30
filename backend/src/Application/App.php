<?php

declare(strict_types=1);

namespace NewsAggregator\Application;

use NewsAggregator\Infrastructure\Container\ContainerInterface;
use NewsAggregator\Infrastructure\Database\DatabaseManager;
use NewsAggregator\Infrastructure\Cache\CacheManager;
use NewsAggregator\Infrastructure\Logger\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;

final readonly class App
{
    public function __construct(
        private ContainerInterface $container
    ) {
        $this->bootstrap();
    }
    
    private function bootstrap(): void
    {
        $this->registerServices();
        $this->initializeDatabase();
        $this->setupLogging();
    }
    
    private function registerServices(): void
    {
        // Register core services
        $this->container->singleton(DatabaseManager::class, function () {
            return new DatabaseManager(
                host: $_ENV['DB_HOST'] ?? 'localhost',
                port: (int)($_ENV['DB_PORT'] ?? 3306),
                database: $_ENV['DB_NAME'] ?? 'news_aggregator',
                username: $_ENV['DB_USER'] ?? 'root',
                password: $_ENV['DB_PASS'] ?? ''
            );
        });
        
        $this->container->singleton(CacheManager::class, function () {
            return new CacheManager(
                driver: $_ENV['CACHE_DRIVER'] ?? 'file',
                ttl: (int)($_ENV['CACHE_TTL'] ?? 3600)
            );
        });
        
        $this->container->singleton(Logger::class, function () {
            $logger = new Logger('news-aggregator');
            $logLevel = match ($_ENV['LOG_LEVEL'] ?? 'info') {
                'debug' => Level::Debug,
                'info' => Level::Info,
                'warning' => Level::Warning,
                'error' => Level::Error,
                default => Level::Info
            };
            
            $handler = new StreamHandler(
                __DIR__ . '/../../' . ($_ENV['LOG_FILE'] ?? 'storage/logs/app.log'),
                $logLevel
            );
            
            $logger->pushHandler($handler);
            return $logger;
        });
    }
    
    private function initializeDatabase(): void
    {
        $db = $this->container->get(DatabaseManager::class);
        $db->initialize();
    }
    
    private function setupLogging(): void
    {
        $logger = $this->container->get(Logger::class);
        
        set_error_handler(function (int $severity, string $message, string $file, int $line) use ($logger) {
            if (error_reporting() & $severity) {
                $logger->error("PHP Error: $message", [
                    'file' => $file,
                    'line' => $line,
                    'severity' => $severity
                ]);
            }
        });
        
        set_exception_handler(function (Throwable $exception) use ($logger) {
            $logger->critical('Uncaught exception', [
                'exception' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]);
        });
    }
    
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
