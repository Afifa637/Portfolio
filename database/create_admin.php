<?php

/**
 * Create or reset the admin account.
 *
 *   php database/create_admin.php
 *
 * Interactive by design: no password is ever passed as a command-line argument
 * (they end up in shell history and in `ps` output) and none is stored in the
 * repository. The password is hashed with bcrypt before it reaches the database.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script may only be run from the command line.\n");
}

require_once __DIR__ . '/../includes/bootstrap.php';

/** Read a line from the terminal, hiding the characters where the shell allows it. */
function prompt(string $label, bool $hidden = false): string
{
    echo $label;

    if ($hidden && DIRECTORY_SEPARATOR !== '\\' && shell_exec('command -v stty')) {
        shell_exec('stty -echo');
        $value = trim((string) fgets(STDIN));
        shell_exec('stty echo');
        echo PHP_EOL;

        return $value;
    }

    if ($hidden) {
        echo '(input will be visible) ';
    }

    return trim((string) fgets(STDIN));
}

echo "\n  Portfolio — admin account setup\n";
echo "  ───────────────────────────────\n\n";

if (!Database::available()) {
    exit("  ✗ Cannot reach the database.\n    Check your .env settings, then run database/schema.sql first.\n\n");
}

if (!Database::hasTable('admins')) {
    exit("  ✗ The `admins` table does not exist.\n    Run: mysql -u root -p portfolio_db < database/schema.sql\n\n");
}

$name  = prompt('  Name          : ') ?: 'Administrator';
$email = prompt('  Email         : ');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("\n  ✗ That is not a valid email address.\n\n");
}

$password = prompt('  Password      : ', true);
$confirm  = prompt('  Confirm       : ', true);

if ($password !== $confirm) {
    exit("\n  ✗ The passwords do not match.\n\n");
}

if (strlen($password) < 10) {
    exit("\n  ✗ Use at least 10 characters. This account can edit the whole site.\n\n");
}

$hash = password_hash($password, PASSWORD_BCRYPT);

$exists = Database::first('SELECT id FROM admins WHERE email = ?', [$email]);

// The original schema had no `name` column. Adapt rather than fail, so this
// works before database/migrate.php has been run as well as after.
$columns = array_column(Database::all('SHOW COLUMNS FROM admins'), 'Field');
$hasName = in_array('name', $columns, true);

if ($exists) {
    $ok = $hasName
        ? Database::execute('UPDATE admins SET name = ?, password = ? WHERE email = ?', [$name, $hash, $email])
        : Database::execute('UPDATE admins SET password = ? WHERE email = ?', [$hash, $email]);
} else {
    $ok = $hasName
        ? Database::execute('INSERT INTO admins (name, email, password) VALUES (?, ?, ?)', [$name, $email, $hash])
        : Database::execute('INSERT INTO admins (email, password) VALUES (?, ?)', [$email, $hash]);
}

echo $ok
    ? "\n  ✓ " . ($exists ? 'Password updated' : 'Admin account created') . " for {$email}\n    Sign in at /admin/login.php\n\n"
    : "\n  ✗ Could not write to the database: " . (Database::lastError() ?? 'unknown error') . "\n\n";
