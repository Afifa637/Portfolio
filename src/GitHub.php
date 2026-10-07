<?php

/**
 * GitHub API client.
 *
 * Fetches the owner's public profile and repositories and folds the result into
 * a shape the views can render directly: language totals, headline statistics,
 * and per-repository metadata keyed by name.
 *
 * Rendering never blocks on the network. A page request is served from the
 * best data already on disk, and a refresh is delegated to api/github.php,
 * which the browser calls after load. An API that is slow, down, or rate
 * limited therefore costs nothing in TTFB and blanks no section:
 *
 *   1. Fresh cache      — served directly (default 6 hours).
 *   2. Stale cache      — served immediately, flagged for async refresh.
 *   3. Bundled snapshot — storage/github-snapshot.json, committed to the repo,
 *                         so a first deploy has real data before any API call.
 *   4. Blocking fetch   — only when nothing at all is on disk.
 *   5. Empty payload    — the section is skipped; curated content still renders.
 *
 * Unauthenticated requests are limited to 60/hour per IP, which a shared host
 * will exhaust. Setting GITHUB_TOKEN in .env raises that to 5,000/hour; the
 * token is only ever used server-side and never reaches the browser.
 */

declare(strict_types=1);

final class GitHub
{
    private const API  = 'https://api.github.com';

    /** Generous, because fetching only ever happens off the render path. */
    private const CONNECT_TIMEOUT = 6;
    private const TIMEOUT = 14;

    /** @var array<string, mixed>|null */
    private static ?array $data = null;

    private static bool $needsRefresh = false;

    /** Repositories excluded from the activity feed: forks and scratch work. */
    private const HIDE = ['Afifa637', 'Portfolio01', 'Digital-Clock', 'Notepad'];

    /** @return array<string, mixed> */
    public static function data(): array
    {
        if (self::$data !== null) {
            return self::$data;
        }

        $user = (string) Content::get('identity.github_user', '');

        if ($user === '') {
            return self::$data = self::empty();
        }

        $ttl = (int) env('GITHUB_CACHE_TTL', 21600);

        // 1. Fresh cache — the common case, and entirely offline.
        $cached = self::readCache(self::cacheFile());

        if ($cached !== null) {
            if ((time() - ($cached['fetched_at'] ?? 0)) < $ttl) {
                return self::$data = $cached;
            }

            // 2. Stale but usable. Serve it now; the browser triggers a refresh.
            self::$needsRefresh = true;
            $cached['stale'] = true;

            return self::$data = $cached;
        }

        // 3. Bundled snapshot, so a fresh deploy is never empty.
        $snapshot = self::readCache(APP_ROOT . '/storage/github-snapshot.json');

        if ($snapshot !== null) {
            self::$needsRefresh = true;
            $snapshot['stale'] = true;

            return self::$data = $snapshot;
        }

        // 4. Nothing on disk at all — accept one blocking fetch.
        $fresh = self::refresh();

        return self::$data = $fresh ?? self::empty();
    }

    /**
     * Fetch from the API and update the cache. Called by api/github.php off the
     * render path, and once at cold start.
     *
     * @return array<string, mixed>|null
     */
    public static function refresh(): ?array
    {
        $user = (string) Content::get('identity.github_user', '');

        if ($user === '') {
            return null;
        }

        $fresh = self::fetch($user);

        if ($fresh === null) {
            return null;
        }

        self::writeCache(self::cacheFile(), $fresh);
        self::$data = $fresh;
        self::$needsRefresh = false;

        return $fresh;
    }

    /** True when the served payload is stale and a background refresh is warranted. */
    public static function needsRefresh(): bool
    {
        self::data();

        return self::$needsRefresh;
    }

    private static function cacheFile(): string
    {
        $user = (string) Content::get('identity.github_user', 'user');

        return APP_ROOT . '/storage/cache/github-'
            . preg_replace('/[^a-z0-9_-]/i', '', $user) . '.json';
    }

    /** Metadata for one repository, or null when GitHub data is unavailable. */
    public static function repo(string $name): ?array
    {
        if ($name === '') {
            return null;
        }

        return self::data()['repos'][$name] ?? null;
    }

    /** @return array<string, mixed>|null */
    private static function fetch(string $user): ?array
    {
        $profile = self::request(self::API . '/users/' . rawurlencode($user));

        if (!is_array($profile) || empty($profile['login'])) {
            return null;
        }

        $repos = self::request(self::API . '/users/' . rawurlencode($user) . '/repos?per_page=100&sort=pushed');

        if (!is_array($repos)) {
            $repos = [];
        }

        return self::shape($profile, $repos);
    }

