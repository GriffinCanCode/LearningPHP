<?php
$articleId = $_GET['id'] ?? null;

if (!$articleId) {
    header('Location: /');
    exit;
}

$article = getArticle($articleId);

if (!$article) {
    include __DIR__ . '/404.php';
    return;
}
?>

<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
            <li><a href="/" class="hover:text-primary">Home</a></li>
            <li>/</li>
            <li><a href="/?category=<?= urlencode($article['category']['slug'] ?? '') ?>" class="hover:text-primary">
                <?= htmlspecialchars($article['category']['name'] ?? 'General') ?>
            </a></li>
            <li>/</li>
            <li class="text-gray-900">Article</li>
        </ol>
    </nav>

    <article class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Article Header -->
        <div class="p-8">
            <div class="flex items-center justify-between mb-4">
                <span class="<?= getCategoryColor($article['category']['name'] ?? 'general') ?> px-3 py-1 text-sm font-medium rounded-full">
                    <?= htmlspecialchars($article['category']['name'] ?? 'General') ?>
                </span>
                <time class="text-gray-500 text-sm">
                    <?= formatDate($article['published_at']) ?>
                </time>
            </div>

            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6 leading-tight">
                <?= htmlspecialchars($article['title']) ?>
            </h1>

            <!-- Article Meta -->
            <div class="flex items-center justify-between border-b border-gray-200 pb-6 mb-6">
                <div class="flex items-center space-x-4 text-sm text-gray-600">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 11.172V5l-1-1z"></path>
                        </svg>
                        <span><?= htmlspecialchars($article['source']['name'] ?? 'Unknown') ?></span>
                    </div>
                    
                    <?php if (!empty($article['author'])): ?>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span><?= htmlspecialchars($article['author']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <a href="<?= htmlspecialchars($article['url']) ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors">
                    Read Original
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                </a>
            </div>

            <!-- Article Image -->
            <?php if (!empty($article['image_url'])): ?>
                <div class="mb-8">
                    <img src="<?= htmlspecialchars($article['image_url']) ?>" 
                         alt="<?= htmlspecialchars($article['title']) ?>" 
                         class="w-full h-64 md:h-80 object-cover rounded-lg"
                         onerror="this.style.display='none';">
                </div>
            <?php endif; ?>

            <!-- Article Summary -->
            <?php if (!empty($article['summary'])): ?>
                <div class="bg-gray-50 p-6 rounded-lg mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Summary</h2>
                    <p class="text-gray-700 leading-relaxed">
                        <?= nl2br(htmlspecialchars($article['summary'])) ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Article Content -->
            <div class="prose prose-lg max-w-none">
                <?php if (!empty($article['content'])): ?>
                    <div class="text-gray-800 leading-relaxed">
                        <?= nl2br(htmlspecialchars($article['content'])) ?>
                    </div>
                <?php else: ?>
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 my-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    Full content is not available. Please visit the original article for the complete story.
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tags -->
            <?php if (!empty($article['tags'])): ?>
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">Tags</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($article['tags'] as $tag): ?>
                            <span class="bg-gray-100 text-gray-700 px-3 py-1 text-xs font-medium rounded-full">
                                #<?= htmlspecialchars($tag) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </article>

    <!-- Related Articles Section -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Articles</h2>
        
        <?php
        // Get related articles from same category
        $relatedNews = getNews($article['category']['slug'] ?? null, null, null, 1);
        $relatedArticles = array_filter(
            $relatedNews['articles'] ?? [], 
            fn($a) => $a['id'] !== $article['id']
        );
        $relatedArticles = array_slice($relatedArticles, 0, 3);
        ?>
        
        <?php if (!empty($relatedArticles)): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($relatedArticles as $related): ?>
                    <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <?php if (!empty($related['image_url'])): ?>
                            <img src="<?= htmlspecialchars($related['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($related['title']) ?>" 
                                 class="w-full h-32 object-cover"
                                 onerror="this.style.display='none';">
                        <?php endif; ?>
                        
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">
                                <a href="/?page=article&id=<?= urlencode($related['id']) ?>" 
                                   class="hover:text-primary transition-colors">
                                    <?= htmlspecialchars($related['title']) ?>
                                </a>
                            </h3>
                            
                            <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                                <?= htmlspecialchars(truncate($related['summary'], 100)) ?>
                            </p>
                            
                            <time class="text-xs text-gray-500">
                                <?= formatDate($related['published_at']) ?>
                            </time>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500">No related articles found.</p>
        <?php endif; ?>
    </div>
</div>
