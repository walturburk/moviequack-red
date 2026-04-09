<?php

/**
 * Dashboard endpoint — recommendations + bookmark summary
 *
 * GET /api/v1/dashboard
 *
 * Returns:
 *   suggestions   top movie recommendations for this user
 *   stream_sites  subscription services with most bookmarked movies
 */

if (method() !== 'GET') {
    respond_error('Method not allowed', 405);
}

$me = require_auth();

// Personalised suggestions (correlation-weighted ratings)
$suggested = getTopSuggestedMovies($me) ?: [];
$suggestions = array_map(fn($m) => [
    'id'            => $m['movieid'],
    'poster'        => $m['poster'],
    'overall'       => round((float) ($m['overall'] ?? 0), 2),
    'total_overall' => round((float) ($m['total_overall'] ?? 0), 2),
], array_slice($suggested, 0, 20));

// Subscription streaming sites sorted by number of bookmarked movies
$streamSites = getStreamSites($me, true) ?: [];
$sites = array_map(fn($s) => [
    'provider'      => $s['id'],
    'provider_name' => $s['clear'],
    'logo'          => $s['logo'] ?? null,
    'count'         => (int) $s['quant'],
], $streamSites);

respond([
    'suggestions'  => $suggestions,
    'stream_sites' => $sites,
]);
