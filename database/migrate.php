<?php

/**
 * Schema migration and content import.
 *
 *   php database/migrate.php            upgrade schema, import missing content
 *   php database/migrate.php --reimport replace imported content with profile.php
 *
 * Two jobs:
 *
 *   1. Bring the schema up to database/schema.sql without touching existing
 *      rows. Every statement is guarded, so running it twice changes nothing.
 *
 *   2. Seed the new content tables from config/profile.php, so the admin panel
 *      opens fully populated — every project, skill, service and activity
 *      already there — instead of presenting empty forms to fill in by hand.
 *
 * Content import only ever fills an *empty* table. Your edits are never
 * overwritten unless you explicitly pass --reimport.
 */

declare(strict_types=1);

/*
 * Normally CLI only. setup.php includes this file to run the same migration
 * through the browser, because most budget hosting offers no shell — it defines
 * SETUP_RUNNER only after validating the setup key, which is the only way in.
 */
if (PHP_SAPI !== 'cli' && !defined('SETUP_RUNNER')) {
    http_response_code(403);
    exit("CLI only.\n");
}

require_once __DIR__ . '/../includes/bootstrap.php';

// getopt() returns false outside the CLI.
$options  = PHP_SAPI === 'cli' ? (getopt('', ['reimport', 'force']) ?: []) : [];
$reimport = isset($options['reimport']);

if (!Database::available()) {
    exit("\n  ✗ Cannot reach the database. Check .env, then try again.\n\n");
}

$conn = Database::connection();

echo "\n  Portfolio — migration\n";
echo "  ─────────────────────\n\n";

/* ===================================================== 1. schema =========== */

echo "  Schema\n";

$sql = (string) file_get_contents(__DIR__ . '/schema.sql');

// Strip whole-line `--` comments before splitting. The header comment contains
// example shell commands with their own semicolons, and splitting first turns
// those into garbage statements.
$sql = implode("\n", array_filter(
    explode("\n", $sql),
    static fn(string $line): bool => !preg_match('/^\s*--/', $line)
));

/*
 * Split on semicolons that are NOT inside a quoted string. A naive explode(';')
 * breaks any statement whose COMMENT text contains a semicolon, silently
 * dropping that table on a fresh install.
 */
$statements = [];
$current    = '';
$quote      = null;

for ($i = 0, $len = strlen($sql); $i < $len; $i++) {
    $char = $sql[$i];

    if ($quote !== null) {
        $current .= $char;

        if ($char === '\\' && $i + 1 < $len) {
            $current .= $sql[++$i];
        } elseif ($char === $quote) {
            $quote = null;
        }

        continue;
    }

    if ($char === "'" || $char === '"' || $char === '`') {
        $quote = $char;
        $current .= $char;
        continue;
    }

    if ($char === ';') {
        $statements[] = trim($current);
        $current = '';
        continue;
    }

    $current .= $char;
}

if (trim($current) !== '') {
    $statements[] = trim($current);
}

$statements = array_filter($statements);
$created    = 0;

// mysqli runs in exception mode, so a failed statement throws rather than
// returning false.
foreach ($statements as $statement) {
    if ($statement === '') {
        continue;
    }

    try {
        $conn->query($statement);

        if (stripos($statement, 'CREATE TABLE') !== false) {
            $created++;
        }
    } catch (Throwable $e) {
        if (stripos($statement, 'CREATE TABLE') !== false) {
            printf("    ! %s\n", $e->getMessage());
        }
    }
}

printf("    ✓ %d table definitions applied\n", $created);

/** @return list<string> */
function columnsOf(mysqli $conn, string $table): array
{
    $result = @$conn->query('SHOW COLUMNS FROM `' . $conn->real_escape_string($table) . '`');

    return $result ? array_column($result->fetch_all(MYSQLI_ASSOC), 'Field') : [];
}

