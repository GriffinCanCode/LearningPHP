# NewsAggregator

A modern, specialized news aggregation platform built with PHP 8.4 and best practices. This application collects news from multiple sources using proprietary scraping techniques and REST API integrations, then presents them in a clean, modern web interface.

## Features

- **Multi-Source Aggregation**: Supports RSS feeds, REST APIs, and custom web scraping
- **Smart Deduplication**: Automatically detects and removes duplicate articles
- **Category Management**: Organizes news into technology, business, sports, politics, health, science, entertainment, and general categories
- **Search Functionality**: Full-text search across all articles
- **Modern UI**: Clean, responsive design built with Tailwind CSS
- **Performance Optimized**: Includes caching, rate limiting, and efficient database queries
- **Real-time Updates**: Configurable scraping schedules with monitoring
- **RESTful API**: Complete API for programmatic access

## Architecture

The project follows a clean, modular architecture with separation of concerns:

```
LearningPHP/
├── backend/           # PHP 8.4 API backend
│   ├── src/
│   │   ├── Application/    # Application services and use cases
│   │   ├── Domain/        # Domain entities and business logic
│   │   └── Infrastructure/ # External concerns (database, HTTP, etc.)
│   ├── public/        # Web server entry point
│   ├── config/        # Configuration files
│   ├── storage/       # Logs, cache, and temporary files
│   └── composer.json  # PHP dependencies
├── frontend/          # PHP-based web interface
│   ├── pages/         # Page templates
│   ├── includes/      # Shared components and functions
│   └── assets/        # CSS, JS, and other static assets
└── README.md
```

## Technology Stack

### Backend
- **PHP 8.4**: Latest PHP with modern syntax and performance improvements
- **Doctrine DBAL**: Database abstraction layer
- **Guzzle HTTP**: HTTP client for API calls and web scraping
- **Symfony Components**: DOM crawler, CSS selector, caching
- **Monolog**: Comprehensive logging system
- **FastRoute**: High-performance routing
- **Custom DI Container**: Dependency injection with auto-wiring

### Frontend
- **PHP 8.4**: Server-side rendering
- **Tailwind CSS**: Utility-first CSS framework
- **Vanilla JavaScript**: Progressive enhancement
- **Modern Web Standards**: Semantic HTML5, responsive design

### Database
- **MySQL 8.0+**: Relational database with full-text search
- **Optimized Schema**: Indexes for performance, foreign keys for integrity

## Installation

### Prerequisites
- PHP 8.4+
- MySQL 8.0+
- Composer
- Web server (Apache/Nginx) or PHP built-in server

### Backend Setup

1. **Install Dependencies**
   ```bash
   cd backend
   composer install
   ```

2. **Environment Configuration**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials and API keys
   ```

3. **Database Setup**
   ```bash
   # Create database
   mysql -u root -p -e "CREATE DATABASE news_aggregator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   
   # The application will auto-create tables on first run
   ```

4. **Start Backend Server**
   ```bash
   cd public
   php -S localhost:8000
   ```

### Frontend Setup

1. **Configure API Connection**
   ```bash
   # Edit frontend/includes/config.php
   # Set API_BASE_URL to your backend URL
   ```

2. **Set Permissions**
   ```bash
   chmod -R 755 frontend/
   mkdir -p frontend/cache
   chmod 755 frontend/cache
   ```

3. **Start Frontend Server**
   ```bash
   cd frontend
   php -S localhost:3000
   ```

### Production Deployment

For production, configure your web server to:
- Point backend document root to `backend/public/`
- Point frontend document root to `frontend/`
- Enable URL rewriting for clean URLs
- Set appropriate PHP settings (memory_limit, max_execution_time)
- Configure SSL/TLS

## Configuration

### News Sources

Add news sources through the database or API:

```php
// RSS Feed Example
INSERT INTO sources (id, name, url, type, description, configuration) VALUES
(UUID(), 'BBC News', 'http://feeds.bbci.co.uk/news/rss.xml', 'rss', 'BBC News RSS Feed', '{"max_articles": 50}');

