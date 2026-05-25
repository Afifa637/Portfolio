<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        $v = getenv($key);
        return ($v !== false && $v !== null) ? $v : $default;
    }
}

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$appEnv = env('APP_ENV', 'production');
if ($appEnv === 'production') {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

$conn = new mysqli(
    env('DB_HOST', '127.0.0.1'),
    env('DB_USERNAME', 'root'),
    env('DB_PASSWORD', ''),
    env('DB_DATABASE', 'portfolio_db'),
    (int) env('DB_PORT', 3306)
);

if ($conn->connect_error) {
    error_log('DB Connection failed: ' . $conn->connect_error);
    die('Database connection error.');
}

$conn->set_charset('utf8mb4');

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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
    function rate_limit_ok(string $key, int $limit = 4, int $windowSeconds = 120): bool
    {
        $now = time();
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'start' => $now];
        }
        $bucket = &$_SESSION[$key];
        if (($now - $bucket['start']) > $windowSeconds) {
            $bucket = ['count' => 0, 'start' => $now];
        }
        $bucket['count']++;
        return $bucket['count'] <= $limit;
    }
}
