<?php

declare(strict_types=1);

namespace NewsAggregator\Infrastructure\Http\Controllers;

use NewsAggregator\Infrastructure\Database\DatabaseManager;
use NewsAggregator\Infrastructure\Cache\CacheManager;
use NewsAggregator\Infrastructure\Logger\Logger;

final readonly class HealthController
{
    public function __construct(
        private DatabaseManager $database,
        private CacheManager $cache,
        private Logger $logger
    ) {}
    
    public function check(array $params): array
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'memory' => $this->checkMemory(),
        ];
        
        $allHealthy = array_reduce($checks, fn($carry, $check) => $carry && $check['status'] === 'ok', true);
        $overallStatus = $allHealthy ? 'healthy' : 'degraded';
        
        if (!$allHealthy) {
            http_response_code(503);
        }
        
        return [
            'status' => $overallStatus,
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'checks' => $checks,
            'uptime' => $this->getUptime()
        ];
    }
    
    private function checkDatabase(): array
    {
        try {
            $connection = $this->database->getConnection();
            $result = $connection->fetchOne('SELECT 1');
            
            if ($result == 1) {
                return [
                    'status' => 'ok',
                    'message' => 'Database connection successful',
                    'response_time' => $this->measureExecutionTime(
                        fn() => $connection->fetchOne('SELECT COUNT(*) FROM sources')
                    )
                ];
            }
            
            return [
                'status' => 'error',
                'message' => 'Database query returned unexpected result'
            ];
        } catch (\Exception $e) {
            $this->logger->error('Database health check failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }
    
    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_' . uniqid();
            $testValue = 'test_' . time();
            
            $startTime = microtime(true);
            
            // Test cache write
            $writeSuccess = $this->cache->set($testKey, $testValue, 10);
            if (!$writeSuccess) {
                return ['status' => 'error', 'message' => 'Cache write failed'];
            }
            
            // Test cache read
            $readValue = $this->cache->get($testKey);
            if ($readValue !== $testValue) {
                return ['status' => 'error', 'message' => 'Cache read failed'];
            }
            
            // Test cache delete
            $deleteSuccess = $this->cache->delete($testKey);
            
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            return [
                'status' => 'ok',
                'message' => 'Cache operations successful',
                'response_time' => $responseTime . 'ms'
            ];
        } catch (\Exception $e) {
            $this->logger->error('Cache health check failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'error',
                'message' => 'Cache check failed: ' . $e->getMessage()
            ];
        }
    }
    
    private function checkStorage(): array
    {
        $storagePath = __DIR__ . '/../../../storage';
        $requiredDirs = ['logs', 'cache'];
        
        $issues = [];
        
        // Check if storage directory exists and is writable
        if (!is_dir($storagePath)) {
            $issues[] = 'Storage directory does not exist';
        } elseif (!is_writable($storagePath)) {
            $issues[] = 'Storage directory is not writable';
        }
        
        // Check required subdirectories
        foreach ($requiredDirs as $dir) {
            $dirPath = $storagePath . '/' . $dir;
            
            if (!is_dir($dirPath)) {
                // Try to create it
                if (!mkdir($dirPath, 0755, true)) {
                    $issues[] = "Cannot create {$dir} directory";
                }
            } elseif (!is_writable($dirPath)) {
                $issues[] = "{$dir} directory is not writable";
            }
        }
        
        // Check disk space
        $freeBytes = disk_free_space($storagePath);
        $totalBytes = disk_total_space($storagePath);
        
        if ($freeBytes !== false && $totalBytes !== false) {
            $freePercentage = ($freeBytes / $totalBytes) * 100;
            
            if ($freePercentage < 5) {
                $issues[] = 'Low disk space (less than 5% free)';
            }
            
            $diskInfo = [
                'free_space' => $this->formatBytes($freeBytes),
                'total_space' => $this->formatBytes($totalBytes),
                'free_percentage' => round($freePercentage, 1) . '%'
            ];
        } else {
            $diskInfo = ['error' => 'Unable to determine disk space'];
        }
        
        return [
            'status' => empty($issues) ? 'ok' : 'error',
            'message' => empty($issues) ? 'Storage accessible' : implode(', ', $issues),
            'disk_space' => $diskInfo ?? null
        ];
    }
    
    private function checkMemory(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        
        $usagePercentage = $memoryLimit > 0 ? ($memoryUsage / $memoryLimit) * 100 : 0;
        
        $status = 'ok';
        $message = 'Memory usage normal';
        
        if ($usagePercentage > 80) {
            $status = 'warning';
            $message = 'High memory usage';
        } elseif ($usagePercentage > 90) {
            $status = 'error';
            $message = 'Critical memory usage';
        }
        
        return [
            'status' => $status,
            'message' => $message,
            'usage' => $this->formatBytes($memoryUsage),
            'limit' => $memoryLimit > 0 ? $this->formatBytes($memoryLimit) : 'unlimited',
            'usage_percentage' => round($usagePercentage, 1) . '%'
        ];
    }
    
    private function getUptime(): string
    {
        // Simple uptime based on when the application started
        // In a real application, you might store this in a persistent store
        $startFile = __DIR__ . '/../../../storage/.start_time';
        
        if (file_exists($startFile)) {
            $startTime = (int)file_get_contents($startFile);
        } else {
            $startTime = time();
            file_put_contents($startFile, $startTime);
        }
        
        $uptime = time() - $startTime;
        
        return $this->formatUptime($uptime);
    }
    
    private function measureExecutionTime(callable $callback): string
    {
        $start = microtime(true);
        $callback();
        $end = microtime(true);
        
        return round(($end - $start) * 1000, 2) . 'ms';
    }
    
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    private function parseMemoryLimit(string $limit): int
    {
        if ($limit === '-1') {
            return -1; // unlimited
        }
        
        $value = (int)$limit;
        $unit = strtoupper(substr($limit, -1));
        
        return match ($unit) {
            'K' => $value * 1024,
            'M' => $value * 1024 * 1024,
            'G' => $value * 1024 * 1024 * 1024,
            default => $value
        };
    }
    
    private function formatUptime(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds} seconds";
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            return "{$minutes} minutes";
        } elseif ($seconds < 86400) {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return "{$hours} hours, {$minutes} minutes";
        } else {
            $days = floor($seconds / 86400);
            $hours = floor(($seconds % 86400) / 3600);
            return "{$days} days, {$hours} hours";
        }
    }
}