// Columns added to tables that already existed before this release.
$additions = [
    'admins'   => [
        'name'       => "VARCHAR(120) NOT NULL DEFAULT 'Administrator'",
        'created_at' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ],
    'home_roles'   => ['order_no' => 'INT NOT NULL DEFAULT 0'],
    'home_socials' => ['order_no' => 'INT NOT NULL DEFAULT 0'],
    'education'    => [
        'description' => 'TEXT DEFAULT NULL',
        'order_no'    => 'INT NOT NULL DEFAULT 0',
    ],
    'skills' => [
        'category' => 'VARCHAR(80) DEFAULT NULL',
        'group_id' => 'INT DEFAULT NULL',
        'order_no' => 'INT NOT NULL DEFAULT 0',
    ],
    'projects' => [
        'slug'         => 'VARCHAR(190) DEFAULT NULL',
        'subtitle'     => 'VARCHAR(255) DEFAULT NULL',
        'summary'      => 'TEXT DEFAULT NULL',
        'problem'      => 'TEXT DEFAULT NULL',
        'challenges'   => 'TEXT DEFAULT NULL',
        'outcome'      => 'TEXT DEFAULT NULL',
        'learned'      => 'TEXT DEFAULT NULL',
        'year'         => 'VARCHAR(16) DEFAULT NULL',
        'repo_name'    => 'VARCHAR(190) DEFAULT NULL',
        'goal'                => 'TEXT DEFAULT NULL',
        'decisions'           => 'TEXT DEFAULT NULL',
        'security_notes'      => 'TEXT DEFAULT NULL',
        'future_improvements' => 'TEXT DEFAULT NULL',
        'architecture'        => 'TEXT DEFAULT NULL',
        'demo_request'        => 'VARCHAR(190) DEFAULT NULL',
        'featured'     => 'TINYINT(1) NOT NULL DEFAULT 0',
        'is_published' => 'TINYINT(1) NOT NULL DEFAULT 1',
        'order_no'     => 'INT NOT NULL DEFAULT 0',
    ],
    'contact_info'     => ['order_no' => 'INT NOT NULL DEFAULT 0'],
    'contact_messages' => ['is_read' => 'TINYINT(1) NOT NULL DEFAULT 0'],
    'footer'           => ['order_no' => 'INT NOT NULL DEFAULT 0'],
];

$added = 0;

foreach ($additions as $table => $columns) {
    if (!Database::hasTable($table)) {
        continue;
    }

    $existing = columnsOf($conn, $table);

    foreach ($columns as $column => $definition) {
        if (in_array($column, $existing, true)) {
            continue;
        }

        if (@$conn->query("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}")) {
            $added++;
            printf("    + %s.%s\n", $table, $column);
        }
    }
}

printf("    ✓ %d column%s added\n", $added, $added === 1 ? '' : 's');

/*
 * Reconcile existing columns with schema.sql.
 *
 * CREATE TABLE IF NOT EXISTS leaves an existing table untouched, so a database
 * created by an earlier version keeps its original column definitions forever.
 * That drift is not cosmetic:
 *
 *   • projects.category was ENUM('web','app','terminal'), so every newer
 *     category was silently stored as an empty string;
 *   • home_socials.icon_class was NOT NULL, so adding a social link without
 *     choosing an icon failed outright;
 *   • skills.percentage was NOT NULL with no default, which works here only
 *     because this server is not in strict mode — on a host that is, creating
 *     a skill would fail.
 *
 * Changes are deliberately one-directional: columns are only ever relaxed,
 * widened or converted toward the declared type. Nothing is narrowed, so no
 * existing value can be truncated by this step.
 */

/** @return array<string, array<string, string>> table => column => definition */
function parseSchemaColumns(string $sql): array
{
    $tables = [];

    if (!preg_match_all('/CREATE TABLE IF NOT EXISTS\s+(\w+)\s*\((.*?)\n\)\s*ENGINE/s', $sql, $matches, PREG_SET_ORDER)) {
        return $tables;
    }

    foreach ($matches as [, $table, $body]) {
        foreach (preg_split('/\r?\n/', $body) ?: [] as $line) {
            $line = trim($line);

            // Skip keys, constraints and the trailing comma on the last column.
            if ($line === '' || preg_match('/^(PRIMARY|UNIQUE|KEY|CONSTRAINT|FOREIGN|INDEX)/i', $line)) {
                continue;
            }

            if (!preg_match('/^(\w+)\s+(.+?),?$/', $line, $parts)) {
                continue;
            }

            $definition = rtrim($parts[2], ',');

            // AUTO_INCREMENT primary keys are never reconciled.
            if (stripos($definition, 'AUTO_INCREMENT') !== false) {
                continue;
            }

            $tables[$table][$parts[1]] = $definition;
        }
    }

    return $tables;
}

$intended = parseSchemaColumns($sql);
$retyped  = 0;

