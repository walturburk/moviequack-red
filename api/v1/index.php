<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Debug log — remove when fixed
$_log = fopen('/tmp/api_debug.log', 'a');
fwrite($_log, "\n--- " . date('H:i:s') . " ---\n");
fwrite($_log, "URI: " . $_SERVER['REQUEST_URI'] . "\n");
fwrite($_log, "METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n");
fwrite($_log, "PHP: " . phpversion() . "\n");

register_shutdown_function(function() {
    global $_log;
    $err = error_get_last();
    if ($err) {
        fwrite($_log, "FATAL: " . $err['message'] . " in " . $err['file'] . " line " . $err['line'] . "\n");
    }
    fclose($_log);
});

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

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'PHP error', 'errno' => $errno, 'message' => $errstr, 'file' => $errfile, 'line' => $errline));
    exit(1);
});

set_exception_handler(function($e) {
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'Uncaught exception', 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()));
    exit(1);
});

// Bootstrap: session + db + app functions
fwrite($_log, "Loading db_functions.php\n");
require_once __DIR__ . '/../../db_functions.php';
fwrite($_log, "Loading functions.php\n");
require_once __DIR__ . '/../../functions.php';
fwrite($_log, "Loading base.php\n");
// API utilities (CORS headers sent here, preflight handled here)
require_once __DIR__ . '/base.php';
fwrite($_log, "Bootstrap done, resource: ");

// Parse path segments after /api/v1/
$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base     = '/api/v1/';
$relative = '';
if (strpos($uri, $base) === 0) {
    $relative = substr($uri, strlen($base));
}
$relative = trim($relative, '/');
$segments = $relative !== '' ? explode('/', $relative) : [];

$resource = isset($segments[0]) ? $segments[0] : '';
fwrite($_log, $resource . "\n");

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
