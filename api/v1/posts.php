<?php

/**
 * Post (quack) endpoints
 *
 * GET    /api/v1/posts/{id}           get a single post + replies
 * POST   /api/v1/posts                create post { movie, message, emoji, replyto? }
 * POST   /api/v1/posts/{id}/vote      vote on post { upvote: bool, downvote: bool }
 * DELETE /api/v1/posts/{id}           delete own post
 */

$postId = $segments[1] ?? null;
$action = $segments[2] ?? null;

// ── POST /api/v1/posts (create) ───────────────────────────────────────────────
if ($postId === null) {
    if (method() !== 'POST') {
        respond_error('Method not allowed', 405);
    }
    $me   = require_auth();
    $body = request_body();

    $movie   = trim($body['movie'] ?? '');
    $message = trim($body['message'] ?? '');
    $emoji   = trim($body['emoji'] ?? '');
    $replyTo = trim($body['replyto'] ?? '');

    if (!$movie || !$message) {
        respond_error('movie and message are required');
    }

    $id = newId('p');
    postMessage($id, $emoji, $movie, $message, $me);
    if ($replyTo) {
        postAsAnswer($id, $replyTo);
    }

    respond(['id' => $id], 201);
}

// ── Routes that need a post id ────────────────────────────────────────────────
$postIdEsc = db_escape($postId);

switch ($action) {

    // ── GET /api/v1/posts/{id} ────────────────────────────────────────────────
    case null:
        if (method() !== 'GET') {
            respond_error('Method not allowed', 405);
        }
        optional_auth();
        $post = getMessage($postId);
        if (!$post) {
            respond_error('Post not found', 404);
        }
        $p       = $post[0];
        $votes   = getVotes($postId);
        $replies = getReplies($postId) ?: [];

        respond([
            'id'        => $p['id'],
            'message'   => $p['message'],
            'emoji'     => $p['emoji'],
            'user'      => $p['user1'],
            'movie'     => [
                'id'    => $p['movieid'],
                'title' => $p['movietitle'],
                'year'  => $p['movieyear'],
            ],
            'timestamp' => (int) $p['timestamp'],
            'votes'     => [
                'upvotes'   => (int) ($votes['upvotes'] ?? 0),
                'downvotes' => (int) ($votes['downvotes'] ?? 0),
                'diff'      => (int) ($votes['diff'] ?? 0),
            ],
            'replies'   => array_map(fn($r) => [
                'user'      => $r['username'],
                'message'   => $r['message'],
                'timestamp' => (int) $r['timestamp'],
            ], $replies),
        ]);

    // ── POST /api/v1/posts/{id}/vote ──────────────────────────────────────────
    case 'vote':
        if (method() !== 'POST') {
            respond_error('Method not allowed', 405);
        }
        require_auth();
        $body     = request_body();
        $upvote   = (int) ($body['upvote'] ?? 0);
        $downvote = (int) ($body['downvote'] ?? 0);

        $diff  = vote($postId, $upvote, $downvote);
        $votes = getVotes($postId);
        respond([
            'upvotes'   => (int) ($votes['upvotes'] ?? 0),
            'downvotes' => (int) ($votes['downvotes'] ?? 0),
            'diff'      => (int) ($votes['diff'] ?? 0),
        ]);

    // ── DELETE /api/v1/posts/{id} ─────────────────────────────────────────────
    case null:
        // Handled above; this branch is for DELETE on /{id}
        break;

    default:
        respond_error('Not found', 404);
}

// DELETE on the base resource (no action segment)
if ($action === null && method() === 'DELETE') {
    require_auth();
    removePost($postId);
    respond(['message' => 'Post deleted']);
}
