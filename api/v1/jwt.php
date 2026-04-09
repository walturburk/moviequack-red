<?php

// Simple HS256 JWT implementation — no external dependencies.
// Change JWT_SECRET in your config or environment before deploying to production.

define('JWT_SECRET', getenv('JWT_SECRET') ?: 'mq-change-this-secret-in-production');
define('JWT_TTL', 60 * 60 * 24 * 30); // 30 days

function base64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode(string $data): string {
    return base64_decode(strtr($data, '-_', '+/'));
}

function jwt_encode(array $payload): string {
    $header    = base64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload   = base64url_encode(json_encode($payload));
    $signature = base64url_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    return "$header.$payload.$signature";
}

/**
 * Returns the decoded payload array on success, or false on failure.
 * Checks expiry automatically.
 */
function jwt_decode(string $token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false;
    }
    [$header, $payload, $signature] = $parts;
    $expectedSig = base64url_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    if (!hash_equals($expectedSig, $signature)) {
        return false;
    }
    $data = json_decode(base64url_decode($payload), true);
    if (!$data || (isset($data['exp']) && $data['exp'] < time())) {
        return false;
    }
    return $data;
}

function jwt_for_user(string $username): string {
    return jwt_encode([
        'sub' => $username,
        'iat' => time(),
        'exp' => time() + JWT_TTL,
    ]);
}
