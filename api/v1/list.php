<?php

/**
 * List endpoint — movies filtered by user(s) and/or tag
 *
 * GET /api/v1/list?user[]={username}&tag={tag}
 *
 * user[] may be repeated for multiple users.
 * tag defaults to "bookmark".
 */

if (method() !== 'GET') {
    respond_error('Method not allowed', 405);
}

optional_auth();

$users = $_GET['user'] ?? [];
if (!is_array($users)) {
    $users = [$users];
}
$users = array_filter(array_map('trim', $users));

$tag = trim($_GET['tag'] ?? 'bookmark');

if (empty($users) && !$tag) {
    respond_error('At least one user or a tag is required');
}

$items = getFilteredItems($users ?: null, $tag) ?: [];

$clean = array_map(fn($item) => [
    'id'         => $item['item'],
    'title'      => $item['title'],
    'poster'     => $item['poster'],
    'user_count' => (int) $item['num_users'],
], $items);

respond(['movies' => $clean]);
