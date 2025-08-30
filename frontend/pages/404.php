<div class="text-center py-16">
    <div class="w-32 h-32 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-8">
        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.5-1.01-6-2.709M15 15v2a2 2 0 01-2 2H9a2 2 0 01-2-2v-2m8-6V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.092"></path>
        </svg>
    </div>
    
    <h1 class="text-6xl font-bold text-gray-900 mb-4">404</h1>
    <h2 class="text-2xl font-semibold text-gray-700 mb-4">Page Not Found</h2>
    <p class="text-gray-500 mb-8 max-w-md mx-auto">
        Sorry, we couldn't find the page you're looking for. The article may have been moved or deleted.
    </p>
    
    <div class="space-x-4">
        <a href="/" 
           class="inline-flex items-center px-6 py-3 bg-primary text-white font-medium rounded-lg hover:bg-primary-dark transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            Go Home
        </a>
        
        <a href="/?page=sources" 
           class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 11.172V5l-1-1z"></path>
            </svg>
            Browse Sources
        </a>
    </div>
    
    <div class="mt-12 max-w-lg mx-auto">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Popular Categories</h3>
        <div class="flex flex-wrap justify-center gap-2">
            <?php 
            $categories = getCategories();
            foreach ($categories as $slug => $name): 
            ?>
                <a href="/?category=<?= urlencode($slug) ?>" 
                   class="<?= getCategoryColor($slug) ?> px-3 py-1 text-sm font-medium rounded-full hover:opacity-80 transition-opacity">
                    <?= htmlspecialchars($name) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