foreach ($intended as $table => $columns) {
    if (!Database::hasTable($table)) {
        continue;
    }

    $live = [];

    foreach (Database::all(
        'SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
        [$table]
    ) as $row) {
        $live[(string) $row['COLUMN_NAME']] = $row;
    }

    foreach ($columns as $column => $definition) {
        if (!isset($live[$column])) {
            continue;
        }

        $currentType = strtolower((string) $live[$column]['COLUMN_TYPE']);
        $isNullable  = $live[$column]['IS_NULLABLE'] === 'YES';
        $wantsNull   = stripos($definition, 'NOT NULL') === false;

        preg_match('/^\s*(\w+)(?:\(([^)]*)\))?/', $definition, $want);
        $wantType = strtolower($want[1] ?? '');
        $wantSize = isset($want[2]) ? (int) $want[2] : 0;

        preg_match('/^(\w+)(?:\(([^)]*)\))?/', $currentType, $have);
        $haveType = strtolower($have[1] ?? '');
        $haveSize = isset($have[2]) ? (int) $have[2] : 0;

        $reason = null;

        if (!$isNullable && $wantsNull) {
            $reason = 'NOT NULL → NULL';
        } elseif ($haveType !== $wantType) {
            // Only convert toward the declared type, never between unrelated
            // families that could lose data.
            $safe = ['enum' => ['varchar'], 'year' => ['varchar'], 'int' => ['varchar'], 'varchar' => ['text', 'varchar']];

            if (in_array($wantType, $safe[$haveType] ?? [], true)) {
                $reason = "{$haveType} → {$wantType}";
            }
        } elseif ($wantType === 'varchar' && $wantSize > $haveSize) {
            $reason = "varchar({$haveSize}) → varchar({$wantSize})";
        }

        if ($reason === null) {
            continue;
        }

        try {
            $conn->query("ALTER TABLE `{$table}` MODIFY `{$column}` {$definition}");
            printf("    ~ %-32s %s\n", "{$table}.{$column}", $reason);
            $retyped++;
        } catch (Throwable $e) {
            printf("    ! %s.%s — %s\n", $table, $column, $e->getMessage());
        }
    }
}

printf("    ✓ %d column definition%s reconciled\n\n", $retyped, $retyped === 1 ? '' : 's');

/* ================================================ 2. legacy repairs ======== */

echo "  Repairs\n";