    /**
     * @param  array<string, mixed>       $profile
     * @param  list<array<string, mixed>> $repos
     * @return array<string, mixed>
     */
    private static function shape(array $profile, array $repos): array
    {
        $byName    = [];
        $languages = [];
        $stars     = 0;
        $forks     = 0;
        $owned     = 0;
        $lastPush  = null;

        foreach ($repos as $repo) {
            $name = (string) ($repo['name'] ?? '');

            if ($name === '') {
                continue;
            }

            $isFork = (bool) ($repo['fork'] ?? false);

            $byName[$name] = [
                'name'        => $name,
                'full_name'   => (string) ($repo['full_name'] ?? ''),
                'url'         => (string) ($repo['html_url'] ?? ''),
                'description' => (string) ($repo['description'] ?? ''),
                'language'    => $repo['language'] ?? null,
                'stars'       => (int) ($repo['stargazers_count'] ?? 0),
                'forks'       => (int) ($repo['forks_count'] ?? 0),
                'topics'      => array_values((array) ($repo['topics'] ?? [])),
                'homepage'    => (string) ($repo['homepage'] ?? ''),
                'pushed_at'   => (string) ($repo['pushed_at'] ?? ''),
                'created_at'  => (string) ($repo['created_at'] ?? ''),
                'is_fork'     => $isFork,
                'archived'    => (bool) ($repo['archived'] ?? false),
            ];

            if ($isFork) {
                continue;
            }

            $owned++;
            $stars += (int) ($repo['stargazers_count'] ?? 0);
            $forks += (int) ($repo['forks_count'] ?? 0);

            if (!empty($repo['language'])) {
                $lang = (string) $repo['language'];
                $languages[$lang] = ($languages[$lang] ?? 0) + 1;
            }

            $pushed = (string) ($repo['pushed_at'] ?? '');

            if ($pushed !== '' && ($lastPush === null || $pushed > $lastPush)) {
                $lastPush = $pushed;
            }
        }

        arsort($languages);

        $totalLang = array_sum($languages) ?: 1;

        $languageStats = [];

        foreach ($languages as $language => $count) {
            $languageStats[] = [
                'name'    => $language,
                'count'   => $count,
                'percent' => round($count / $totalLang * 100, 1),
                'color'   => lang_color($language),
            ];
        }

        // Recent public activity, forks and scratch repos filtered out.
        $recent = array_values(array_filter(
            $byName,
            static fn(array $r): bool => !$r['is_fork']
                && !in_array($r['name'], self::HIDE, true)
                && $r['pushed_at'] !== ''
        ));

        usort($recent, static fn(array $a, array $b): int => strcmp($b['pushed_at'], $a['pushed_at']));

        return [
            'ok'         => true,
            'stale'      => false,
            'fetched_at' => time(),
            'profile'    => [
                'login'       => (string) ($profile['login'] ?? ''),
                'name'        => (string) ($profile['name'] ?? ''),
                'url'         => (string) ($profile['html_url'] ?? ''),
                'avatar'      => (string) ($profile['avatar_url'] ?? ''),
                'bio'         => (string) ($profile['bio'] ?? ''),
                'location'    => (string) ($profile['location'] ?? ''),
                'followers'   => (int) ($profile['followers'] ?? 0),
                'public_repos' => (int) ($profile['public_repos'] ?? 0),
                'created_at'  => (string) ($profile['created_at'] ?? ''),
            ],
            'stats' => [
                'repos'      => $owned,
                'stars'      => $stars,
                'forks'      => $forks,
                'languages'  => count($languages),
                'since'      => substr((string) ($profile['created_at'] ?? ''), 0, 4),
                'last_push'  => $lastPush,
            ],
            'languages' => array_slice($languageStats, 0, 10),
            'recent'    => array_slice($recent, 0, 6),
            'repos'     => $byName,
        ];
    }

    /** @return array<mixed>|null Decoded JSON, or null on any transport/API failure. */
    private static function request(string $url): ?array
    {
        $token   = (string) env('GITHUB_TOKEN', '');
        $headers = [
            'Accept: application/vnd.github+json',
            'X-GitHub-Api-Version: 2022-11-28',
            'User-Agent: portfolio-site',
        ];

        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $body   = null;
        $status = 0;

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
                CURLOPT_TIMEOUT        => self::TIMEOUT,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 3,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);

            $body   = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $error  = curl_error($ch);

            curl_close($ch);

            if ($body === false) {
                error_log('[portfolio] GitHub request failed: ' . $error);
                return null;
            }
        } elseif (filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOL)) {
            $context = stream_context_create(['http' => [
                'method'        => 'GET',
                'header'        => implode("\r\n", $headers),
                'timeout'       => self::TIMEOUT,
                'ignore_errors' => true,
            ]]);

            $body = @file_get_contents($url, false, $context);

            foreach ($http_response_header ?? [] as $header) {
                if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $m)) {
                    $status = (int) $m[1];
                }
            }
        } else {
            error_log('[portfolio] No HTTP transport available for the GitHub API.');
            return null;
        }

        if ($status === 403 || $status === 429) {
            error_log('[portfolio] GitHub rate limit hit. Set GITHUB_TOKEN in .env to raise it to 5000/hour.');
            return null;
        }

        if ($status < 200 || $status >= 300 || !is_string($body)) {
            error_log('[portfolio] GitHub API returned HTTP ' . $status);
            return null;
        }

        $decoded = json_decode($body, true);

        return is_array($decoded) ? $decoded : null;
    }

    /** @return array<string, mixed>|null */
    private static function readCache(string $file): ?array
    {
        if (!is_file($file) || !is_readable($file)) {
            return null;
        }

        $decoded = json_decode((string) @file_get_contents($file), true);

        return (is_array($decoded) && !empty($decoded['ok'])) ? $decoded : null;
    }

    /** @param array<string, mixed> $data */
    private static function writeCache(string $file, array $data): void
    {
        $dir = dirname($file);

        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return;
        }

        // Write-then-rename so a concurrent read never sees a half-written file.
        $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';

        if (@file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX) !== false) {
            @rename($tmp, $file);
        } else {
            @unlink($tmp);
        }
    }

    /** @return array<string, mixed> */
    private static function empty(): array
    {
        return [
            'ok'         => false,
            'stale'      => false,
            'fetched_at' => 0,
            'profile'    => [],
            'stats'      => ['repos' => 0, 'stars' => 0, 'forks' => 0, 'languages' => 0, 'since' => '', 'last_push' => null],
            'languages'  => [],
            'recent'     => [],
            'repos'      => [],
        ];
    }
}
