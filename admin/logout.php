<?php

/**
 * Admin sign-out.
 *
 * Loads config.php first so the session is opened under the application's own
 * name — starting it directly would create and then destroy a different,
 * default-named session and leave the real one signed in.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$_SESSION = [];

// Expire the session cookie itself, not just its contents; otherwise the
// identifier stays in the browser and can be reused.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $params['path'],
        'domain'   => $params['domain'],
        'secure'   => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Lax',
    ]);
}

session_destroy();

header('Location: login.php', true, 303);
exit;
