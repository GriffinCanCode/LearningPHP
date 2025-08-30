<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$page = $_GET['page'] ?? 'home';
$category = $_GET['category'] ?? null;
$search = $_GET['search'] ?? null;

// Basic routing
$allowedPages = ['home', 'category', 'article', 'sources', 'about'];
if (!in_array($page, $allowedPages)) {
    $page = 'home';
}

$pageTitle = match($page) {
    'home' => 'Latest News',
    'category' => 'Category: ' . ucfirst($category ?? 'All'),
    'article' => 'Article Details',
    'sources' => 'News Sources',
    'about' => 'About',
    default => 'News Aggregator'
};

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - News Aggregator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1E40AF',
                        secondary: '#7C3AED',
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-900">
                        <a href="/" class="hover:text-primary">NewsAggregator</a>
                    </h1>
                </div>
                
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="/" class="<?= $page === 'home' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700' ?> px-3 py-2 text-sm font-medium">
                            Home
                        </a>
                        <a href="/?page=sources" class="<?= $page === 'sources' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700' ?> px-3 py-2 text-sm font-medium">
                            Sources
                        </a>
                        <a href="/?page=about" class="<?= $page === 'about' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700' ?> px-3 py-2 text-sm font-medium">
                            About
                        </a>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <form method="GET" class="relative">
                        <input type="hidden" name="page" value="home">
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Search news..." 
                            value="<?= htmlspecialchars($search ?? '') ?>"
                            class="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                        <button type="submit" class="absolute right-2 top-2 text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </nav>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php
        // Load the requested page
        $pageFile = __DIR__ . "/pages/{$page}.php";
        if (file_exists($pageFile)) {
            include $pageFile;
        } else {
            include __DIR__ . '/pages/404.php';
        }
        ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center text-gray-500 text-sm">
                <p>&copy; <?= date('Y') ?> News Aggregator. Built with PHP 8.4 and modern web technologies.</p>
            </div>
        </div>
    </footer>

    <script src="/assets/js/main.js"></script>
</body>
</html>
