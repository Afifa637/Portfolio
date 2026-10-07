<?php

/**
 * Application bootstrap.
 *
 * Loads environment, starts a hardened session, registers helpers, and exposes
 * a lazily-created database connection. Unlike the previous version this file
 * never halts the request when MySQL is unavailable: the public site is driven
 * by config/profile.php and treats the database strictly as an override layer.
 */

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/vendor/autoload.php';

/* ------------------------------------------------------------------ env ---- */

if (is_file(APP_ROOT . '/.env')) {
    Dotenv\Dotenv::createImmutable(APP_ROOT)->safeLoad();
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return match (strtolower((string) $value)) {
            'true', '(true)'   => true,
            'false', '(false)' => false,
            'null', '(null)'   => null,
            default            => $value,
        };
    }
}

/* -------------------------------------------------------------- errors ---- */

define('APP_ENV', (string) env('APP_ENV', 'production'));
define('APP_DEBUG', APP_ENV !== 'production');
define('APP_URL', rtrim((string) env('APP_URL', ''), '/'));

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
}
ini_set('log_errors', '1');

/* ------------------------------------------------------------- session ---- */

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

define('APP_HTTPS', $isHttps);

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('portfolio_session');
    session_start();
}

/* ------------------------------------------------------- security headers -- */

if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), interest-cohort=()');
    header_remove('X-Powered-By');

    if ($isHttps) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

/* ------------------------------------------------------------- helpers ---- */

require_once APP_ROOT . '/src/helpers.php';
require_once APP_ROOT . '/src/Database.php';
require_once APP_ROOT . '/src/Content.php';
require_once APP_ROOT . '/src/GitHub.php';
require_once APP_ROOT . '/src/Knowledge.php';
require_once APP_ROOT . '/src/Mailer.php';
require_once APP_ROOT . '/src/ContactHandler.php';

/* ------------------------------------------------------------ database ---- */

/**
 * Legacy compatibility: admin/ and any older section files expect a global
 * $conn mysqli handle. It is null when the database is unreachable, and every
 * consumer on the public site checks before using it.
 */
$conn = Database::connection();