// Derive repo_name from the GitHub URL legacy rows stored in view_link.
if (Database::hasTable('projects')) {
    $fixed = 0;

    foreach (Database::all('SELECT id, view_link, repo_name FROM projects') as $row) {
        if (!empty($row['repo_name'])) {
            continue;
        }

        $link = (string) ($row['view_link'] ?? '');

        if ($link === '' || !str_contains($link, 'github.com')) {
            continue;
        }

        $segments = explode('/', trim((string) parse_url(trim($link), PHP_URL_PATH), '/'));
        $repo     = $segments[1] ?? '';

        if ($repo !== '' && Database::execute('UPDATE projects SET repo_name = ? WHERE id = ?', [$repo, (int) $row['id']])) {
            $fixed++;
        }
    }

    printf("    ✓ repo_name backfilled (%d)\n", $fixed);

    // A GitHub URL is the source link, not a live demo; the card renders a
    // separate Code button for the repository.
    Database::execute("UPDATE projects SET view_link = '' WHERE view_link LIKE '%github.com%'");

    // Point image paths at the optimised, kebab-case files.
    $remapped = 0;

    foreach (Database::all("SELECT id, image FROM projects WHERE image <> ''") as $row) {
        $base = pathinfo((string) $row['image'], PATHINFO_FILENAME);
        $slug = strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', (string) preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $base)));
        $slug = trim($slug, '-');
        $candidate = 'assets/images/' . $slug . '.jpg';

        if ($row['image'] !== $candidate && is_file(APP_ROOT . '/' . $candidate)) {
            Database::execute('UPDATE projects SET image = ? WHERE id = ?', [$candidate, (int) $row['id']]);
            $remapped++;
        }
    }

    printf("    ✓ image paths remapped to optimised files (%d)\n", $remapped);

    /*
     * Merge duplicate projects.
     *
     * Matching by repository name alone let the same project in twice when the
     * two sources disagreed — a row linked to "Grocery-Shop" and an import
     * keyed on "GreenGrocer" are the same app. The oldest row wins (it holds
     * the hand-written description) and absorbs the case-study fields from the
     * duplicate before it is removed.
     */
    $seen   = [];
    $merged = 0;

    foreach (Database::all('SELECT * FROM projects ORDER BY id ASC') as $row) {
        $key = strtolower((string) preg_replace(
            '/[^a-z0-9]/i',
            '',
            (string) preg_replace('/\b(game|app|application|system|project|website|management)\b/i', '', (string) $row['title'])
        ));

        if ($key === '') {
            continue;
        }

        if (!isset($seen[$key])) {
            $seen[$key] = $row;
            continue;
        }

        $keep = $seen[$key];

        // Copy across any case-study field the surviving row is missing.
        foreach (['slug', 'subtitle', 'summary', 'problem', 'challenges', 'outcome', 'learned', 'year', 'category', 'image', 'repo_name'] as $field) {
            if (empty($keep[$field]) && !empty($row[$field])) {
                Database::execute("UPDATE projects SET `{$field}` = ? WHERE id = ?", [$row[$field], (int) $keep['id']]);
                $keep[$field] = $row[$field];
            }
        }

        if (!empty($row['featured'])) {
            Database::execute('UPDATE projects SET featured = 1 WHERE id = ?', [(int) $keep['id']]);
        }

        // Move the duplicate's feature bullets over if the survivor has none.
        $keepFeatures = (int) (Database::first('SELECT COUNT(*) AS n FROM project_features WHERE project_id = ?', [(int) $keep['id']])['n'] ?? 0);

        if ($keepFeatures === 0) {
            Database::execute('UPDATE project_features SET project_id = ? WHERE project_id = ?', [(int) $keep['id'], (int) $row['id']]);
        }

        Database::execute('DELETE FROM projects WHERE id = ?', [(int) $row['id']]);
        $seen[$key] = $keep;
        $merged++;

        printf("      merged \"%s\" (#%d) into #%d\n", $row['title'], $row['id'], $keep['id']);
    }

    printf("    ✓ duplicate projects merged (%d)\n", $merged);

    /*
     * Re-apply categories from profile.php. Rows written while the column was
     * still an ENUM hold an empty string, and legacy rows use the old
     * web/app/terminal vocabulary.
     */
    $legacy = [
        'web' => 'fullstack', 'app' => 'mobile', 'terminal' => 'systems',
        'console' => 'systems', 'desktop' => 'systems', 'iot' => 'systems',
    ];

    // Valid slugs come from the table the admin manages, not a fixed list.
    $known = array_map(
        'strtolower',
        array_column(Database::all('SELECT slug FROM project_categories'), 'slug')
    );

    if ($known === []) {
        $known = ['backend', 'fullstack', 'mobile', 'systems', 'ai', 'frontend'];
    }

    $fromConfig = [];

    foreach ((require APP_ROOT . '/config/profile.php')['projects'] as $project) {
        if (!empty($project['repo'])) {
            $fromConfig[strtolower($project['repo'])] = $project['category'];
        }
    }

    $recategorised = 0;

    foreach (Database::all('SELECT id, category, repo_name FROM projects') as $row) {
        $current = strtolower(trim((string) $row['category']));

        /*
         * A category already valid is left alone. Preferring config/profile.php
         * here meant every run reset categories the owner had changed in the
         * admin panel — the migration is meant to repair data, not overwrite
         * deliberate edits.
         */
        if (in_array($current, $known, true)) {
            continue;
        }

        $target = $legacy[$current]
            ?? $fromConfig[strtolower((string) $row['repo_name'])]
            ?? ($known[0] ?? 'fullstack');

        if (!in_array(strtolower($target), $known, true)) {
            $target = $known[0] ?? 'fullstack';
        }

        Database::execute('UPDATE projects SET category = ? WHERE id = ?', [$target, (int) $row['id']]);
        $recategorised++;
    }

    printf("    ✓ invalid categories repaired (%d)\n", $recategorised);
}

echo "\n";

/* ============================================= 3. content import =========== */

/** @var array<string, mixed> $profile */
$profile = require APP_ROOT . '/config/profile.php';

echo "  Content import\n";

/** Insert rows only when the table is empty, so edits are never clobbered. */
function importInto(string $table, array $rows, bool $reimport, string $label): void
{
    $count = (int) (Database::first("SELECT COUNT(*) AS n FROM `{$table}`")['n'] ?? 0);

    if ($count > 0 && !$reimport) {
        printf("    · %-20s %d existing row%s, left alone\n", $label, $count, $count === 1 ? '' : 's');
        return;
    }

    if ($count > 0) {
        Database::connection()?->query("DELETE FROM `{$table}`");
    }

    $inserted = 0;

    foreach ($rows as $row) {
        $columns      = array_keys($row);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $columnList   = '`' . implode('`, `', $columns) . '`';

        if (Database::execute("INSERT INTO `{$table}` ({$columnList}) VALUES ({$placeholders})", array_values($row))) {
            $inserted++;
        }
    }

    printf("    ✓ %-20s %d row%s imported\n", $label, $inserted, $inserted === 1 ? '' : 's');
}

