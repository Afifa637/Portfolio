<?php

/**
 * XML sitemap.
 *
 * Generated rather than static so project deep links stay in step with
 * config/profile.php. Served at /sitemap.xml via the .htaccess rewrite.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

// Skipped on the CLI, where this file may be included by another script and
// headers would already have been sent.
if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header('Content-Type: application/xml; charset=utf-8');
    header('Cache-Control: public, max-age=86400');
}

$today = date('Y-m-d');

$urls = [
    ['loc' => url(),                  'priority' => '1.0', 'freq' => 'weekly'],
    ['loc' => url('#about'),          'priority' => '0.8', 'freq' => 'monthly'],
    ['loc' => url('#skills'),         'priority' => '0.7', 'freq' => 'monthly'],
    ['loc' => url('#projects'),       'priority' => '0.9', 'freq' => 'weekly'],
    ['loc' => url('#github'),         'priority' => '0.6', 'freq' => 'daily'],
    ['loc' => url('#resume'),         'priority' => '0.8', 'freq' => 'monthly'],
    ['loc' => url('#contact'),        'priority' => '0.7', 'freq' => 'monthly'],
];

foreach (Content::get('projects', []) as $project) {
    $urls[] = [
        'loc'      => url('#project-' . $project['slug']),
        'priority' => !empty($project['featured']) ? '0.8' : '0.6',
        'freq'     => 'monthly',
    ];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url): ?>
    <url>
        <loc><?= e($url['loc']) ?></loc>
        <lastmod><?= $today ?></lastmod>
        <changefreq><?= $url['freq'] ?></changefreq>
        <priority><?= $url['priority'] ?></priority>
    </url>
<?php endforeach; ?>
</urlset>
