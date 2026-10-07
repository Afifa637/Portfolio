<?php

/**
 * Background GitHub refresh endpoint.
 *
 * The page renders from disk and, when that copy is stale, the browser calls
 * this once after load. The refresh therefore never delays first paint, and a
 * slow or rate-limited API is invisible to the visitor.
 *
 * Returns the refreshed statistics so the already-rendered section can be
 * updated in place without a reload.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Same-origin only: this endpoint costs an outbound API call, so it should not
// be usable as a free proxy from other sites.
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$host   = $_SERVER['HTTP_HOST'] ?? '';

if ($origin !== '' && parse_url($origin, PHP_URL_HOST) !== $host) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Cross-origin requests are not accepted.']);
    exit;
}

// Cheap throttle so a reload loop cannot hammer the GitHub API.
if (!rate_limit_ok('github_refresh', 4, 600)) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Refreshed too recently.']);
    exit;
}

$data = GitHub::refresh();

if ($data === null) {
    // Not an error worth surfacing: the page already shows cached data.
    http_response_code(200);
    echo json_encode(['ok' => false, 'error' => 'GitHub is unreachable; cached data is still in use.']);
    exit;
}

echo json_encode([
    'ok'         => true,
    'fetched_at' => $data['fetched_at'],
    'stats'      => $data['stats'],
    'languages'  => $data['languages'],
    'recent'     => array_map(static fn(array $r): array => [
        'name'        => $r['name'],
        'url'         => $r['url'],
        'description' => $r['description'],
        'language'    => $r['language'],
        'stars'       => $r['stars'],
        'forks'       => $r['forks'],
        'pushed_at'   => $r['pushed_at'],
    ], $data['recent']),
], JSON_UNESCAPED_SLASHES);