$identity = $profile['identity'];
$about    = $profile['about'];
$seo      = $profile['seo'];
$contact  = $profile['contact'];

/* --- settings ---------------------------------------------------------- */

$settings = [
    ['identity', 'name',          $identity['name'],         'Full name',              'text',     'Shown in the hero, header and footer'],
    ['identity', 'first_name',    $identity['first_name'],   'First name',             'text',     'Rendered on the first hero line'],
    ['identity', 'last_name',     $identity['last_name'],    'Last name',              'text',     'Rendered in the accent gradient'],
    ['identity', 'title',         $identity['title'],        'Job title',              'text',     'Used in metadata and structured data'],
    ['identity', 'subtitle',      $identity['subtitle'],     'Subtitle',               'text',     'Small line under your name in the hero panel'],
    ['identity', 'tagline',       $identity['tagline'],      'Tagline',                'text',     'One line, used in the footer'],
    ['identity', 'location',      $identity['location'],     'Location',               'text',     null],
    ['identity', 'email',         $identity['email'],        'Public email',           'email',    'Where contact messages are delivered'],
    ['identity', 'availability',  $identity['availability'], 'Availability badge',     'text',     'The pill at the top of the hero'],
    ['identity', 'available',     '1',                       'Show the live dot',      'bool',     'Animated green dot beside availability'],
    ['identity', 'avatar',        $identity['avatar'],       'Portrait image',         'image',    'Hero panel and about section'],
    ['identity', 'github_user',   $identity['github_user'],  'GitHub username',        'text',     'Drives the live activity section'],
    ['hero',     'pitch',         $identity['pitch'],        'Hero pitch',             'textarea', 'The paragraph under your name'],
    // setting_key is globally unique, so section keys are namespaced — plain
    // 'lead' would collide between the About and Contact sections.
    ['about',    'about_lead',    $about['lead'],            'About — lead paragraph', 'textarea', 'Set larger than the rest'],
    ['about',    'about_body',    implode("\n\n", $about['body']), 'About — body', 'textarea', 'Blank line between paragraphs'],
    ['contact',  'contact_heading', $contact['heading'],     'Contact heading',        'text',     null],
    ['contact',  'contact_lead',  $contact['lead'],          'Contact lead',           'textarea', null],
    ['seo',      'seo_title',     $seo['title'],             'Browser / search title', 'text',     'Around 60 characters'],
    ['seo',      'seo_description', $seo['description'],     'Meta description',       'textarea', 'Around 155 characters'],
    ['seo',      'seo_keywords',  $seo['keywords'],          'Keywords',               'text',     'Comma separated'],
    ['seo',      'seo_image',     $seo['image'],             'Social share image',     'image',    '1200×630 works best'],
    ['status',   'focus',         $profile['status']['focus'],    'Current focus',    'text', 'Shown in the system status panel'],
    ['status',   'current_mode',  $profile['status']['mode'],     'Current mode',     'text', 'Building, Learning, Shipping…'],
    ['status',   'timezone',      $profile['status']['timezone'], 'Time zone',        'text', 'IANA name, e.g. Asia/Dhaka — drives the live clock'],
];

$settingRows = array_map(
    static fn(array $s, int $i): array => [
        'group_key'   => $s[0],
        'setting_key' => $s[1],
        'value'       => (string) $s[2],
        'label'       => $s[3],
        'input_type'  => $s[4],
        'hint'        => $s[5],
        'order_no'    => $i,
    ],
    $settings,
    array_keys($settings)
);

$existingKeys = array_column(Database::all('SELECT setting_key FROM site_settings'), 'setting_key');

if ($existingKeys !== [] && !$reimport) {
    // Existing install: add only the keys that are new in this release.
    $newKeys = 0;

    foreach ($settingRows as $row) {
        if (in_array($row['setting_key'], $existingKeys, true)) {
            continue;
        }

        if (Database::execute(
            'INSERT INTO site_settings (group_key, setting_key, value, label, input_type, hint, order_no)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            array_values($row)
        )) {
            $newKeys++;
        }
    }

    printf("    · %-20s %d existing, %d new key%s added\n", 'site_settings', count($existingKeys), $newKeys, $newKeys === 1 ? '' : 's');
} else importInto('site_settings', array_map(
    static fn(array $s, int $i): array => [
        'group_key'   => $s[0],
        'setting_key' => $s[1],
        'value'       => (string) $s[2],
        'label'       => $s[3],
        'input_type'  => $s[4],
        'hint'        => $s[5],
        'order_no'    => $i,
    ],
    $settings,
    array_keys($settings)
), $reimport, 'site_settings');

