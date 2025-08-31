<?php

declare(strict_types=1);

namespace NewsAggregator\Infrastructure\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;

final class DatabaseManager
{
    private ?Connection $connection = null;
    
    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $database,
        private readonly string $username,
        private readonly string $password,
        private readonly string $driver = 'mysql'
    ) {}
    
    public function getConnection(): Connection
    {
        if ($this->connection === null) {
            $this->connection = $this->createConnection();
        }
        
        return $this->connection;
    }
    
    public function initialize(): void
    {
        $this->createTables();
        $this->seedDefaultData();
    }
    
    private function createConnection(): Connection
    {
        if ($this->driver === 'sqlite') {
            $connectionParams = [
                'driver' => 'pdo_sqlite',
                'path' => $this->host, // For SQLite, host contains the file path
            ];
        } else {
            $connectionParams = [
                'dbname' => $this->database,
                'user' => $this->username,
                'password' => $this->password,
                'host' => $this->host,
                'port' => $this->port,
                'driver' => 'pdo_mysql',
                'charset' => 'utf8mb4',
            ];
        }
        
        try {
            return DriverManager::getConnection($connectionParams);
        } catch (Exception $e) {
            throw new DatabaseConnectionException(
                "Failed to connect to database: {$e->getMessage()}", 
                0, 
                $e
            );
        }
    }
    
    private function createTables(): void
    {
        $connection = $this->getConnection();
        
        // Sources table
        if ($this->driver === 'sqlite') {
            $connection->executeStatement('
                CREATE TABLE IF NOT EXISTS sources (
                    id TEXT PRIMARY KEY,
                    name TEXT NOT NULL UNIQUE,
                    url TEXT NOT NULL,
                    type TEXT NOT NULL CHECK (type IN ("api", "rss", "scraping")),
                    description TEXT,
                    configuration TEXT,
                    api_key TEXT,
                    is_active INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    last_scraped_at DATETIME
                )
            ');
            $connection->executeStatement('CREATE INDEX IF NOT EXISTS idx_sources_type ON sources(type)');
            $connection->executeStatement('CREATE INDEX IF NOT EXISTS idx_sources_active ON sources(is_active)');
        } else {
            $connection->executeStatement('
                CREATE TABLE IF NOT EXISTS sources (
                    id CHAR(36) PRIMARY KEY,
                    name VARCHAR(255) NOT NULL UNIQUE,
                    url VARCHAR(500) NOT NULL,
                    type ENUM("api", "rss", "scraping") NOT NULL,
                    description TEXT,
                    configuration JSON,
                    api_key VARCHAR(255) NULL,
                    is_active BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    last_scraped_at TIMESTAMP NULL,
                    INDEX idx_sources_type (type),
                    INDEX idx_sources_active (is_active)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');
        }
        
        // Categories table
        if ($this->driver === 'sqlite') {
            $connection->executeStatement('
                CREATE TABLE IF NOT EXISTS categories (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL UNIQUE,
                    slug TEXT NOT NULL UNIQUE,
                    color TEXT DEFAULT "#6B7280",
                    description TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ');
            $connection->executeStatement('CREATE INDEX IF NOT EXISTS idx_categories_slug ON categories(slug)');
        } else {
            $connection->executeStatement('
                CREATE TABLE IF NOT EXISTS categories (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL UNIQUE,
                    slug VARCHAR(100) NOT NULL UNIQUE,
                    color VARCHAR(7) DEFAULT "#6B7280",
                    description VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_categories_slug (slug)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');
        }
        
        // Articles table
        if ($this->driver === 'sqlite') {
            $connection->executeStatement('
                CREATE TABLE IF NOT EXISTS articles (
                    id TEXT PRIMARY KEY,
                    title TEXT NOT NULL,
                    content TEXT,
                    summary TEXT,
                    url TEXT NOT NULL,
                    image_url TEXT,
                    author TEXT,
                    source_id TEXT NOT NULL,
                    category_id INTEGER NOT NULL,
                    content_hash TEXT,
                    published_at DATETIME NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (source_id) REFERENCES sources(id) ON DELETE CASCADE,
                    FOREIGN KEY (category_id) REFERENCES categories(id)
                )
            ');
            $connection->executeStatement('CREATE INDEX IF NOT EXISTS idx_articles_published ON articles(published_at DESC)');
            $connection->executeStatement('CREATE INDEX IF NOT EXISTS idx_articles_source ON articles(source_id)');
            $connection->executeStatement('CREATE INDEX IF NOT EXISTS idx_articles_category ON articles(category_id)');
            $connection->executeStatement('CREATE INDEX IF NOT EXISTS idx_articles_content_hash ON articles(content_hash)');
        } else {
            $connection->executeStatement('
                CREATE TABLE IF NOT EXISTS articles (
                    id CHAR(36) PRIMARY KEY,
                    title VARCHAR(500) NOT NULL,
                    content LONGTEXT,
                    summary TEXT,
                    url VARCHAR(1000) NOT NULL,
                    image_url VARCHAR(1000),
                    author VARCHAR(255),
                    source_id CHAR(36) NOT NULL,
                    category_id INT NOT NULL,
                    content_hash VARCHAR(32),
                    published_at TIMESTAMP NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (source_id) REFERENCES sources(id) ON DELETE CASCADE,
                    FOREIGN KEY (category_id) REFERENCES categories(id),
                    INDEX idx_articles_published (published_at DESC),
                    INDEX idx_articles_source (source_id),
                    INDEX idx_articles_category (category_id),
                    INDEX idx_articles_content_hash (content_hash),
                    INDEX idx_articles_url (url(255)),
                    FULLTEXT idx_articles_search (title, content, summary)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');
        }
        
        // Article tags table
        if ($this->driver === 'sqlite') {
            $connection->executeStatement('
                CREATE TABLE IF NOT EXISTS article_tags (
                    article_id TEXT NOT NULL,
                    tag TEXT NOT NULL,
                    PRIMARY KEY (article_id, tag),
                    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
                )
            ');
            $connection->executeStatement('CREATE INDEX IF NOT EXISTS idx_tags_tag ON article_tags(tag)');
        } else {
            $connection->executeStatement('
                CREATE TABLE IF NOT EXISTS article_tags (
                    article_id CHAR(36) NOT NULL,
                    tag VARCHAR(100) NOT NULL,
                    PRIMARY KEY (article_id, tag),
                    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
                    INDEX idx_tags_tag (tag)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');
        }
        
        // Scraping logs table
        if ($this->driver === 'sqlite') {
            $connection->executeStatement('
                CREATE TABLE IF NOT EXISTS scraping_logs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    source_id TEXT NOT NULL,
                    status TEXT NOT NULL CHECK (status IN ("started", "completed", "failed")),
                    articles_found INTEGER DEFAULT 0,
                    articles_new INTEGER DEFAULT 0,
                    error_message TEXT,
                    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    completed_at DATETIME,
                    FOREIGN KEY (source_id) REFERENCES sources(id) ON DELETE CASCADE
                )
            ');
            $connection->executeStatement('CREATE INDEX IF NOT EXISTS idx_logs_source ON scraping_logs(source_id)');
            $connection->executeStatement('CREATE INDEX IF NOT EXISTS idx_logs_status ON scraping_logs(status)');
            $connection->executeStatement('CREATE INDEX IF NOT EXISTS idx_logs_started ON scraping_logs(started_at DESC)');
        } else {
            $connection->executeStatement('
                CREATE TABLE IF NOT EXISTS scraping_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    source_id CHAR(36) NOT NULL,
                    status ENUM("started", "completed", "failed") NOT NULL,
                    articles_found INT DEFAULT 0,
                    articles_new INT DEFAULT 0,
                    error_message TEXT NULL,
                    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    completed_at TIMESTAMP NULL,
                    FOREIGN KEY (source_id) REFERENCES sources(id) ON DELETE CASCADE,
                    INDEX idx_logs_source (source_id),
                    INDEX idx_logs_status (status),
                    INDEX idx_logs_started (started_at DESC)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');
        }
    }
    
    private function seedDefaultData(): void
    {
        $connection = $this->getConnection();
        
        // Check if categories exist
        $categoryCount = $connection->fetchOne('SELECT COUNT(*) FROM categories');
        
        if ($categoryCount == 0) {
            $categories = [
                ['Technology', 'technology', '#3B82F6', 'Technology and software news'],
                ['Business', 'business', '#10B981', 'Business and finance news'],
                ['Sports', 'sports', '#F59E0B', 'Sports and athletics news'],
                ['Politics', 'politics', '#EF4444', 'Political news and analysis'],
                ['Health', 'health', '#8B5CF6', 'Health and medical news'],
                ['Science', 'science', '#06B6D4', 'Science and research news'],
                ['Entertainment', 'entertainment', '#F97316', 'Entertainment and celebrity news'],
                ['General', 'general', '#6B7280', 'General news and current events'],
            ];
            
            foreach ($categories as [$name, $slug, $color, $description]) {
                $connection->insert('categories', [
                    'name' => $name,
                    'slug' => $slug,
                    'color' => $color,
                    'description' => $description,
                ]);
            }
        }
        
        // Check if sample sources exist
        $sourceCount = $connection->fetchOne('SELECT COUNT(*) FROM sources');
        
        if ($sourceCount == 0) {
            // Add some sample sources
            $sources = [
                [
                    'id' => '550e8400-e29b-41d4-a716-446655440000',
                    'name' => 'BBC News RSS',
                    'url' => 'http://feeds.bbci.co.uk/news/rss.xml',
                    'type' => 'rss',
                    'description' => 'BBC News RSS feed for general news',
                    'configuration' => json_encode(['max_articles' => 50]),
                ],
                [
                    'id' => '550e8400-e29b-41d4-a716-446655440001',
                    'name' => 'NewsAPI',
                    'url' => 'https://newsapi.org/v2/top-headlines',
                    'type' => 'api',
                    'description' => 'NewsAPI for breaking news headlines',
                    'configuration' => json_encode([
                        'country' => 'us',
                        'pageSize' => 50,
                        'endpoint_type' => 'headlines'
                    ]),
                ],
            ];
            
            foreach ($sources as $source) {
                $connection->insert('sources', $source);
            }
        }
    }
}

class DatabaseConnectionException extends \Exception
{
}
