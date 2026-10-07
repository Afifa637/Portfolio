<?php

/**
 * Router for PHP's built-in development server, which ignores .htaccess.
 *
 *   php -S 127.0.0.1:8899 router.php
 *
 * Mirrors the production rewrites: /projects/<slug>, /sitemap.xml, the custom
 * 404, and the deny rules for application internals. Not used in production.
 */

declare(strict_types=1);

$path = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));

// Internals and dotfiles are never served, exactly as .htaccess enforces.
if (preg_match('#^/(includes|src|config|storage|database|vendor|tools)/#', $path) || preg_match('#/\.#', $path)) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    return true;
}

if (preg_match('#^/projects/([a-z0-9-]+)/?$#', $path, $m)) {
    $_GET['slug'] = $m[1];
    require __DIR__ . '/project.php';
    return true;
}

if ($path === '/sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    require __DIR__ . '/sitemap.php';
    return true;
}

$file = __DIR__ . $path;

if ($path !== '/' && is_file($file)) {
    return false; // let the built-in server serve the real file
}

if ($path === '/' || is_dir($file)) {
    return false;
}

http_response_code(404);
require __DIR__ . '/404.php';
return true;