// API Source Example  
INSERT INTO sources (id, name, url, type, description, configuration, api_key) VALUES
(UUID(), 'NewsAPI', 'https://newsapi.org/v2/top-headlines', 'api', 'NewsAPI Headlines', 
'{"country": "us", "pageSize": 50}', 'your_api_key_here');

// Web Scraping Example
INSERT INTO sources (id, name, url, type, description, configuration) VALUES
(UUID(), 'TechCrunch', 'https://techcrunch.com/', 'scraping', 'TechCrunch Articles',
'{"selectors": {"title": "h2.post-title", "content": ".post-content", "link": "h2.post-title a"}, "max_articles": 20}');
```

### Scraping Configuration

Configure scraping behavior in source configuration:

```json
{
  "max_articles": 50,
  "rate_limit_delay": 2,
  "selectors": {
    "title": ".article-title",
    "content": ".article-content", 
    "summary": ".article-summary",
    "author": ".article-author",
    "image": ".article-image img",
    "published": ".article-date"
  }
}
```

## API Endpoints

### News
- `GET /api/news` - Get latest news (paginated)
- `GET /api/news/{id}` - Get specific article
- `GET /api/news/category/{category}` - Get news by category
- `GET /api/news/source/{source}` - Get news by source
- `GET /api/news/search/{query}` - Search articles

### Sources
- `GET /api/sources` - List all sources
- `POST /api/sources` - Create new source
- `GET /api/sources/{id}` - Get source details
- `PUT /api/sources/{id}` - Update source
- `DELETE /api/sources/{id}` - Delete source

### Scraping
- `POST /api/scrape/all` - Trigger scraping for all sources
- `POST /api/scrape/source/{id}` - Scrape specific source

## Development

### Code Style
The project follows PSR-12 coding standards with additional rules:
- Use `declare(strict_types=1)` in all PHP files
- Prefer readonly classes and properties where applicable
- Use PHP 8.4 features (enums, union types, constructor promotion)
- Document complex business logic

### Testing
```bash
cd backend
composer test         # Run PHPUnit tests
composer psalm        # Static analysis
composer phpstan      # Additional static analysis
composer cs           # Check code style
composer cbf          # Fix code style
```

### Adding New Sources

1. **Create Source Configuration**
   ```php
   $source = Source::create(
       name: 'Example News',
       url: 'https://example.com/rss',
       type: SourceType::RSS,
       description: 'Example news source'
   );
   ```

2. **Implement Custom Scraper** (if needed)
   ```php
   class ExampleScraper implements ScraperInterface {
       public function scrape(Source $source): array {
           // Custom scraping logic
       }
   }
   ```

## Monitoring

### Logs
Logs are stored in `backend/storage/logs/app.log` with different levels:
- **INFO**: Successful operations
- **WARNING**: Non-critical issues  
- **ERROR**: Failed operations
- **DEBUG**: Detailed debugging info

### Performance Monitoring
Monitor these key metrics:
- Scraping success/failure rates
- Article processing time
- Database query performance
- Cache hit rates
- Memory usage

## Troubleshooting

### Common Issues

**Database Connection Errors**
- Check database credentials in `.env`
- Ensure MySQL is running
- Verify database exists and has proper permissions

**Scraping Failures**
- Check source URL accessibility
- Verify selectors for web scraping
- Monitor rate limiting
- Check API key validity

**Performance Issues**
- Enable PHP OPcache
- Optimize database indexes
- Configure appropriate cache TTL
- Monitor memory usage

## License

This project is open source and available under the [MIT License](LICENSE).

## Contributing

1. Fork the repository
2. Create a feature branch
3. Write tests for new functionality
4. Ensure code passes all quality checks
5. Submit a pull request

## Roadmap

- [ ] WebSocket support for real-time updates
- [ ] Advanced ML-based categorization
- [ ] Social media source integration
- [ ] Mobile app API
- [ ] Advanced analytics dashboard
- [ ] Multi-language support
- [ ] Content summarization AI
- [ ] User personalization features
