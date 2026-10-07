<?php

/**
 * Derived knowledge about the portfolio.
 *
 * Everything the interactive features claim — "Spring Boot: used in 2
 * projects", the edges in the hero graph, the technology count in the metrics
 * strip — is computed here from the project stacks the owner wrote. None of it
 * is typed in by hand, so it cannot drift from the content or overstate it.
 *
 * It also assembles the single JSON payload the front-end modules read (the
 * command palette, Ask Afifa, the terminal, recruiter mode, the stack explorer,
 * build mode), so every one of them is driven by the same CMS content.
 */

declare(strict_types=1);

final class Knowledge
{
    /** @var array<string, mixed>|null */
    private static ?array $cache = null;

    /**
     * Names that mean the same technology, keyed by canonical form.
     * Matching is on normalised keys, never substrings — "C" must not match
     * every stack that happens to contain the letter.
     */
    private const ALIASES = [
        'spring boot'        => ['spring boot', 'spring boot 3'],
        'jpa'                => ['jpa', 'jpa / hibernate', 'hibernate', 'spring data jpa'],
        'rest apis'          => ['rest apis', 'rest', 'rest api'],
        'jwt'                => ['jwt', 'jwt auth'],
        'swagger'            => ['swagger', 'swagger / openapi', 'openapi'],
        'android'            => ['android', 'android (java/kotlin)'],
        'firestore'          => ['firestore', 'cloud firestore', 'firebase firestore'],
        'firebase'           => ['firebase'],
        'firebase auth'      => ['firebase auth'],
        'docker'             => ['docker'],
        'react'              => ['react'],
        'react native'       => ['react native'],
        'postgresql'         => ['postgresql', 'postgres'],
        'c++'                => ['c++', 'cpp'],
        'eloquent orm'       => ['eloquent orm', 'eloquent'],
        'swift'              => ['swift', 'swiftui'],
        'game ai'            => ['game ai'],
    ];

    /* ------------------------------------------------------------ keys --- */

    /** Canonical key for a technology name. */
    public static function key(string $name): string
    {
        $k = strtolower(trim($name));
        $k = (string) preg_replace('/\s+/', ' ', $k);

        foreach (self::ALIASES as $canonical => $variants) {
            if (in_array($k, $variants, true)) {
                return $canonical;
            }
        }

        return $k;
    }

    /* --------------------------------------------------------- indexes --- */

    /**
     * Map of technology key => list of project slugs that use it.
     *
     * @return array<string, list<string>>
     */
    public static function techIndex(): array
    {
        return self::build()['techIndex'];
    }

    /** @return list<string> project slugs using any of the given names */
    public static function projectsUsing(array $names): array
    {
        $index = self::techIndex();
        $slugs = [];

        foreach ($names as $name) {
            foreach ($index[self::key((string) $name)] ?? [] as $slug) {
                $slugs[$slug] = true;
            }
        }

        return array_keys($slugs);
    }

    /** @return array<string, array<string, mixed>> projects keyed by slug */
    public static function projectsBySlug(): array
    {
        return self::build()['bySlug'];
    }

    /* ------------------------------------------------------- the graph --- */

    /**
     * Hero System Core: configured nodes, with counts and edges computed from
     * which technologies actually appear together in a project.
     *
     * @return array{nodes: list<array<string, mixed>>, edges: list<array{0:string,1:string,2:int}>}
     */
    public static function coreGraph(): array
    {
        return self::build()['graph'];
    }

    /* --------------------------------------------------------- metrics --- */

    /** @return array<string, int|string|null> */
    public static function metrics(): array
    {
        return self::build()['metrics'];
    }

    /* --------------------------------------------------------- payload --- */

    /**
     * Everything the client-side modules need, in one structure.
     *
     * @return array<string, mixed>
     */
    public static function payload(): array
    {
        return self::build()['payload'];
    }

    /* ----------------------------------------------------------- build --- */

