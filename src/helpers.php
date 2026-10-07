<?php

/**
 * View and security helpers shared across the whole application.
 */

declare(strict_types=1);

if (!function_exists('e')) {
    /** Escape for HTML text and quoted attribute contexts. */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('asset')) {
    /**
     * Resolve an asset path and append a cache-busting fingerprint based on the
     * file's modification time, so a deploy invalidates the browser cache
     * without needing far-future headers to be hand-managed.
     */
    function asset(string $path): string
    {
        $path = ltrim($path, '/');
        $file = APP_ROOT . '/' . $path;

        // Encode path segments so filenames containing spaces stay valid URLs.
        $encoded = implode('/', array_map('rawurlencode', explode('/', $path)));

        return is_file($file)
            ? $encoded . '?v=' . filemtime($file)
            : $encoded;
    }
}

if (!function_exists('picture')) {
    /**
     * Render a <picture> that prefers the WebP produced by tools/optimize-images.php
     * and falls back to the original file.
     *
     * The WebP copies are ~90% smaller; the fallback only ships to browsers
     * that cannot decode WebP. When no WebP exists the output degrades to a
     * plain <img>, so adding a new screenshot never breaks the page.
     *
     * @param array<string, string|int> $attrs Extra attributes for the <img>.
     */
    function picture(string $path, string $alt, array $attrs = []): string
    {
        $path = ltrim($path, '/');

        if (!is_file(APP_ROOT . '/' . $path)) {
            return '';
        }

        $defaults = ['loading' => 'lazy', 'decoding' => 'async'];
        $attrs   += $defaults;

        $rendered = '';
        foreach ($attrs as $key => $value) {
            $rendered .= ' ' . $key . '="' . e((string) $value) . '"';
        }

        $img  = '<img src="' . e(asset($path)) . '" alt="' . e($alt) . '"' . $rendered . '>';
        $webp = preg_replace('/\.(png|jpe?g)$/i', '.webp', $path) ?? '';

        if ($webp === '' || $webp === $path || !is_file(APP_ROOT . '/' . $webp)) {
            return $img;
        }

        return '<picture><source srcset="' . e(asset($webp)) . '" type="image/webp">' . $img . '</picture>';
    }
}

