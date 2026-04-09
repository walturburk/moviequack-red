<?php

/**
 * Auth endpoints
 *
 * POST /api/v1/auth/login      { username, password }
 * POST /api/v1/auth/register   { username, password, email }
 * POST /api/v1/auth/logout     (no-op for JWT; client discards token)
 * GET  /api/v1/auth/me         → current user info
 */

// $segments is set by index.php: ['auth', 'login'] etc.
$action = $segments[1] ?? '';

switch ($action) {

    // ── POST /api/v1/auth/login ───────────────────────────────────────────────
    case 'login':
        if (method() !== 'POST') {
            respond_error('Method not allowed', 405);
        }
        $body     = request_body();
        $username = db_escape(trim($body['username'] ?? ''));
        $password = trim($body['password'] ?? '');

        if (!$username || !$password) {
            respond_error('username and password are required');
        }

        $rows = db_select("SELECT username, password FROM `user` WHERE username = '$username' LIMIT 1");

        if (!$rows || !hashOk($password, $rows[0]['password'])) {
            respond_error('Invalid username or password', 401);
        }

        $user  = $rows[0]['username'];
        newSession($user);

        respond([
            'token' => jwt_for_user($user),
            'user'  => ['username' => $user],
        ]);

    // ── POST /api/v1/auth/register ────────────────────────────────────────────
    case 'register':
        if (method() !== 'POST') {
            respond_error('Method not allowed', 405);
        }
        $body        = request_body();
        $rawUsername = trim($body['username'] ?? '');
        $password    = trim($body['password'] ?? '');
        $email       = trim($body['email'] ?? '');

        if (strlen($password) < 4) {
            respond_error('Password must be at least 4 characters');
        }
        if (strlen($rawUsername) < 3) {
            respond_error('Username must be at least 3 characters');
        }
        if (strlen($rawUsername) > 22) {
            respond_error('Username may be at most 22 characters');
        }
        if (preg_match('/[^a-zA-Z0-9_]/', $rawUsername)) {
            respond_error('Username may only contain letters, numbers, and underscores');
        }

        $username = db_escape($rawUsername);
        $emailEsc = db_escape($email);

        $existing = db_select("SELECT username, email FROM `user` WHERE username = '$username' OR email = '$emailEsc' LIMIT 1");
        if ($existing) {
            if ($existing[0]['username'] === $username) {
                respond_error('Username is already taken');
            }
            respond_error('Email is already registered');
        }

        $hash = createHash($password);
        $ip   = getUserIp();
        db_query("INSERT INTO `user` (username, password, email, ip) VALUES ('$username', '$hash', '$emailEsc', '$ip')");

        $user = $rawUsername;
        newSession($user);

        respond([
            'token' => jwt_for_user($user),
            'user'  => ['username' => $user],
        ], 201);

    // ── POST /api/v1/auth/logout ──────────────────────────────────────────────
    case 'logout':
        // JWT is stateless; just tell the client to discard the token.
        respond(['message' => 'Logged out']);

    // ── GET /api/v1/auth/me ───────────────────────────────────────────────────
    case 'me':
        $username = require_auth();
        $rows     = db_select("SELECT username, email FROM `user` WHERE username = '$username' LIMIT 1");
        if (!$rows) {
            respond_error('User not found', 404);
        }
        $following = getFollowing($username) ?: [];
        respond([
            'username'       => $rows[0]['username'],
            'email'          => $rows[0]['email'],
            'following_count' => count($following),
        ]);

    default:
        respond_error('Not found', 404);
}
