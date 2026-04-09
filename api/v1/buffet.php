<?php

/**
 * Buffet endpoint — top-rated movies from users you follow
 *
 * GET /api/v1/buffet
 */

if (method() !== 'GET') {
    respond_error('Method not allowed', 405);
}

$me      = require_auth();
$movies  = getBuffet() ?: [];

$clean = array_map(fn($m) => [
    'id'       => $m['id'],
    'title'    => $m['title'],
    'year'     => $m['year'],
    'poster'   => $m['poster'],
    'backdrop' => $m['backdrop'],
    'rating'   => round((float) ($m['rate'] ?? 0), 2),
], $movies);

respond(['movies' => $clean]);
