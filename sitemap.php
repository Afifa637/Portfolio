<?php

/**
 * XML sitemap.
 *
 * Generated rather than static so case-study links stay in step with the
 * projects in the admin (or config/profile.php without a database). Served at /sitemap.xml via the .htaccess rewrite.
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

// Only real documents: search engines ignore #fragments, so the home page
// sections are not listed separately.
$urls = [
    ['loc' => url(),              'priority' => '1.0', 'freq' => 'weekly'],
    ['loc' => url('resume.php'),  'priority' => '0.8', 'freq' => 'monthly'],
];

foreach (Content::get('projects', []) as $project) {
    $urls[] = [
        'loc'      => origin() . project_url((string) $project['slug']),
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
