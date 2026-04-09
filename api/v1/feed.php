<?php

/**
 * Feed endpoint
 *
 * GET /api/v1/feed    activity feed for the authenticated user
 */

if (method() !== 'GET') {
    respond_error('Method not allowed', 405);
}

$me   = require_auth();
$feed = getFeed($me) ?: [];

$clean = array_map(function ($item) {
    return [
        'feedtype'   => $item['feedtype'] ?? null,
        'user'       => $item['user1'] ?? null,
        'movie'      => [
            'id'     => $item['movieid'] ?? null,
            'title'  => $item['movietitle'] ?? null,
            'year'   => $item['movieyear'] ?? null,
            'poster' => $item['poster'] ?? null,
        ],
        'tag'        => $item['tag'] ?? null,
        'rating'     => isset($item['rating']) ? (int) $item['rating'] : null,
        'message'    => $item['message'] ?? null,
        'emoji'      => $item['emoji'] ?? null,
        'upvote'     => isset($item['upvote']) ? (bool) $item['upvote'] : null,
        'downvote'   => isset($item['downvote']) ? (bool) $item['downvote'] : null,
        'timestamp'  => (int) ($item['timestamp'] ?? 0),
    ];
}, $feed);

respond(['feed' => $clean]);
