<?php

declare(strict_types=1);

namespace NewsAggregator\Domain\News;

use JsonSerializable;

final readonly class Category implements JsonSerializable
{
    private function __construct(
        public string $name,
        public string $slug,
        public string $color,
        public string $description
    ) {}
    
    public static function create(string $name, string $description = '', string $color = '#6B7280'): self
    {
        return new self(
            name: $name,
            slug: strtolower(str_replace(' ', '-', $name)),
            color: $color,
            description: $description ?: $name
        );
    }
    
    public static function technology(): self
    {
        return self::create('Technology', 'Technology and software news', '#3B82F6');
    }
    
    public static function business(): self
    {
        return self::create('Business', 'Business and finance news', '#10B981');
    }
    
    public static function sports(): self
    {
        return self::create('Sports', 'Sports and athletics news', '#F59E0B');
    }
    
    public static function politics(): self
    {
        return self::create('Politics', 'Political news and analysis', '#EF4444');
    }
    
    public static function health(): self
    {
        return self::create('Health', 'Health and medical news', '#8B5CF6');
    }
    
    public static function science(): self
    {
        return self::create('Science', 'Science and research news', '#06B6D4');
    }
    
    public static function entertainment(): self
    {
        return self::create('Entertainment', 'Entertainment and celebrity news', '#F97316');
    }
    
    public static function general(): self
    {
        return self::create('General', 'General news and current events', '#6B7280');
    }
    
    public static function getDefaultCategories(): array
    {
        return [
            self::technology(),
            self::business(),
            self::sports(),
            self::politics(),
            self::health(),
            self::science(),
            self::entertainment(),
            self::general(),
        ];
    }
    
    public function matches(string $keyword): bool
    {
        $keyword = strtolower($keyword);
        
        return str_contains(strtolower($this->name), $keyword) ||
               str_contains(strtolower($this->description), $keyword) ||
               $this->slug === $keyword;
    }
    
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'color' => $this->color,
            'description' => $this->description,
        ];
    }
}
