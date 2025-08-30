<?php
$currentCategory = $_GET['category'] ?? null;
$currentSearch = $_GET['search'] ?? null;
$currentPage = max(1, intval($_GET['page'] ?? 1));

// Get news data
$newsData = getNews($currentCategory, null, $currentSearch, $currentPage);
$articles = $newsData['articles'] ?? [];
$totalArticles = $newsData['total'] ?? 0;
$totalPages = ceil($totalArticles / ITEMS_PER_PAGE);

$categories = getCategories();
?>

<!-- Category Filter Bar -->
<div class="mb-8">
    <div class="flex flex-wrap gap-2">
        <a href="/" 
           class="<?= $currentCategory === null ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50' ?> px-4 py-2 rounded-full text-sm font-medium border border-gray-200 transition-colors">
            All Categories
        </a>
        <?php foreach ($categories as $slug => $name): ?>
            <a href="/?category=<?= urlencode($slug) ?>" 
               class="<?= $currentCategory === $slug ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50' ?> px-4 py-2 rounded-full text-sm font-medium border border-gray-200 transition-colors">
                <?= htmlspecialchars($name) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php if ($currentSearch): ?>
    <div class="mb-6">
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
            <div class="flex">
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        Showing search results for: <strong><?= htmlspecialchars($currentSearch) ?></strong>
                        <a href="/" class="ml-2 text-blue-600 hover:text-blue-800 underline">Clear search</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($currentCategory): ?>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">
            <?= htmlspecialchars($categories[$currentCategory] ?? ucfirst($currentCategory)) ?> News
        </h2>
        <p class="text-gray-600 mt-1"><?= $totalArticles ?> articles found</p>
    </div>
<?php endif; ?>

<!-- News Grid -->
<?php if (empty($articles)): ?>
    <div class="text-center py-12">
        <div class="w-24 h-24 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No articles found</h3>
        <p class="text-gray-500">Try adjusting your search or category filter.</p>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <?php foreach ($articles as $article): ?>
            <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                <?php if (!empty($article['image_url'])): ?>
                    <div class="aspect-w-16 aspect-h-9">
                        <img src="<?= htmlspecialchars($article['image_url']) ?>" 
                             alt="<?= htmlspecialchars($article['title']) ?>" 
                             class="w-full h-48 object-cover"
                             onerror="this.style.display='none';">
                    </div>
                <?php endif; ?>
                
                <div class="p-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="<?= getCategoryColor($article['category']['name'] ?? 'general') ?> px-2 py-1 text-xs font-medium rounded-full">
                            <?= htmlspecialchars($article['category']['name'] ?? 'General') ?>
                        </span>
                        <time class="text-xs text-gray-500">
                            <?= formatDate($article['published_at']) ?>
                        </time>
                    </div>
                    
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                        <a href="/?page=article&id=<?= urlencode($article['id']) ?>" 
                           class="hover:text-primary transition-colors">
                            <?= htmlspecialchars($article['title']) ?>
                        </a>
                    </h3>
                    
                    <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                        <?= htmlspecialchars(truncate($article['summary'])) ?>
                    </p>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center text-xs text-gray-500">
                            <span><?= htmlspecialchars($article['source']['name'] ?? 'Unknown') ?></span>
                            <?php if (!empty($article['author'])): ?>
                                <span class="mx-1">•</span>
                                <span><?= htmlspecialchars($article['author']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <a href="<?= htmlspecialchars($article['url']) ?>" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="text-primary hover:text-primary-dark text-sm font-medium">
                            Read More →
                        </a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex justify-center items-center space-x-2">
            <?php if ($currentPage > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>" 
                   class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Previous
                </a>
            <?php endif; ?>
            
            <?php
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
            
            for ($i = $startPage; $i <= $endPage; $i++):
            ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                   class="<?= $i === $currentPage ? 'bg-primary text-white border-primary' : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50' ?> px-3 py-2 text-sm font-medium border rounded-md">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>" 
                   class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Next
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
