<?php

/**
 * Shared API utilities: CORS headers, JSON responses, auth middleware.
 *
 * Every api/v1/*.php handler file should include this (via index.php).
 */

require_once __DIR__ . '/jwt.php';

// ── CORS ─────────────────────────────────────────────────────────────────────
// Allow any origin during development; tighten in production.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Response helpers ──────────────────────────────────────────────────────────

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function respond_error($message, $status = 400) {
    respond(['error' => $message], $status);
}

// ── Request helpers ───────────────────────────────────────────────────────────

function request_body() {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (is_array($json)) {
        return $json;
    }
    // Fall back to form data
    return $_REQUEST;
}

function method() {
    return $_SERVER['REQUEST_METHOD'];
}

// ── Auth middleware ───────────────────────────────────────────────────────────

/**
 * Reads the Bearer token from the Authorization header, validates it,
 * and populates $_SESSION so that existing functions.php functions work.
 *
 * Returns the username string on success, or calls respond_error + exit on failure.
 */
function require_auth() {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    // Some PHP setups expose it via a different key
    if (!$header && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $header  = $headers['Authorization'] ?? '';
    }

    if (!$header || stripos($header, 'bearer ') !== 0) {
        respond_error('Missing or malformed Authorization header', 401);
    }

    $token   = trim(substr($header, 7));
    $payload = jwt_decode($token);

    if (!$payload || empty($payload['sub'])) {
        respond_error('Invalid or expired token', 401);
    }

    // Populate session so existing helper functions work without changes
    $_SESSION['user']     = $payload['sub'];
    $_SESSION['loggedin'] = true;

    return $payload['sub'];
}

/**
 * Like require_auth() but returns null instead of halting for optional auth.
 */
function optional_auth() {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!$header && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $header  = $headers['Authorization'] ?? '';
    }
    if (!$header || stripos($header, 'bearer ') !== 0) {
        return null;
    }
    $token   = trim(substr($header, 7));
    $payload = jwt_decode($token);
    if (!$payload || empty($payload['sub'])) {
        return null;
    }
    $_SESSION['user']     = $payload['sub'];
    $_SESSION['loggedin'] = true;
    return $payload['sub'];
}
