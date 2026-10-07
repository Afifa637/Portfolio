<?php

/**
 * Admin bootstrap.
 *
 * Previously this file duplicated environment loading, session setup, the
 * database connection, and the CSRF helpers from the public site — two copies
 * that had already drifted apart. It now delegates to the single application
 * bootstrap and only adds what is specific to the admin area.
 *
 * $conn stays a global for the manage_*.php screens, which were written
 * against it directly.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

/**
 * The admin area genuinely requires a database — unlike the public site, which
 * falls back to config/profile.php. Fail with a clear instruction rather than a
 * stack of undefined-method errors.
 */
if (!Database::available()) {
    http_response_code(503);
    exit(
        '<!doctype html><meta charset="utf-8"><title>Database unavailable</title>'
        . '<style>body{font:16px/1.6 system-ui,sans-serif;background:#0b0d14;color:#e4e9f5;'
        . 'display:grid;place-items:center;min-height:100vh;margin:0;padding:24px}'
        . 'div{max-width:38rem}code{background:#1a1f2e;padding:2px 6px;border-radius:4px;'
        . 'font-family:ui-monospace,monospace;font-size:.9em}a{color:#ffb454}</style>'
        . '<div><h1>Database unavailable</h1>'
        . '<p>The admin panel needs MySQL. The public site is unaffected and is still '
        . 'serving content from <code>config/profile.php</code>.</p>'
        . '<p>Check <code>.env</code>, make sure the server is running, then create the '
        . 'schema with:</p>'
        . '<p><code>mysql -u root -p portfolio_db &lt; database/schema.sql</code></p>'
        . '<p><a href="../index.php">← Back to the site</a></p></div>'
    );
}

if (!function_exists('require_admin')) {
    /** Guard every admin screen; call at the top, before any output. */
    function require_admin(): void
    {
        if (empty($_SESSION['admin'])) {
            header('Location: login.php');
            exit;
        }

        // Expire an idle session after two hours.
        $idleLimit = 7200;

        if (isset($_SESSION['admin_last_seen']) && (time() - $_SESSION['admin_last_seen']) > $idleLimit) {
            $_SESSION = [];
            session_destroy();
            header('Location: login.php?expired=1');
            exit;
        }

        $_SESSION['admin_last_seen'] = time();
    }
}

if (!function_exists('admin_redirect')) {
    /** Redirect with a one-shot status message. */
    function admin_redirect(string $to, string $message = '', bool $ok = true): never
    {
        if ($message !== '') {
            flash($ok ? 'admin_success' : 'admin_error', $message);
        }

        header('Location: ' . $to, true, 303);
        exit;
    }
}

// Admin pages must never be cached or indexed.
if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, private');
    header('X-Robots-Tag: noindex, nofollow');
}
