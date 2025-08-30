<?php

declare(strict_types=1);

// Configuration for the frontend
define('API_BASE_URL', 'http://localhost:8000/api');
define('ITEMS_PER_PAGE', 12);
define('CACHE_DURATION', 300); // 5 minutes

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start session
session_start();

// Set timezone
date_default_timezone_set('UTC');

// CSRF protection helper
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_token(): string {
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(string $token): bool {
    return hash_equals($_SESSION['csrf_token'], $token);
}