/* --- simple lists ------------------------------------------------------ */

importInto('home_roles', array_map(
    static fn(string $r, int $i): array => ['role' => $r, 'order_no' => $i],
    $identity['roles'],
    array_keys($identity['roles'])
), $reimport, 'home_roles');

importInto('home_socials', array_map(
    static fn(array $s, int $i): array => [
        'platform'   => $s['label'],
        'url'        => $s['url'],
        'icon_class' => $s['icon'],
        'order_no'   => $i,
    ],
    $profile['socials'],
    array_keys($profile['socials'])
), $reimport, 'home_socials');

importInto('about_facts', array_map(
    static fn(array $f, int $i): array => ['label' => $f['label'], 'value' => $f['value'], 'order_no' => $i],
    $about['facts'],
    array_keys($about['facts'])
), $reimport, 'about_facts');

importInto('services', array_map(
    static fn(array $s, int $i): array => [
        'title' => $s['title'], 'icon' => $s['icon'], 'body' => $s['body'], 'order_no' => $i,
    ],
    $profile['services'],
    array_keys($profile['services'])
), $reimport, 'services');

importInto('activities', array_map(
    static fn(array $a, int $i): array => [
        'role' => $a['role'], 'org' => $a['org'], 'detail' => $a['detail'], 'icon' => $a['icon'], 'order_no' => $i,
    ],
    $profile['activities'],
    array_keys($profile['activities'])
), $reimport, 'activities');

importInto('contact_channels', array_map(
    static fn(array $c, int $i): array => [
        'label' => $c['label'], 'value' => $c['value'], 'href' => $c['href'], 'icon' => $c['icon'], 'order_no' => $i,
    ],
    $contact['channels'],
    array_keys($contact['channels'])
), $reimport, 'contact_channels');

importInto('contact_purposes', array_map(
    static fn(string $p, int $i): array => ['label' => $p, 'order_no' => $i],
    $contact['purposes'],
    array_keys($contact['purposes'])
), $reimport, 'contact_purposes');

$categories = $profile['project_categories'];
unset($categories['all']);

importInto('project_categories', array_map(
    static fn(string $slug, string $label): array => ['slug' => $slug, 'label' => $label, 'order_no' => 0],
    array_keys($categories),
    array_values($categories)
), $reimport, 'project_categories');

/* --- skills ------------------------------------------------------------ */

importInto('skill_groups', array_map(
    static fn(array $g, int $i): array => [
        'name' => $g['group'], 'icon' => $g['icon'], 'note' => $g['note'], 'order_no' => $i,
    ],
    $profile['skills'],
    array_keys($profile['skills'])
), $reimport, 'skill_groups');

// Skills already existed in the original schema, so only link them to groups
// and import any that are missing rather than replacing the table.
$groupIds = [];

foreach (Database::all('SELECT id, name FROM skill_groups') as $row) {
    $groupIds[strtolower((string) $row['name'])] = (int) $row['id'];
}

$existingSkills = array_map('strtolower', array_column(Database::all('SELECT skill_name FROM skills'), 'skill_name'));
$addedSkills    = 0;
$linkedSkills   = 0;

foreach ($profile['skills'] as $gi => $group) {
    $groupId = $groupIds[strtolower($group['group'])] ?? null;

    foreach ($group['items'] as $si => $item) {
        if (in_array(strtolower($item), $existingSkills, true)) {
            if ($groupId && Database::execute(
                'UPDATE skills SET group_id = ?, category = ? WHERE LOWER(skill_name) = ? AND (group_id IS NULL OR group_id = 0)',
                [$groupId, $group['group'], strtolower($item)]
            )) {
                $linkedSkills++;
            }
            continue;
        }

        if (Database::execute(
            'INSERT INTO skills (skill_name, group_id, category, order_no) VALUES (?, ?, ?, ?)',
            [$item, $groupId, $group['group'], $gi * 100 + $si]
        )) {
            $addedSkills++;
        }
    }
}

printf("    ✓ %-20s %d added, %d linked to groups\n", 'skills', $addedSkills, $linkedSkills);

/* --- principles, blueprint, journey ------------------------------------- */

importInto('principles', array_map(
    static fn(array $p, int $i): array => [
        'title'    => $p['title'],
        'body'     => $p['body'],
        'evidence' => implode(', ', $p['evidence']),
        'order_no' => $i,
    ],
    $profile['principles'],
    array_keys($profile['principles'])
), $reimport, 'principles');

