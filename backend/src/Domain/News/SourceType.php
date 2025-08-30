<?php

declare(strict_types=1);

namespace NewsAggregator\Domain\News;

enum SourceType: string
{
    case API = 'api';
    case RSS = 'rss';
    case SCRAPING = 'scraping';
    
    public function getDescription(): string
    {
        return match($this) {
            self::API => 'REST API with JSON responses',
            self::RSS => 'RSS/Atom feed',
            self::SCRAPING => 'Web scraping with selectors',
        };
    }
    
    public function requiresConfiguration(): bool
    {
        return match($this) {
            self::API => true,
            self::RSS => false,
            self::SCRAPING => true,
        };
    }
    
    public function supportsRealTime(): bool
    {
        return match($this) {
            self::API => true,
            self::RSS => false,
            self::SCRAPING => false,
        };
    }
}
