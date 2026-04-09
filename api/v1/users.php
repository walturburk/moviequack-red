<?php

/**
 * User endpoints
 *
 * GET  /api/v1/users?q={query}             search users
 * GET  /api/v1/users/{username}            user profile
 * GET  /api/v1/users/{username}/following  who this user follows
 * POST /api/v1/users/{username}/follow     toggle follow/unfollow
 */

$username = $segments[1] ?? null;
$action   = $segments[2] ?? null;

// ── GET /api/v1/users?q= ──────────────────────────────────────────────────────
if ($username === null && method() === 'GET') {
    $q = trim($_GET['q'] ?? '');
    if ($q === '') {
        respond_error('q parameter is required');
    }
    $results = getUsers($q) ?: [];
    respond(['users' => array_column($results, 'username')]);
}

if ($username === null) {
    respond_error('Not found', 404);
}

$usernameEsc = db_escape($username);

switch ($action) {

    // ── GET /api/v1/users/{username} ──────────────────────────────────────────
    case null:
        if (method() !== 'GET') {
            respond_error('Method not allowed', 405);
        }
        $me   = optional_auth();
        $user = db_select("SELECT username FROM `user` WHERE username = '$usernameEsc' LIMIT 1");
        if (!$user) {
            respond_error('User not found', 404);
        }

        $following    = getFollowing($username) ?: [];
        $bookmarkCount = db_select(
            "SELECT COUNT(*) AS c FROM `tag` WHERE user = '$usernameEsc' AND tag = 'bookmark'"
        );

        $isFollowing = false;
        if ($me) {
            $isFollowing = (bool) checkiffollows($me, $username);
        }

        respond([
            'username'       => $username,
            'bookmark_count' => (int) ($bookmarkCount[0]['c'] ?? 0),
            'following_count' => count($following),
            'is_following'   => $isFollowing,
        ]);

    // ── GET /api/v1/users/{username}/following ────────────────────────────────
    case 'following':
        if (method() !== 'GET') {
            respond_error('Method not allowed', 405);
        }
        optional_auth();
        $following = getFollowing($username) ?: [];
        respond(['following' => $following]);

    // ── POST /api/v1/users/{username}/follow ──────────────────────────────────
    case 'follow':
        if (method() !== 'POST') {
            respond_error('Method not allowed', 405);
        }
        $me  = require_auth();
        $ret = follow($username, $me);  // toggle — returns true if now following, false if unfollowed
        respond(['following' => (bool) $ret]);

    default:
        respond_error('Not found', 404);
}
