<?php

/**
 * Web installer.
 *
 * Most budget and free PHP hosting offers no SSH, so `php database/migrate.php`
 * is not an option there. This runs the same migration and creates the first
 * admin account through the browser instead.
 *
 * It is dangerous by nature — it can write to the schema and create a login —
 * so it refuses to do anything unless SETUP_KEY is set in .env and matches the
 * key supplied in the URL. Delete the SETUP_KEY line when you are finished and
 * this page turns itself off.
 *
 *   https://your-site/setup.php?key=YOUR_SETUP_KEY
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

$configured = (string) env('SETUP_KEY', '');
$supplied   = (string) ($_GET['key'] ?? $_POST['key'] ?? '');

/** Render a minimal page in the site's own styling and stop. */
function setup_page(string $title, string $body, int $status = 200): never
{
    http_response_code($status);

    echo '<!doctype html><html lang="en" data-theme="dark"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<meta name="robots" content="noindex,nofollow">'
        . '<title>' . e($title) . ' — Setup</title>'
        . '<link rel="stylesheet" href="' . e(asset('assets/css/app.css')) . '">'
        . '<style>body{display:grid;place-items:center;min-height:100svh;padding:var(--sp-6)}'
        . '.setup{width:min(42rem,100%);display:grid;gap:var(--sp-5)}'
        . '.setup pre{padding:var(--sp-4);border:1px solid var(--border);border-radius:var(--r-md);'
        . 'background:var(--bg-elev);overflow-x:auto;font-family:var(--font-mono);'
        . 'font-size:var(--fs-sm);line-height:1.7;white-space:pre-wrap}'
        . '.setup ol{display:grid;gap:var(--sp-2);padding-left:1.2rem;color:var(--text-dim);line-height:1.7}'
        . '</style></head><body><main class="setup">' . $body . '</main></body></html>';

    exit;
}

/* ------------------------------------------------------------- guards ---- */

if ($configured === '') {
    setup_page(
        'Disabled',
        '<h1>Setup is disabled</h1>'
        . '<p class="lead">This page does nothing unless a setup key is configured, which is how it '
        . 'stays safe to leave on a live server.</p>'
        . '<p>To enable it, add a long random line to your <code>.env</code> file:</p>'
        . '<pre>SETUP_KEY=' . e(bin2hex(random_bytes(16))) . '</pre>'
        . '<p class="text-dim">Then reload with <code>?key=</code> followed by that value. '
        . 'Delete the line again once you are done.</p>',
        403
    );
}

if (strlen($configured) < 16) {
    setup_page(
        'Weak key',
        '<h1>Setup key is too short</h1>'
        . '<p class="lead">Use at least 16 characters. A short key can be guessed, and this page can '
        . 'alter your database.</p>',
        403
    );
}

// Constant-time comparison, and a rate limit so the key cannot be brute forced.
if (!rate_limit_ok('setup_attempts', 8, 900)) {
    setup_page('Too many attempts', '<h1>Too many attempts</h1><p class="lead">Wait fifteen minutes.</p>', 429);
}

if ($supplied === '' || !hash_equals($configured, $supplied)) {
    setup_page(
        'Setup',
        '<h1>Setup key required</h1>'
        . '<p class="lead">Append your key to the address:</p>'
        . '<pre>' . e(url('setup.php')) . '?key=YOUR_SETUP_KEY</pre>',
        401
    );
}

/* ------------------------------------------------------------- actions --- */

$step   = (string) ($_GET['step'] ?? 'status');
$keyQS  = 'key=' . rawurlencode($supplied);
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_admin') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        setup_page('Expired', '<h1>Session expired</h1><p class="lead">Reload and try again.</p>', 419);
    }

    $name     = trim((string) ($_POST['name'] ?? 'Administrator'));
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $errors = [];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if (strlen($password) < 10) {
        $errors[] = 'Use a password of at least 10 characters.';
    }

    if ($password !== (string) ($_POST['confirm'] ?? '')) {
        $errors[] = 'The passwords do not match.';
    }

    if (!Database::available()) {
        $errors[] = 'The database is unreachable. Check your .env credentials.';
    } elseif (!Database::hasTable('admins')) {
        $errors[] = 'The admins table does not exist. Run the migration first.';
    }

    if ($errors === []) {
        $hash    = password_hash($password, PASSWORD_BCRYPT);
        $columns = array_column(Database::all('SHOW COLUMNS FROM admins'), 'Field');
        $hasName = in_array('name', $columns, true);
        $exists  = Database::first('SELECT id FROM admins WHERE email = ?', [$email]);

        if ($exists) {
            $ok = $hasName
                ? Database::execute('UPDATE admins SET name = ?, password = ? WHERE email = ?', [$name, $hash, $email])
                : Database::execute('UPDATE admins SET password = ? WHERE email = ?', [$hash, $email]);
        } else {
            $ok = $hasName
                ? Database::execute('INSERT INTO admins (name, email, password) VALUES (?, ?, ?)', [$name, $email, $hash])
                : Database::execute('INSERT INTO admins (email, password) VALUES (?, ?)', [$email, $hash]);
        }

        setup_page(
            'Done',
            '<h1>' . ($exists ? 'Password updated' : 'Admin account created') . '</h1>'
            . '<p class="lead">You can sign in at <a href="' . e(url('admin/login.php')) . '">/admin</a> '
            . 'as <strong>' . e($email) . '</strong>.</p>'
            . '<div class="alert alert-err"><span><strong>Now remove the <code>SETUP_KEY</code> line from '
            . 'your <code>.env</code> file.</strong> While it is there, anyone holding the key can reset '
            . 'this password.</span></div>'
            . '<p><a class="btn btn-primary" href="' . e(url()) . '">View your site</a></p>'
        );
    }

    $output = '<div class="alert alert-err"><span>' . e(implode(' ', $errors)) . '</span></div>';
}