    /** @return array<string, mixed> */
    private static function build(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $projects = Content::get('projects', []);
        $gh       = GitHub::data();

        /* Index every stack entry. */
        $techIndex = [];
        $bySlug    = [];
        $display   = []; // key => the nicest display name seen

        foreach ($projects as $project) {
            $slug = (string) $project['slug'];
            $bySlug[$slug] = $project;

            foreach ($project['stack'] ?? [] as $entry) {
                // Compound entries ("React Native & Expo", "Flutter & Dart") name
                // two technologies; index each so counts agree everywhere.
                foreach (preg_split('/\s*&\s*/', (string) $entry) ?: [] as $tech) {
                    if (trim($tech) === '') {
                        continue;
                    }

                    $k = self::key($tech);
                    $techIndex[$k][] = $slug;
                    $display[$k] ??= trim($tech);
                }
            }

            // Architecture layers name technologies too ("Spring Security, JWT").
            foreach ($project['architecture'] ?? [] as $layer) {
                foreach (array_map('trim', explode(',', (string) ($layer['tech'] ?? ''))) as $tech) {
                    if ($tech === '') {
                        continue;
                    }
                    $k = self::key($tech);
                    $techIndex[$k][] = $slug;
                    $display[$k] ??= $tech;
                }
            }
        }

        foreach ($techIndex as $k => $slugs) {
            $techIndex[$k] = array_values(array_unique($slugs));
        }

        /* Hero graph. */
        $nodes = [];

        foreach (Content::get('core_nodes', []) as $node) {
            $slugs = [];

            foreach ($node['match'] ?? [] as $name) {
                foreach ($techIndex[self::key((string) $name)] ?? [] as $slug) {
                    $slugs[$slug] = true;
                }
            }

            $slugs = array_keys($slugs);

            // Git is in every repository by definition; say so from GitHub's
            // own count instead of pretending it appears in a project stack.
            $repos = !empty($node['all_repos']) ? (int) ($gh['stats']['repos'] ?? 0) : null;

            $nodes[] = [
                'id'       => (string) $node['id'],
                'label'    => (string) $node['label'],
                'group'    => (string) $node['group'],
                'projects' => $slugs,
                'count'    => count($slugs),
                'repos'    => $repos,
            ];
        }

        // An edge means two technologies were used in the same project. The
        // weight is how many projects they share.
        $edges = [];

        for ($i = 0; $i < count($nodes); $i++) {
            for ($j = $i + 1; $j < count($nodes); $j++) {
                $shared = count(array_intersect($nodes[$i]['projects'], $nodes[$j]['projects']));

                if ($shared > 0) {
                    $edges[] = [$nodes[$i]['id'], $nodes[$j]['id'], $shared];
                }
            }
        }

        /* Skills, enriched with evidence. */
        $skills = [];

        foreach (Content::get('skills', []) as $group) {
            $items = [];

            foreach ($group['items'] as $item) {
                $k     = self::key((string) $item);
                $slugs = $techIndex[$k] ?? [];

                // Related = technologies that share a project with this one.
                $related = [];

                foreach ($slugs as $slug) {
                    foreach ($bySlug[$slug]['stack'] ?? [] as $other) {
                        $ok = self::key((string) $other);

                        if ($ok !== $k) {
                            $related[$ok] = ($related[$ok] ?? 0) + 1;
                        }
                    }
                }

                arsort($related);

                $items[] = [
                    'name'     => (string) $item,
                    'key'      => $k,
                    'projects' => $slugs,
                    'related'  => array_slice(array_map(
                        static fn(string $rk): string => $display[$rk] ?? $rk,
                        array_keys($related)
                    ), 0, 6),
                ];
            }

            $skills[] = [
                'group' => (string) $group['group'],
                'icon'  => (string) ($group['icon'] ?? 'code'),
                'note'  => (string) ($group['note'] ?? ''),
                'items' => $items,
            ];
        }

        /* Metrics — every value counted, none typed. */
        $distinctTech = count(array_unique(array_map(
            static fn(string $t): string => self::key($t),
            array_merge(...array_map(static fn(array $p): array => $p['stack'] ?? [], $projects ?: [[]]))
        )));

        $metrics = [
            'projects'     => count($projects),
            'featured'     => count(array_filter($projects, static fn(array $p): bool => !empty($p['featured']))),
            'technologies' => $distinctTech,
            'repos'        => !empty($gh['ok']) ? (int) $gh['stats']['repos'] : null,
            'languages'    => !empty($gh['ok']) ? (int) $gh['stats']['languages'] : null,
            'stars'        => !empty($gh['ok']) ? (int) $gh['stats']['stars'] : null,
            'since'        => !empty($gh['ok']) ? (string) $gh['stats']['since'] : null,
        ];

        /* Client payload. */
        $identity = Content::get('identity', []);

        $payload = [
            'identity' => [
                'name'         => (string) ($identity['name'] ?? ''),
                'title'        => (string) ($identity['title'] ?? ''),
                'subtitle'     => (string) ($identity['subtitle'] ?? ''),
                'location'     => (string) ($identity['location'] ?? ''),
                'email'        => (string) ($identity['email'] ?? ''),
                'availability' => (string) ($identity['availability'] ?? ''),
                'available'    => !empty($identity['available']),
                'pitch'        => (string) ($identity['pitch'] ?? ''),
                'roles'        => array_values($identity['roles'] ?? []),
                'github'       => 'https://github.com/' . ($identity['github_user'] ?? ''),
                'resume'       => url('resume.php'),
                'cv'           => url(ltrim((string) ($identity['resume'] ?? 'download_cv.php'), '/')),
            ],
            'socials'    => array_values(Content::get('socials', [])),
            'education'  => array_values(Content::get('education', [])),
            'activities' => array_values(Content::get('activities', [])),
            'experience' => array_values(Content::get('experience', [])),
            'services'   => array_values(Content::get('services', [])),
            'principles' => array_values(Content::get('principles', [])),
            'journey'    => array_values(Content::get('journey', [])),
            'status'     => Content::get('status', []),
            'categories' => Content::get('project_categories', []),
            'skills'     => $skills,
            'graph'      => ['nodes' => $nodes, 'edges' => $edges],
            'metrics'    => $metrics,
            'projects'   => array_map(static fn(array $p): array => [
                'slug'         => (string) $p['slug'],
                'title'        => (string) $p['title'],
                'subtitle'     => (string) ($p['subtitle'] ?? ''),
                'category'     => (string) $p['category'],
                'featured'     => !empty($p['featured']),
                'year'         => (string) ($p['year'] ?? ''),
                'summary'      => (string) ($p['summary'] ?? ''),
                'problem'      => (string) ($p['problem'] ?? ''),
                'role'         => (string) ($p['role'] ?? ''),
                'stack'        => array_values($p['stack'] ?? []),
                'features'     => array_values($p['features'] ?? []),
                'challenges'   => (string) ($p['challenges'] ?? ''),
                'outcome'      => (string) ($p['outcome'] ?? ''),
                'learned'      => (string) ($p['learned'] ?? ''),
                'decisions'    => (string) ($p['decisions'] ?? ''),
                'security'     => (string) ($p['security'] ?? ''),
                'architecture' => array_values($p['architecture'] ?? []),
                'demo_request' => (string) ($p['demo_request'] ?? ''),
                'demo'         => (string) ($p['demo'] ?? ''),
                'repo'         => (string) ($p['repo'] ?? ''),
                'url'          => project_url((string) $p['slug']),
                'github'       => !empty($p['repo'])
                    ? 'https://github.com/' . ($identity['github_user'] ?? '') . '/' . $p['repo']
                    : '',
            ], $projects),
            'github' => !empty($gh['ok']) ? [
                'recent'    => array_map(static fn(array $r): array => [
                    'name'      => $r['name'],
                    'url'       => $r['url'],
                    'language'  => $r['language'],
                    'pushed_at' => $r['pushed_at'],
                ], array_slice($gh['recent'], 0, 6)),
                'languages' => $gh['languages'],
            ] : null,
        ];

        return self::$cache = [
            'techIndex' => $techIndex,
            'bySlug'    => $bySlug,
            'graph'     => ['nodes' => $nodes, 'edges' => $edges],
            'metrics'   => $metrics,
            'payload'   => $payload,
        ];
    }
}