importInto('blueprint_stages', array_map(
    static fn(array $b, int $i): array => [
        'stage'    => $b['stage'],
        'body'     => $b['body'],
        'tech'     => implode(', ', $b['tech']),
        'order_no' => $i,
    ],
    $profile['blueprint'],
    array_keys($profile['blueprint'])
), $reimport, 'blueprint_stages');

importInto('journey', array_map(
    static fn(array $j, int $i): array => [
        'period'   => $j['period'],
        'title'    => $j['title'],
        'body'     => $j['body'],
        'tech'     => implode(', ', $j['tech']),
        'projects' => implode(', ', $j['projects']),
        'order_no' => $i,
    ],
    $profile['journey'],
    array_keys($profile['journey'])
), $reimport, 'journey');

/* --- education --------------------------------------------------------- */

importInto('education', array_map(
    static fn(array $e, int $i): array => [
        'degree'      => $e['degree'],
        'major'       => $e['grade_note'],
        'institution' => $e['institution'],
        'location'    => $e['location'],
        'grade'       => $e['grade'],
        'start_year'  => $e['start'],
        'end_year'    => $e['end'],
        'description' => $e['detail'],
        'order_no'    => $i,
    ],
    $profile['education'],
    array_keys($profile['education'])
), $reimport, 'education');

/* --- projects ---------------------------------------------------------- */

$projectCount = (int) (Database::first('SELECT COUNT(*) AS n FROM projects')['n'] ?? 0);

if ($projectCount === 0 || $reimport) {
    if ($reimport && $projectCount > 0) {
        $conn->query('DELETE FROM projects');
    }

    $imported = 0;

    foreach ($profile['projects'] as $i => $project) {
        $ok = Database::execute(
            'INSERT INTO projects
                (title, slug, subtitle, category, image, summary, problem, challenges, outcome,
                 learned, skills_used, role, year, view_link, repo_name, featured, is_published, order_no)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)',
            [
                $project['title'],
                $project['slug'],
                $project['subtitle'] ?? '',
                $project['category'],
                $project['image'] ?? '',
                $project['summary'] ?? '',
                $project['problem'] ?? '',
                $project['challenges'] ?? '',
                $project['outcome'] ?? '',
                $project['learned'] ?? '',
                implode(', ', $project['stack'] ?? []),
                $project['role'] ?? '',
                $project['year'] ?? '',
                $project['demo'] ?? '',
                $project['repo'] ?? '',
                !empty($project['featured']) ? 1 : 0,
                $i,
            ]
        );

        if (!$ok) {
            continue;
        }

        $imported++;
        $projectId = $conn->insert_id;

        foreach ($project['features'] ?? [] as $fi => $feature) {
            Database::execute(
                'INSERT INTO project_features (project_id, feature, order_no) VALUES (?, ?, ?)',
                [$projectId, $feature, $fi]
            );
        }
    }

    printf("    ✓ %-20s %d imported with case-study detail\n", 'projects', $imported);
} else {
    /*
     * Projects already exist. Rather than skip them, fill in the case-study
     * fields that the old schema had nowhere to store — so existing rows gain
     * problem/outcome/learned text instead of showing empty sections.
     */
    $enriched = 0;
    $byRepo   = [];

    foreach ($profile['projects'] as $project) {
        if (!empty($project['repo'])) {
            $byRepo[strtolower($project['repo'])] = $project;
        }
    }

    foreach (Database::all('SELECT id, title, repo_name, summary, problem FROM projects') as $row) {
        $match = $byRepo[strtolower((string) $row['repo_name'])] ?? null;

        if (!$match) {
            continue;
        }

        // Only fill blanks.
        if (!empty($row['problem'])) {
            continue;
        }

        Database::execute(
            'UPDATE projects SET slug = ?, subtitle = ?, summary = COALESCE(NULLIF(summary, ""), ?),
                 problem = ?, challenges = ?, outcome = ?, learned = ?, year = ?, category = ?, featured = ?
             WHERE id = ?',
            [
                $match['slug'],
                $match['subtitle'] ?? '',
                $match['summary'] ?? '',
                $match['problem'] ?? '',
                $match['challenges'] ?? '',
                $match['outcome'] ?? '',
                $match['learned'] ?? '',
                $match['year'] ?? '',
                $match['category'],
                !empty($match['featured']) ? 1 : 0,
                (int) $row['id'],
            ]
        );

        $hasFeatures = (int) (Database::first(
            'SELECT COUNT(*) AS n FROM project_features WHERE project_id = ?',
            [(int) $row['id']]
        )['n'] ?? 0);

        if ($hasFeatures === 0) {
            foreach ($match['features'] ?? [] as $fi => $feature) {
                Database::execute(
                    'INSERT INTO project_features (project_id, feature, order_no) VALUES (?, ?, ?)',
                    [(int) $row['id'], $feature, $fi]
                );
            }
        }

        $enriched++;
    }

    printf("    · %-20s %d existing, %d enriched with case-study fields\n", 'projects', $projectCount, $enriched);

    /*
     * Projects in profile.php with no matching row are genuinely new.
     *
     * Matched on repository AND normalised title: the same project can be
     * linked to a renamed repository on one side ("Grocery-Shop") and its
     * current name on the other ("GreenGrocer"), and a repo-only check would
     * re-insert it on every run.
     */
    $normalise = static fn(string $t): string => strtolower((string) preg_replace(
        '/[^a-z0-9]/i',
        '',
        (string) preg_replace('/\b(game|app|application|system|project|website|management)\b/i', '', $t)
    ));

    $existingRows  = Database::all('SELECT title, repo_name FROM projects');
    $existingRepos = array_map('strtolower', array_filter(array_column($existingRows, 'repo_name')));
    $existingNames = array_map($normalise, array_column($existingRows, 'title'));
    $new = 0;

    foreach ($profile['projects'] as $i => $project) {
        if ($project['repo'] === ''
            || in_array(strtolower($project['repo']), $existingRepos, true)
            || in_array($normalise($project['title']), $existingNames, true)) {
            continue;
        }

        $ok = Database::execute(
            'INSERT INTO projects
                (title, slug, subtitle, category, image, summary, problem, challenges, outcome,
                 learned, skills_used, role, year, view_link, repo_name, featured, is_published, order_no)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)',
            [
                $project['title'], $project['slug'], $project['subtitle'] ?? '', $project['category'],
                $project['image'] ?? '', $project['summary'] ?? '', $project['problem'] ?? '',
                $project['challenges'] ?? '', $project['outcome'] ?? '', $project['learned'] ?? '',
                implode(', ', $project['stack'] ?? []), $project['role'] ?? '', $project['year'] ?? '',
                $project['demo'] ?? '', $project['repo'], !empty($project['featured']) ? 1 : 0, $i,
            ]
        );

        if ($ok) {
            $projectId = $conn->insert_id;
            foreach ($project['features'] ?? [] as $fi => $feature) {
                Database::execute(
                    'INSERT INTO project_features (project_id, feature, order_no) VALUES (?, ?, ?)',
                    [$projectId, $feature, $fi]
                );
            }
            $new++;
        }
    }

    if ($new > 0) {
        printf("    ✓ %-20s %d new project%s added from profile.php\n", '', $new, $new === 1 ? '' : 's');
    }
}

