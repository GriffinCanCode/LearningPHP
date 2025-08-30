<?php

declare(strict_types=1);

namespace NewsAggregator\Domain\News;

use DateTimeImmutable;
use JsonSerializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class Source implements JsonSerializable
{
    public function __construct(
        public UuidInterface $id,
        public string $name,
        public string $url,
        public SourceType $type,
        public string $description,
        public array $configuration,
        public bool $isActive,
        public DateTimeImmutable $createdAt,
        public ?DateTimeImmutable $lastScrapedAt = null,
        public ?string $apiKey = null,
    ) {}
    
    public static function create(
        string $name,
        string $url,
        SourceType $type,
        string $description,
        array $configuration = [],
        ?string $apiKey = null
    ): self {
        return new self(
            id: Uuid::uuid4(),
            name: $name,
            url: $url,
            type: $type,
            description: $description,
            configuration: $configuration,
            isActive: true,
            createdAt: new DateTimeImmutable(),
            apiKey: $apiKey
        );
    }
    
    public function isApiSource(): bool
    {
        return $this->type === SourceType::API;
    }
    
    public function isRssSource(): bool
    {
        return $this->type === SourceType::RSS;
    }
    
    public function isScrapingSource(): bool
    {
        return $this->type === SourceType::SCRAPING;
    }
    
    public function requiresApiKey(): bool
    {
        return $this->isApiSource() && $this->apiKey !== null;
    }
    
    public function getScrapingSelectors(): array
    {
        return $this->configuration['selectors'] ?? [];
    }
    
    public function getRateLimitDelay(): int
    {
        return $this->configuration['rate_limit_delay'] ?? 2;
    }
    
    public function getMaxArticles(): int
    {
        return $this->configuration['max_articles'] ?? 50;
    }
    
    public function withUpdatedLastScrapedAt(DateTimeImmutable $timestamp): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            url: $this->url,
            type: $this->type,
            description: $this->description,
            configuration: $this->configuration,
            isActive: $this->isActive,
            createdAt: $this->createdAt,
            lastScrapedAt: $timestamp,
            apiKey: $this->apiKey
        );
    }
    
    public function activate(): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            url: $this->url,
            type: $this->type,
            description: $this->description,
            configuration: $this->configuration,
            isActive: true,
            createdAt: $this->createdAt,
            lastScrapedAt: $this->lastScrapedAt,
            apiKey: $this->apiKey
        );
    }
    
    public function deactivate(): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            url: $this->url,
            type: $this->type,
            description: $this->description,
            configuration: $this->configuration,
            isActive: false,
            createdAt: $this->createdAt,
            lastScrapedAt: $this->lastScrapedAt,
            apiKey: $this->apiKey
        );
    }
    
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id->toString(),
            'name' => $this->name,
            'url' => $this->url,
            'type' => $this->type->value,
            'description' => $this->description,
            'configuration' => $this->configuration,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'last_scraped_at' => $this->lastScrapedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