if (!function_exists('url')) {
    /** Absolute URL for canonical tags, Open Graph, and the sitemap. */
    function url(string $path = ''): string
    {
        $base = APP_URL;

        if ($base === '') {
            $scheme = APP_HTTPS ? 'https' : 'http';
            $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $dir    = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
            $base   = $scheme . '://' . $host . $dir;
        }

        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('icon')) {
    /**
     * Inline SVG icon from a small hand-picked set.
     *
     * Inlining avoids the ~75 KB Font Awesome stylesheet plus its webfont, which
     * was previously render-blocking for the sake of a few dozen glyphs.
     */
    function icon(string $name, int $size = 20, string $class = ''): string
    {
        static $paths = [
            'github'     => '<path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/>',
            'linkedin'   => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/>',
            'mail'       => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
            'map-pin'    => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
            'phone'      => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92Z"/>',
            'download'   => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
            'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
            'arrow-up'   => '<line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/>',
            'arrow-up-right' => '<line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/>',
            'external'   => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>',
            'code'       => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
            'server'     => '<rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/>',
            'database'   => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>',
            'layout'     => '<rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/>',
            'layers'     => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>',
            'smartphone' => '<rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>',
            'settings'   => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/>',
            'terminal'   => '<polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/>',
            'cpu'        => '<rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/>',
            'award'      => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>',
            'star'       => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
            'git-fork'   => '<circle cx="12" cy="18" r="3"/><circle cx="6" cy="6" r="3"/><circle cx="18" cy="6" r="3"/><path d="M18 9v1a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V9"/><path d="M12 12v3"/>',
            'graduation' => '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>',
            'briefcase'  => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
            'calendar'   => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
            'check'      => '<polyline points="20 6 9 17 4 12"/>',
            'copy'       => '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
            'search'     => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
            'menu'       => '<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>',
            'x'          => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
            'sun'        => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>',
            'moon'       => '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>',
            'send'       => '<line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>',
            'activity'   => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
            'book'       => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
            'zap'        => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
            'facebook'   => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
            'skype'      => '<circle cx="12" cy="12" r="9"/><path d="M8.7 14.6c.5 1 1.7 1.6 3.3 1.6 1.8 0 2.9-.8 2.9-1.9 0-1.2-1-1.6-2.9-2-2.2-.5-3.4-1.1-3.4-2.7 0-1.4 1.3-2.4 3.2-2.4 1.6 0 2.7.6 3.2 1.5"/>',
            'twitter'    => '<path d="M22 4.01c-1 .49-1.98.689-3 .99-1.121-1.265-2.783-1.335-4.38-.737S11.977 6.323 12 8v1c-3.245.083-6.135-1.395-8-4 0 0-4.182 7.433 4 11-1.872 1.247-3.739 2.088-6 2 3.308 1.803 6.913 2.423 10.034 1.517 3.58-1.04 6.522-3.723 7.651-7.742a13.84 13.84 0 0 0 .497-3.753c0-.249 1.51-2.772 1.818-4.013z"/>',
            'globe'      => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
        ];

        $body  = $paths[$name] ?? $paths['code'];
        $class = $class !== '' ? ' class="' . e($class) . '"' : '';

        return '<svg' . $class . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24"'
            . ' fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"'
            . ' stroke-linejoin="round" aria-hidden="true" focusable="false">' . $body . '</svg>';
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify(?string $token): bool
    {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('rate_limit_ok')) {
    /** Fixed-window limiter backed by the session. */
    function rate_limit_ok(string $key, int $limit = 5, int $windowSeconds = 300): bool
    {
        $now = time();

        if (!isset($_SESSION[$key]) || ($now - $_SESSION[$key]['start']) > $windowSeconds) {
            $_SESSION[$key] = ['count' => 0, 'start' => $now];
        }

        $_SESSION[$key]['count']++;

        return $_SESSION[$key]['count'] <= $limit;
    }
}

if (!function_exists('flash')) {
    /** Write (with $value) or read-and-clear (without) a one-shot session message. */
    function flash(string $key, ?string $value = null): ?string
    {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }

        $out = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);

        return $out;
    }
}

if (!function_exists('view')) {
    /** Render a view file with an isolated scope. */
    function view(string $name, array $data = []): void
    {
        $file = APP_ROOT . '/views/' . ltrim($name, '/') . '.php';

        if (!is_file($file)) {
            if (APP_DEBUG) {
                throw new RuntimeException("View not found: {$name}");
            }
            return;
        }

        extract($data, EXTR_SKIP);
        require $file;
    }
}

if (!function_exists('str_excerpt')) {
    function str_excerpt(string $text, int $length = 150): string
    {
        $text = trim(strip_tags($text));

        if (mb_strlen($text) <= $length) {
            return $text;
        }

        $cut   = mb_substr($text, 0, $length);
        $space = mb_strrpos($cut, ' ');

        return rtrim($space !== false ? mb_substr($cut, 0, $space) : $cut, ' ,.;:') . '…';
    }
}

if (!function_exists('time_ago')) {
    function time_ago(?string $datetime): string
    {
        if (!$datetime) {
            return '';
        }

        $ts   = strtotime($datetime);
        $diff = time() - $ts;

        if ($diff < 3600)   return 'just now';
        if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';
        if ($diff < 2592000) return floor($diff / 604800) . 'w ago';
        if ($diff < 31536000) return floor($diff / 2592000) . 'mo ago';

        return floor($diff / 31536000) . 'y ago';
    }
}

if (!function_exists('lang_color')) {
    /** GitHub's canonical language colours, for the language bars. */
    function lang_color(?string $language): string
    {
        static $colors = [
            'Java' => '#b07219', 'JavaScript' => '#f1e05a', 'TypeScript' => '#3178c6',
            'PHP' => '#4F5D95', 'Python' => '#3572A5', 'C' => '#555555', 'C++' => '#f34b7d',
            'C#' => '#178600', 'Dart' => '#00B4AB', 'Swift' => '#F05138', 'Kotlin' => '#A97BFF',
            'GDScript' => '#355570', 'Blade' => '#f7523f', 'HTML' => '#e34c26', 'CSS' => '#563d7c',
            'SCSS' => '#c6538c', 'Shell' => '#89e051', 'Go' => '#00ADD8', 'Rust' => '#dea584',
            'Ruby' => '#701516', 'Vue' => '#41b883', 'Jupyter Notebook' => '#DA5B0B',
        ];

        return $colors[$language ?? ''] ?? '#8b949e';
    }
}
