<?php

/**
 * Content resolver.
 *
 * config/profile.php is the baseline; the database is the live source. When a
 * table holds rows, those rows win — that is what makes /admin authoritative.
 * When the database is unreachable or a table is empty, the file content is
 * used instead, so the site never renders a blank section.
 *
 * Access is by dot path: Content::get('identity.name').
 */

declare(strict_types=1);

final class Content
{
    /** @var array<string, mixed>|null */
    private static ?array $data = null;

    /** @return array<string, mixed> */
    public static function all(): array
    {
        if (self::$data !== null) {
            return self::$data;
        }

        /** @var array<string, mixed> $base */
        $base = require APP_ROOT . '/config/profile.php';

        self::$data = Database::available()
            ? self::applyOverrides($base)
            : $base;

        return self::$data;
    }

    /** Read a value by dot path, returning $default when any segment is missing. */
    public static function get(string $path, mixed $default = null): mixed
    {
        $value = self::all();

        foreach (explode('.', $path) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /** Discard the memoised copy so the next read hits the database again. */
    public static function flush(): void
    {
        self::$data = null;
    }

    /**
     * @param  array<string, mixed> $base
     * @return array<string, mixed>
     */
    private static function applyOverrides(array $base): array
    {
        /* ----------------------------------------------- site_settings ---- */

        $settings = [];

        if (Database::hasTable('site_settings')) {
            foreach (Database::all('SELECT setting_key, value FROM site_settings') as $row) {
                $settings[(string) $row['setting_key']] = (string) ($row['value'] ?? '');
            }
        }

        /** Use a stored setting only when it holds something. */
        $set = static function (array &$target, string $key, string $settingKey) use ($settings): void {
            if (isset($settings[$settingKey]) && trim($settings[$settingKey]) !== '') {
                $target[$key] = $settings[$settingKey];
            }
        };

        foreach ([
            'name', 'first_name', 'last_name', 'title', 'subtitle', 'tagline',
            'location', 'email', 'availability', 'avatar', 'github_user',
        ] as $key) {
            $set($base['identity'], $key, $key);
        }

        $set($base['identity'], 'pitch', 'pitch');

        if (isset($settings['available'])) {
            $base['identity']['available'] = (bool) (int) $settings['available'];
        }

        // Legacy single-row table, still written by the original admin screen.
        if (Database::hasTable('home_info')) {
            $row = Database::first('SELECT * FROM home_info LIMIT 1');

            if ($row) {
                foreach ([
                    'name' => 'name', 'subtitle' => 'subtitle', 'description' => 'pitch',
                    'location' => 'location', 'email' => 'email',
                    'availability' => 'availability', 'profile_image' => 'avatar',
                ] as $column => $key) {
                    if (!empty($row[$column]) && !isset($settings[$key])) {
                        $base['identity'][$key] = (string) $row[$column];
                    }
                }
            }
        }

        if (!empty($base['identity']['name'])) {
            $parts = preg_split('/\s+/', trim((string) $base['identity']['name'])) ?: [];

            if (!isset($settings['first_name'])) {
                $base['identity']['first_name'] = array_shift($parts) ?: $base['identity']['first_name'];
                $base['identity']['last_name']  = implode(' ', $parts) ?: $base['identity']['last_name'];
            }
        }

        /* ------------------------------------------------------- roles ---- */

        if (Database::hasTable('home_roles')) {
            $roles = array_values(array_filter(array_map(
                'trim',
                array_column(Database::all('SELECT role FROM home_roles ORDER BY order_no, id'), 'role')
            )));

            if ($roles !== []) {
                $base['identity']['roles'] = $roles;
            }
        }

        /* ----------------------------------------------------- socials ---- */

        if (Database::hasTable('home_socials')) {
            $rows = Database::all('SELECT * FROM home_socials ORDER BY order_no, id');
            $socials = [];

            foreach ($rows as $row) {
                if (empty($row['url'])) {
                    continue;
                }

                $socials[] = [
                    'label' => (string) ($row['platform'] ?? 'Link'),
                    'url'   => (string) $row['url'],
                    'icon'  => self::normaliseIcon((string) ($row['platform'] ?? ''), (string) ($row['icon_class'] ?? '')),
                ];
            }

            if ($socials !== []) {
                $base['socials'] = $socials;
            }
        }

        /* ------------------------------------------------------- about ---- */

        if (isset($settings['about_lead']) && trim($settings['about_lead']) !== '') {
            $base['about']['lead'] = $settings['about_lead'];
        }

        if (isset($settings['about_body']) && trim($settings['about_body']) !== '') {
            $paragraphs = array_values(array_filter(array_map(
                'trim',
                preg_split('/\n{2,}/', $settings['about_body']) ?: []
            )));

            if ($paragraphs !== []) {
                $base['about']['body'] = $paragraphs;
            }
        } elseif (Database::hasTable('about')) {
            $row = Database::first('SELECT * FROM about LIMIT 1');

            if ($row) {
                if (!empty($row['short_intro'])) {
                    $base['about']['lead'] = trim(strip_tags((string) $row['short_intro']));
                }

                if (!empty($row['long_intro'])) {
                    $paragraphs = array_values(array_filter(array_map(
                        static fn($p) => trim(strip_tags($p)),
                        preg_split('/\n{2,}|<\/p>/i', (string) $row['long_intro']) ?: []
                    )));

                    if ($paragraphs !== []) {
                        $base['about']['body'] = $paragraphs;
                    }
                }
            }
        }

        if (Database::hasTable('about_facts')) {
            $facts = array_map(
                static fn(array $r): array => ['label' => (string) $r['label'], 'value' => (string) $r['value']],
                Database::all('SELECT label, value FROM about_facts ORDER BY order_no, id')
            );

            if ($facts !== []) {
                $base['about']['facts'] = $facts;
            }
        }

        /* ------------------------------------------------------ skills ---- */

        if (Database::hasTable('skill_groups') && Database::hasTable('skills')) {
            $groups = Database::all('SELECT * FROM skill_groups ORDER BY order_no, id');
            $skills = [];

            foreach ($groups as $group) {
                $items = array_values(array_filter(array_map(
                    'trim',
                    array_column(
                        Database::all(
                            'SELECT skill_name FROM skills WHERE group_id = ? ORDER BY order_no, id',
                            [(int) $group['id']]
                        ),
                        'skill_name'
                    )
                )));

                if ($items === []) {
                    continue;
                }

                $skills[] = [
                    'group' => (string) $group['name'],
                    'icon'  => (string) ($group['icon'] ?: 'code'),
                    'note'  => (string) ($group['note'] ?? ''),
                    'items' => $items,
                ];
            }

            // Skills with no group would otherwise disappear from the site.
            $orphans = array_values(array_filter(array_map(
                'trim',
                array_column(
                    Database::all('SELECT skill_name FROM skills WHERE group_id IS NULL OR group_id = 0 ORDER BY order_no, id'),
                    'skill_name'
                )
            )));

            if ($orphans !== []) {
                $skills[] = ['group' => 'Also', 'icon' => 'code', 'note' => '', 'items' => $orphans];
            }

            if ($skills !== []) {
                $base['skills'] = $skills;
            }
        }

        /* --------------------------------------------------- education ---- */

        if (Database::hasTable('education')) {
            $rows = Database::all('SELECT * FROM education ORDER BY order_no, start_year DESC, id');

            if ($rows !== []) {
                $base['education'] = array_map(static function (array $row): array {
                    $end = trim((string) ($row['end_year'] ?? ''));

                    return [
                        'degree'      => (string) ($row['degree'] ?? ''),
                        'institution' => (string) ($row['institution'] ?? ''),
                        'location'    => (string) ($row['location'] ?? ''),
                        'start'       => (string) ($row['start_year'] ?? ''),
                        'end'         => $end !== '' ? $end : 'Present',
                        'current'     => $end === '' || strcasecmp($end, 'present') === 0,
                        'grade'       => (string) ($row['grade'] ?? ''),
                        'grade_note'  => (string) ($row['major'] ?? ''),
                        'detail'      => (string) ($row['description'] ?? ''),
                    ];
                }, $rows);
            }
        }

        /* -------------------------------------------------- experience ---- */

        if (Database::hasTable('experience')) {
            $rows = Database::all('SELECT * FROM experience ORDER BY is_current DESC, order_no, id');

            if ($rows !== []) {
                $base['experience'] = array_map(static fn(array $row): array => [
                    'title'    => (string) ($row['title'] ?? ''),
                    'org'      => (string) ($row['company'] ?? ''),
                    'start'    => (string) ($row['start_date'] ?? ''),
                    'end'      => (string) ($row['end_date'] ?? 'Present'),
                    'location' => (string) ($row['location'] ?? ''),
                    'body'     => (string) ($row['description'] ?? ''),
                    'stack'    => self::splitList((string) ($row['tech_stack'] ?? '')),
                    'current'  => !empty($row['is_current']),
                ], $rows);
            }
        }

        /* -------------------------------------------------- activities ---- */

        if (Database::hasTable('activities')) {
            $rows = Database::all('SELECT * FROM activities ORDER BY order_no, id');

            if ($rows !== []) {
                $base['activities'] = array_map(static fn(array $r): array => [
                    'role'   => (string) $r['role'],
                    'org'    => (string) $r['org'],
                    'detail' => (string) ($r['detail'] ?? ''),
                    'icon'   => (string) ($r['icon'] ?: 'award'),
                ], $rows);
            }
        }

        /* ---------------------------------------------------- services ---- */

        if (Database::hasTable('services')) {
            $rows = Database::all('SELECT * FROM services ORDER BY order_no, id');

            if ($rows !== []) {
                $base['services'] = array_map(static fn(array $r): array => [
                    'title' => (string) $r['title'],
                    'icon'  => (string) ($r['icon'] ?: 'server'),
                    'body'  => (string) ($r['body'] ?? ''),
                ], $rows);
            }
        }

        /* ----------------------------------------------------- contact ---- */

        if (isset($settings['contact_heading']) && trim($settings['contact_heading']) !== '') {
            $base['contact']['heading'] = $settings['contact_heading'];
        }

        if (isset($settings['contact_lead']) && trim($settings['contact_lead']) !== '') {
            $base['contact']['lead'] = $settings['contact_lead'];
        }

        if (Database::hasTable('contact_channels')) {
            $rows = Database::all('SELECT * FROM contact_channels ORDER BY order_no, id');

            if ($rows !== []) {
                $base['contact']['channels'] = array_map(static fn(array $r): array => [
                    'label' => (string) $r['label'],
                    'value' => (string) $r['value'],
                    'href'  => (string) ($r['href'] ?? ''),
                    'icon'  => (string) ($r['icon'] ?: 'mail'),
                ], $rows);
            }
        }

        if (Database::hasTable('contact_purposes')) {
            $purposes = array_values(array_filter(array_map(
                'trim',
                array_column(Database::all('SELECT label FROM contact_purposes ORDER BY order_no, id'), 'label')
            )));

            if ($purposes !== []) {
                $base['contact']['purposes'] = $purposes;
            }
        }

        /* --------------------------------------------------------- seo ---- */

        foreach (['seo_title' => 'title', 'seo_description' => 'description', 'seo_keywords' => 'keywords', 'seo_image' => 'image'] as $settingKey => $key) {
            if (isset($settings[$settingKey]) && trim($settings[$settingKey]) !== '') {
                $base['seo'][$key] = $settings[$settingKey];
            }
        }

        /* -------------------------------------------------- categories ---- */

        if (Database::hasTable('project_categories')) {
            $rows = Database::all('SELECT slug, label FROM project_categories ORDER BY order_no, id');

            if ($rows !== []) {
                $categories = ['all' => 'All'];

                foreach ($rows as $row) {
                    $categories[(string) $row['slug']] = (string) $row['label'];
                }

                $base['project_categories'] = $categories;
            }
        }

        /* ---------------------------------------------------- projects ---- */

        if (Database::hasTable('projects')) {
            $projects = self::projectsFromDatabase();

            if ($projects !== []) {
                $base['projects'] = $projects;
            }
        }

        return $base;
    }

    /**
     * Build the project list from the database, including case-study bullets.
     *
     * @return list<array<string, mixed>>
     */
    private static function projectsFromDatabase(): array
    {
        $hasPublished = in_array('is_published', self::columns('projects'), true);

        $rows = Database::all(
            'SELECT * FROM projects' . ($hasPublished ? ' WHERE is_published = 1' : '') . ' ORDER BY order_no, id'
        );

        if ($rows === []) {
            return [];
        }

        // One query for every bullet, grouped in PHP — a query per project
        // would be 17 round trips on this page alone.
        $features = [];

        if (Database::hasTable('project_features')) {
            foreach (Database::all('SELECT project_id, feature FROM project_features ORDER BY project_id, order_no, id') as $row) {
                $features[(int) $row['project_id']][] = (string) $row['feature'];
            }
        }

        $projects = [];

        foreach ($rows as $row) {
            $id      = (int) $row['id'];
            $title   = (string) $row['title'];
            $summary = trim((string) ($row['summary'] ?? ''));

            if ($summary === '') {
                $summary = str_excerpt(strip_tags((string) ($row['description'] ?? '')), 260);
            }

            $image = (string) ($row['image'] ?? '');

            $projects[] = [
                'slug'       => (string) ($row['slug'] ?: self::slugify($title)),
                'repo'       => (string) ($row['repo_name'] ?? ''),
                'title'      => $title,
                'subtitle'   => (string) ($row['subtitle'] ?? ''),
                'category'   => self::normaliseCategory((string) ($row['category'] ?? '')),
                'featured'   => !empty($row['featured']),
                'year'       => (string) ($row['year'] ?? ''),
                // Guard against a path whose file was deleted or renamed.
                'image'      => ($image !== '' && is_file(APP_ROOT . '/' . ltrim($image, '/'))) ? $image : '',
                'summary'    => $summary,
                'problem'    => (string) ($row['problem'] ?? ''),
                'role'       => (string) ($row['role'] ?? ''),
                'stack'      => self::splitList((string) ($row['skills_used'] ?? '')),
                'features'   => $features[$id] ?? [],
                'challenges' => (string) ($row['challenges'] ?? ''),
                'outcome'    => (string) ($row['outcome'] ?? ''),
                'learned'    => (string) ($row['learned'] ?? ''),
                'demo'       => (string) ($row['view_link'] ?? ''),
            ];
        }

        return $projects;
    }

    /** @return list<string> */
    private static function columns(string $table): array
    {
        $rows = Database::all('SHOW COLUMNS FROM `' . str_replace('`', '', $table) . '`');

        return array_column($rows, 'Field');
    }

    /** @return list<string> */
    private static function splitList(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    /**
     * Map a stored category onto the site's taxonomy.
     *
     * The original schema used web / app / terminal, and those values still
     * appear in older rows. Without translation their cards render but match no
     * filter button, so they vanish the moment a visitor narrows the grid.
     */
    private static function normaliseCategory(string $category): string
    {
        $category = strtolower(trim($category));

        $legacy = [
            'web'     => 'fullstack', 'app'     => 'mobile', 'terminal' => 'systems',
            'console' => 'systems',   'desktop' => 'systems', 'iot'     => 'systems',
        ];

        $category = $legacy[$category] ?? $category;
        $known    = ['backend', 'fullstack', 'mobile', 'systems', 'ai', 'frontend'];

        return in_array($category, $known, true) ? $category : 'fullstack';
    }

    public static function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';

        return trim($value, '-');
    }

    /** Map a legacy Font Awesome class or platform name onto our inline icon set. */
    private static function normaliseIcon(string $platform, string $faClass): string
    {
        $haystack = strtolower($platform . ' ' . $faClass);

        return match (true) {
            str_contains($haystack, 'github')   => 'github',
            str_contains($haystack, 'linkedin') => 'linkedin',
            str_contains($haystack, 'facebook') => 'facebook',
            str_contains($haystack, 'skype')    => 'skype',
            str_contains($haystack, 'twitter'), str_contains($haystack, ' x ') => 'twitter',
            str_contains($haystack, 'mail'), str_contains($haystack, 'envelope') => 'mail',
            str_contains($haystack, 'phone'), str_contains($haystack, 'whatsapp') => 'phone',
            str_contains($haystack, 'web'), str_contains($haystack, 'site') => 'globe',
            default                             => 'external',
        };
    }
}
