<?php
$sources = getSources();
?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-4">News Sources</h1>
    <p class="text-gray-600">Our news aggregator pulls from these trusted sources to bring you comprehensive coverage.</p>
</div>

<?php if (empty($sources)): ?>
    <div class="text-center py-12">
        <div class="w-24 h-24 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 11.172V5l-1-1z"></path>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No sources configured</h3>
        <p class="text-gray-500">Sources will appear here once they are configured in the backend.</p>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($sources as $source): ?>
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <?= htmlspecialchars($source['name']) ?>
                    </h3>
                    
                    <div class="flex items-center space-x-2">
                        <span class="<?= $source['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?> px-2 py-1 text-xs font-medium rounded-full">
                            <?= $source['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                        
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 text-xs font-medium rounded-full">
                            <?= ucfirst($source['type']) ?>
                        </span>
                    </div>
                </div>
                
                <p class="text-gray-600 text-sm mb-4">
                    <?= htmlspecialchars($source['description']) ?>
                </p>
                
                <div class="flex items-center justify-between text-sm text-gray-500">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                        <a href="<?= htmlspecialchars($source['url']) ?>" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="hover:text-primary">
                            Visit Source
                        </a>
                    </div>
                    
                    <?php if (isset($source['last_scraped_at']) && $source['last_scraped_at']): ?>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Last: <?= formatDate($source['last_scraped_at']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="/?source=<?= urlencode($source['name']) ?>" 
                       class="inline-flex items-center text-primary hover:text-primary-dark text-sm font-medium">
                        View Articles from this Source
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="mt-12 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-medium text-blue-900 mb-2">Source Types</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <h4 class="font-medium text-blue-800 mb-1">API Sources</h4>
                <p class="text-blue-700">Real-time data from REST APIs with JSON responses</p>
            </div>
            <div>
                <h4 class="font-medium text-blue-800 mb-1">RSS Feeds</h4>
                <p class="text-blue-700">Syndicated content from RSS and Atom feeds</p>
            </div>
            <div>
                <h4 class="font-medium text-blue-800 mb-1">Web Scraping</h4>
                <p class="text-blue-700">Custom scrapers with CSS selectors and rate limiting</p>
            </div>
        </div>
    </div>
<?php endif; ?>
