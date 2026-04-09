<?php

/**
 * My Movies endpoint — bookmarks grouped by streaming type
 *
 * GET /api/v1/mymovies?users[]={username}&users[]={username}
 *
 * Returns movies grouped by:
 *   Free | Subscription | Rent | Buy
 * Each group is an array of providers, each with their movie list.
 */

if (method() !== 'GET') {
    respond_error('Method not allowed', 405);
}

$me = require_auth();

// Users to include (defaults to only the authenticated user)
$requestedUsers = $_GET['users'] ?? [];
if (!is_array($requestedUsers)) {
    $requestedUsers = [$requestedUsers];
}

// Only allow following + self
$following = getFollowing($me) ?: [];
$allowed   = array_merge($following, [$me]);

$users = array_values(array_intersect($requestedUsers, $allowed));
if (empty($users)) {
    $users = [$me];
}
$users[] = $me; // always include self
$users   = array_unique($users);

$allMovies = getFilteredItems($users, 'bookmark');
$movieIds  = array_column($allMovies, 'item');

if (empty($movieIds)) {
    respond(['Free' => [], 'Subscription' => [], 'Rent' => [], 'Buy' => []]);
}

$movies = getStreamableMovies($movieIds);

$streamsites = [];
foreach ($movies as $movie) {
    $type     = $movie['type'];
    $provider = $movie['provider'];
    $streamsites[$type][$provider]['clear']    = $movie['clear'];
    $streamsites[$type][$provider]['count']    = ($streamsites[$type][$provider]['count'] ?? 0) + 1;
    $streamsites[$type][$provider]['movies'][] = [
        'id'     => $movie['movieid'],
        'poster' => $movie['poster'],
    ];
}

$ss = [
    'Free'         => array_values($streamsites['free'] ?? []),
    'Subscription' => array_values($streamsites['flatrate'] ?? []),
    'Rent'         => array_values($streamsites['rent'] ?? []),
    'Buy'          => array_values($streamsites['buy'] ?? []),
];

foreach ($ss as $key => $group) {
    usort($ss[$key], fn($a, $b) => $b['count'] <=> $a['count']);
}

respond($ss);
