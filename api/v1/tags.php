<?php

/**
 * Tag endpoints
 *
 * GET    /api/v1/tags?movie={id}            list tags for a movie
 * POST   /api/v1/tags                       add tag   { movie, tag }
 * DELETE /api/v1/tags?movie={id}&tag={tag}  remove tag
 */

switch (method()) {

    case 'GET':
        optional_auth();
        $movieId = db_escape(trim($_GET['movie'] ?? ''));
        if (!$movieId) {
            respond_error('movie parameter is required');
        }
        $tags = getTags($movieId) ?: [];
        $flat = array_map(fn($t) => [
            'tag'       => $t['tag'],
            'user'      => $t['user'],
            'timestamp' => (int) $t['timestamp'],
        ], $tags);
        respond(['tags' => $flat]);

    case 'POST':
        $me   = require_auth();
        $body = request_body();
        $movieId = trim($body['movie'] ?? '');
        $tag     = trim($body['tag'] ?? '');

        if (!$movieId || !$tag) {
            respond_error('movie and tag are required');
        }

        addTag($movieId, $tag, $me);
        $tags = getTags(db_escape($movieId)) ?: [];
        respond(['tags' => array_column($tags, 'tag')], 201);

    case 'DELETE':
        require_auth();
        $movieId = trim($_GET['movie'] ?? '');
        $tag     = trim($_GET['tag'] ?? '');

        if (!$movieId || !$tag) {
            respond_error('movie and tag are required');
        }

        removeTag($movieId, $tag);
        respond(['message' => 'Tag removed']);

    default:
        respond_error('Method not allowed', 405);
}