/*
 * Fill the case-study fields added in this release on projects that already
 * exist. Only blank fields are written, so anything edited in the admin is
 * left exactly as it is.
 */
$byRepo = [];
$bySlug = [];

foreach ($profile['projects'] as $project) {
    if (!empty($project['repo'])) {
        $byRepo[strtolower($project['repo'])] = $project;
    }
    $bySlug[strtolower($project['slug'])] = $project;
}

$filled = 0;

foreach (Database::all('SELECT id, slug, repo_name, goal, decisions, security_notes, future_improvements, architecture, demo_request FROM projects') as $row) {
    $match = $byRepo[strtolower((string) $row['repo_name'])] ?? $bySlug[strtolower((string) $row['slug'])] ?? null;

    if (!$match) {
        continue;
    }

    $fields = [
        'goal'                => (string) ($match['goal'] ?? ''),
        'decisions'           => (string) ($match['decisions'] ?? ''),
        'security_notes'      => (string) ($match['security'] ?? ''),
        'future_improvements' => (string) ($match['future'] ?? ''),
        'architecture'        => !empty($match['architecture'])
            ? (string) json_encode($match['architecture'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : '',
        'demo_request'        => (string) ($match['demo_request'] ?? ''),
    ];

    foreach ($fields as $column => $value) {
        if ($value === '' || trim((string) ($row[$column] ?? '')) !== '') {
            continue;
        }

        if (Database::execute("UPDATE projects SET `{$column}` = ? WHERE id = ?", [$value, (int) $row['id']])) {
            $filled++;
        }
    }
}

printf("    ✓ %-20s %d new case-study field%s filled\n", 'projects', $filled, $filled === 1 ? '' : 's');

echo "\n  Done. Everything above is now editable at /admin.\n\n";
