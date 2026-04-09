<?php

/**
 * MovieQuack REST API v1 — entry point / router
 *
 * All /api/v1/* requests are rewritten here by .htaccess.
 * The router parses the path and delegates to the matching handler file.
 *
 * Route map:
 *   /api/v1/auth/*        → auth.php
 *   /api/v1/movies/*      → movies.php
 *   /api/v1/users/*       → users.php
 *   /api/v1/tags/*        → tags.php
 *   /api/v1/posts/*       → posts.php
 *   /api/v1/feed          → feed.php
 *   /api/v1/mymovies      → mymovies.php
 *   /api/v1/dashboard     → dashboard.php
 *   /api/v1/buffet        → buffet.php
 *   /api/v1/list          → list.php
 */

// Bootstrap: session + db + app functions
require_once __DIR__ . '/../../db_functions.php';
require_once __DIR__ . '/../../functions.php';

// API utilities (CORS headers sent here, preflight handled here)
require_once __DIR__ . '/base.php';

// Parse path segments after /api/v1/
$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base     = '/api/v1/';
$relative = '';
if (strpos($uri, $base) === 0) {
    $relative = substr($uri, strlen($base));
}
$relative = trim($relative, '/');
$segments = $relative !== '' ? explode('/', $relative) : [];

$resource = $segments[0] ?? '';

switch ($resource) {
    case 'auth':
        require __DIR__ . '/auth.php';
        break;
    case 'movies':
        require __DIR__ . '/movies.php';
        break;
    case 'users':
        require __DIR__ . '/users.php';
        break;
    case 'tags':
        require __DIR__ . '/tags.php';
        break;
    case 'posts':
        require __DIR__ . '/posts.php';
        break;
    case 'feed':
        require __DIR__ . '/feed.php';
        break;
    case 'mymovies':
        require __DIR__ . '/mymovies.php';
        break;
    case 'dashboard':
        require __DIR__ . '/dashboard.php';
        break;
    case 'buffet':
        require __DIR__ . '/buffet.php';
        break;
    case 'list':
        require __DIR__ . '/list.php';
        break;
    default:
        respond_error('Not found', 404);
}
