<?php

declare(strict_types=1);

/**
 * Make API request to backend
 */
function apiRequest(string $endpoint, string $method = 'GET', array $data = []): array|false
{
    $url = API_BASE_URL . $endpoint;
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);
    
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response === false || $httpCode >= 400) {
        return false;
    }
    
    return json_decode($response, true) ?? [];
}

/**
 * Get news articles with optional filtering
 */
function getNews(string $category = null, string $source = null, string $search = null, int $page = 1): array
{
    $cacheKey = md5("news_{$category}_{$source}_{$search}_{$page}");
    
    // Check cache first
    if ($cached = getCache($cacheKey)) {
        return $cached;
    }
    
    $endpoint = '/news';
    $params = [];
    
    if ($category) {
        $endpoint = "/news/category/{$category}";
    } elseif ($source) {
        $endpoint = "/news/source/{$source}";
    } elseif ($search) {
        $endpoint = "/news/search/" . urlencode($search);
    }
    
    if ($page > 1) {
        $params['page'] = $page;
    }
    
    if (!empty($params)) {
        $endpoint .= '?' . http_build_query($params);
    }
    
    $result = apiRequest($endpoint);
    
    if ($result) {
        setCache($cacheKey, $result, CACHE_DURATION);
    }
    
    return $result ?: ['articles' => [], 'total' => 0];
}

/**
 * Get news sources
 */
function getSources(): array
{
    $cacheKey = 'sources';
    
    if ($cached = getCache($cacheKey)) {
        return $cached;
    }
    
    $result = apiRequest('/sources');
    
    if ($result) {
        setCache($cacheKey, $result, CACHE_DURATION);
    }
    
    return $result ?: [];
}

/**
 * Get single article by ID
 */
function getArticle(string $id): array|null
{
    $cacheKey = "article_{$id}";
    
    if ($cached = getCache($cacheKey)) {
        return $cached;
    }
    
    $result = apiRequest("/news/{$id}");
    
    if ($result) {
        setCache($cacheKey, $result, CACHE_DURATION);
        return $result;
    }
    
    return null;
}

/**
 * Simple file-based caching
 */
function getCache(string $key): mixed
{
    $cacheFile = __DIR__ . "/../cache/{$key}.cache";
    
    if (!file_exists($cacheFile)) {
        return null;
    }
    
    $data = file_get_contents($cacheFile);
    $cache = unserialize($data);
    
    if ($cache['expires'] < time()) {
        unlink($cacheFile);
        return null;
    }
    
    return $cache['data'];
}

function setCache(string $key, mixed $data, int $ttl): void
{
    $cacheDir = __DIR__ . '/../cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    $cacheFile = "{$cacheDir}/{$key}.cache";
    $cache = [
        'data' => $data,
        'expires' => time() + $ttl,
    ];
    
    file_put_contents($cacheFile, serialize($cache));
}

/**
 * Format date for display
 */
function formatDate(string $date): string
{
    return date('M j, Y \a\t g:i A', strtotime($date));
}

/**
 * Truncate text to specified length
 */
function truncate(string $text, int $length = 150): string
{
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . '...';
}

/**
 * Get category color class for Tailwind
 */
function getCategoryColor(string $category): string
{
    return match(strtolower($category)) {
        'technology' => 'bg-blue-100 text-blue-800',
        'business' => 'bg-green-100 text-green-800',
        'sports' => 'bg-yellow-100 text-yellow-800',
        'politics' => 'bg-red-100 text-red-800',
        'health' => 'bg-purple-100 text-purple-800',
        'science' => 'bg-cyan-100 text-cyan-800',
        'entertainment' => 'bg-orange-100 text-orange-800',
        default => 'bg-gray-100 text-gray-800',
    };
}

/**
 * Get available categories
 */
function getCategories(): array
{
    return [
        'technology' => 'Technology',
        'business' => 'Business',
        'sports' => 'Sports',
        'politics' => 'Politics',
        'health' => 'Health',
        'science' => 'Science',
        'entertainment' => 'Entertainment',
        'general' => 'General',
    ];
}
