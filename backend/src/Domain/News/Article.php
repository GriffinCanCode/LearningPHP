<?php

declare(strict_types=1);

namespace NewsAggregator\Domain\News;

use DateTimeImmutable;
use JsonSerializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class Article implements JsonSerializable
{
    public function __construct(
        public UuidInterface $id,
        public string $title,
        public string $content,
        public string $summary,
        public string $url,
        public string $imageUrl,
        public string $author,
        public Source $source,
        public Category $category,
        public DateTimeImmutable $publishedAt,
        public DateTimeImmutable $createdAt,
        public array $tags = [],
        public ?string $contentHash = null,
    ) {}
    
    public static function create(
        string $title,
        string $content,
        string $summary,
        string $url,
        string $imageUrl,
        string $author,
        Source $source,
        Category $category,
        DateTimeImmutable $publishedAt,
        array $tags = []
    ): self {
        return new self(
            id: Uuid::uuid4(),
            title: $title,
            content: $content,
            summary: $summary,
            url: $url,
            imageUrl: $imageUrl,
            author: $author,
            source: $source,
            category: $category,
            publishedAt: $publishedAt,
            createdAt: new DateTimeImmutable(),
            tags: $tags,
            contentHash: md5($title . $content)
        );
    }
    
    public function isDuplicate(Article $other): bool
    {
        return $this->contentHash === $other->contentHash 
            || $this->url === $other->url
            || (similar_text($this->title, $other->title) / strlen($this->title)) > 0.85;
    }
    
    public function isFromSource(string $sourceName): bool
    {
        return $this->source->name === $sourceName;
    }
    
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags, true);
    }
    
    public function withUpdatedContent(string $content, string $summary): self
    {
        return new self(
            id: $this->id,
            title: $this->title,
            content: $content,
            summary: $summary,
            url: $this->url,
            imageUrl: $this->imageUrl,
            author: $this->author,
            source: $this->source,
            category: $this->category,
            publishedAt: $this->publishedAt,
            createdAt: $this->createdAt,
            tags: $this->tags,
            contentHash: md5($this->title . $content)
        );
    }
    
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id->toString(),
            'title' => $this->title,
            'content' => $this->content,
            'summary' => $this->summary,
            'url' => $this->url,
            'image_url' => $this->imageUrl,
            'author' => $this->author,
            'source' => $this->source,
            'category' => $this->category,
            'published_at' => $this->publishedAt->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'tags' => $this->tags,
        ];
    }
}
