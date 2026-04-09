<?php

/**
 * Movie endpoints
 *
 * GET    /api/v1/movies?q={query}            search (local + TMDB)
 * GET    /api/v1/movies/{id}                 movie detail
 * POST   /api/v1/movies/{id}/bookmark        add bookmark tag
 * DELETE /api/v1/movies/{id}/bookmark        remove bookmark tag
 * POST   /api/v1/movies/{id}/rate            { rating: 1-10 | "null" }
 * GET    /api/v1/movies/{id}/streams         streaming availability
 * GET    /api/v1/movies/{id}/posts           quacks for this movie
 */

// $segments = ['movies', '{id}', '{action}']
$id     = $segments[1] ?? null;
$action = $segments[2] ?? null;

// ── GET /api/v1/movies?q= ─────────────────────────────────────────────────────
if ($id === null && method() === 'GET') {
    $username = optional_auth();
    $q = trim($_GET['q'] ?? '');
    if ($q === '') {
        respond_error('q parameter is required');
    }

    $internal = getMovies($q) ?: [];
    $external = getExternalMovies($q) ?: [];

    // Normalise external results to the same shape as internal
    $externalNorm = array_map(function ($m) {
        $date  = $m['release_date'] ?? '';
        $year  = $date ? explode('-', $date)[0] : null;
        return [
            'id'             => 'm' . $m['id'],
            'tmdbid'         => $m['id'],
            'title'          => $m['title'],
            'originaltitle'  => $m['original_title'],
            'year'           => $year,
            'poster'         => $m['poster_path'] ?? null,
            'source'         => 'external',
        ];
    }, $external);

    $internalNorm = array_map(function ($m) {
        return array_merge($m, ['source' => 'internal']);
    }, $internal);

    respond(['results' => array_merge($internalNorm, $externalNorm)]);
}

// ── Routes that need a movie id ───────────────────────────────────────────────
if ($id === null) {
    respond_error('Not found', 404);
}

switch ($action) {

    // ── GET /api/v1/movies/{id} ───────────────────────────────────────────────
    case null:
        if (method() !== 'GET') {
            respond_error('Method not allowed', 405);
        }
        $username = optional_auth();
        $eid      = db_escape($id);
        $movie    = db_select("SELECT * FROM `movie` WHERE id = '$eid' LIMIT 1");
        if (!$movie) {
            respond_error('Movie not found', 404);
        }
        $m      = $movie[0];
        $genres = getGenreNamesForMovie($eid) ?: [];

        $genreNames = array_column($genres, 'name');

        $bookmarked = false;
        $userRating = null;
        if ($username) {
            $tag        = db_select("SELECT 1 FROM `tag` WHERE movie = '$eid' AND user = '$username' AND tag = 'bookmark' LIMIT 1");
            $bookmarked = (bool) $tag;
            $userRating = getUsersMovieRating($eid, $username);
        }

        respond([
            'id'            => $m['id'],
            'title'         => $m['title'],
            'originaltitle' => $m['originaltitle'],
            'year'          => $m['year'],
            'overview'      => $m['overview'],
            'tagline'       => $m['tagline'],
            'runtime'       => $m['runtime'],
            'imdbid'        => $m['imdbid'],
            'tmdbid'        => $m['tmdbid'],
            'poster'        => $m['poster'],
            'backdrop'      => $m['backdrop'],
            'genres'        => $genreNames,
            'rating'        => (float) getMovieRating($eid),
            'user_rating'   => $userRating ? (int) $userRating : null,
            'bookmarked'    => $bookmarked,
        ]);

    // ── POST /api/v1/movies/{id}/bookmark ─────────────────────────────────────
    case 'bookmark':
        $username = require_auth();
        $eid      = db_escape($id);

        if (method() === 'POST') {
            // Ensure movie exists locally; add from TMDB if not
            $exists = db_select("SELECT id FROM `movie` WHERE id = '$eid' LIMIT 1");
            if (!$exists) {
                $tmdbId = ltrim($id, 'm');
                addMovie($tmdbId);
            }
            addTag($id, 'bookmark', $username);
            respond(['bookmarked' => true]);
        }

        if (method() === 'DELETE') {
            removeTag($id, 'bookmark');
            respond(['bookmarked' => false]);
        }

        respond_error('Method not allowed', 405);

    // ── POST /api/v1/movies/{id}/rate ─────────────────────────────────────────
    case 'rate':
        if (method() !== 'POST') {
            respond_error('Method not allowed', 405);
        }
        $username = require_auth();
        $body     = request_body();
        $rating   = $body['rating'] ?? null;

        if ($rating === null) {
            respond_error('rating is required');
        }

        rateMovie($id, $rating);
        respond([
            'rating'      => (float) getMovieRating($id),
            'user_rating' => $rating === 'null' ? null : (int) $rating,
        ]);

    // ── GET /api/v1/movies/{id}/streams ───────────────────────────────────────
    case 'streams':
        if (method() !== 'GET') {
            respond_error('Method not allowed', 405);
        }
        optional_auth();
        $eid    = db_escape($id);
        $rows   = db_select(
            "SELECT s.type, s.provider, p.clear AS provider_name, p.logo
             FROM `stream` AS s
             LEFT JOIN `provider` AS p ON p.id = s.provider
             WHERE s.movieid = '$eid'
             GROUP BY s.provider"
        ) ?: [];

        $grouped = [];
        foreach ($rows as $row) {
            $type = $row['type'];
            $grouped[$type][] = [
                'provider'      => $row['provider'],
                'provider_name' => $row['provider_name'],
                'logo'          => $row['logo'],
            ];
        }

        respond($grouped);

    // ── GET /api/v1/movies/{id}/posts ─────────────────────────────────────────
    case 'posts':
        if (method() !== 'GET') {
            respond_error('Method not allowed', 405);
        }
        optional_auth();
        $posts = getMessages($id) ?: [];
        $clean = array_map(function ($p) {
            return [
                'id'        => $p['id'],
                'message'   => $p['message'],
                'emoji'     => $p['emoji'],
                'user'      => $p['user1'],
                'timestamp' => (int) $p['timestamp'],
                'votes'     => (float) $p['votes'],
                'replies'   => getReplies($p['id']) ?: [],
            ];
        }, $posts);
        respond(['posts' => $clean]);

    default:
        respond_error('Not found', 404);
}