if ($step === 'migrate') {
    if (!Database::available()) {
        setup_page(
            'No database',
            '<h1>Database unreachable</h1>'
            . '<p class="lead">Check <code>DB_HOST</code>, <code>DB_USERNAME</code>, '
            . '<code>DB_PASSWORD</code> and <code>DB_DATABASE</code> in <code>.env</code>. '
            . 'On shared hosting the host is rarely <code>localhost</code>.</p>'
            . '<p class="text-dim">Reported: ' . e((string) (Database::lastError() ?? 'unknown')) . '</p>'
            . '<p><a class="btn btn-ghost" href="?' . e($keyQS) . '">Back</a></p>',
            503
        );
    }

    // Reuse the CLI migration verbatim rather than keeping a second copy that
    // can drift out of step with it.
    define('SETUP_RUNNER', true);
    ob_start();

    try {
        require __DIR__ . '/database/migrate.php';
    } catch (Throwable $e) {
        echo "\nFAILED: " . $e->getMessage() . "\n";
    }

    $log = (string) ob_get_clean();

    setup_page(
        'Migration complete',
        '<h1>Database ready</h1>'
        . '<pre>' . e(trim($log)) . '</pre>'
        . '<p><a class="btn btn-primary" href="?' . e($keyQS) . '&step=admin">Create your admin account →</a></p>'
    );
}

/* ---------------------------------------------------------------- views -- */

$dbUp     = Database::available();
$hasTables = $dbUp && Database::hasTable('projects');
$adminCount = ($dbUp && Database::hasTable('admins'))
    ? (int) (Database::first('SELECT COUNT(*) AS n FROM admins')['n'] ?? 0)
    : 0;

if ($step === 'admin') {
    setup_page(
        'Create admin',
        $output
        . '<h1>Create your admin account</h1>'
        . '<p class="lead">This is the login for <code>/admin</code>, where you manage every part of the site.</p>'
        . '<form method="post" class="form" action="?' . e($keyQS) . '&step=admin">'
        . csrf_field()
        . '<input type="hidden" name="action" value="create_admin">'
        . '<input type="hidden" name="key" value="' . e($supplied) . '">'
        . '<div class="field"><label for="n">Name</label>'
        . '<input id="n" type="text" name="name" value="' . e((string) Content::get('identity.name', '')) . '"></div>'
        . '<div class="field"><label for="e">Email</label>'
        . '<input id="e" type="email" name="email" required autocomplete="username" '
        . 'value="' . e((string) Content::get('identity.email', '')) . '"></div>'
        . '<div class="field"><label for="p">Password</label>'
        . '<input id="p" type="password" name="password" required minlength="10" autocomplete="new-password">'
        . '<span class="field-error">At least 10 characters.</span></div>'
        . '<div class="field"><label for="c">Confirm password</label>'
        . '<input id="c" type="password" name="confirm" required autocomplete="new-password"></div>'
        . '<div><button class="btn btn-primary" type="submit">Create account</button></div>'
        . '</form>'
    );
}

setup_page(
    'Setup',
    '<h1>Portfolio setup</h1>'
    . '<p class="lead">Two steps, then delete your setup key.</p>'
    . '<dl class="fact-list">'
    . '<div class="fact"><dt>Database connection</dt><dd>' . ($dbUp ? 'Connected' : 'Unreachable') . '</dd></div>'
    . '<div class="fact"><dt>Tables</dt><dd>' . ($hasTables ? 'Present' : 'Not created yet') . '</dd></div>'
    . '<div class="fact"><dt>Admin accounts</dt><dd>' . $adminCount . '</dd></div>'
    . '<div class="fact"><dt>Writable cache</dt><dd>'
        . (is_writable(APP_ROOT . '/storage/cache') ? 'Yes' : 'No — run chmod -R 775 storage') . '</dd></div>'
    . '</dl>'
    . '<ol>'
    . '<li>Run the migration — creates every table and imports your content.</li>'
    . '<li>Create your admin account.</li>'
    . '<li>Delete the <code>SETUP_KEY</code> line from <code>.env</code>.</li>'
    . '</ol>'
    . '<div style="display:flex;gap:var(--sp-3);flex-wrap:wrap">'
    . '<a class="btn btn-primary" href="?' . e($keyQS) . '&step=migrate">Run the migration</a>'
    . '<a class="btn btn-ghost" href="?' . e($keyQS) . '&step=admin">Create admin account</a>'
    . '</div>'
    . (!$dbUp
        ? '<div class="alert alert-err"><span>No database connection. The public site still works from '
          . '<code>config/profile.php</code>, but <code>/admin</code> needs one.</span></div>'
        : '')
);
