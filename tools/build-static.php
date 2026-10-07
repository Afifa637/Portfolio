<?php

/**
 * Static site builder.
 *
 *   php tools/build-static.php [--base=/Portfolio/] [--url=https://…]
 *
 * Renders the site to dist/ as plain HTML so it can be served by GitHub Pages,
 * Netlify, S3, or any static host — no PHP runtime, no database.
 *
 * This is possible because the site was built content-first: config/profile.php
 * is the source of truth and the database is only an override layer. The three
 * server-dependent surfaces are handled as follows:
 *
 *   • Contact form   — the POST handler cannot run, so the form is replaced
 *                      with a direct-email panel rather than left as a control
 *                      that silently fails.
 *   • api/github.php — the background refresh is dropped; the build bakes in
 *                      whatever the GitHub cache held at build time, and the
 *                      workflow rebuilds on a schedule to keep it current.
 *   • /admin         — omitted entirely. Editing happens in config/profile.php
 *                      and redeploys on push.
 *
 * The PHP application is untouched and stays fully functional on real PHP
 * hosting; this is an additional output, not a replacement.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("CLI only.\n");
}

/* ------------------------------------------------------------- options ---- */

$options = getopt('', ['base::', 'url::', 'out::']);

// Project pages live at https://<user>.github.io/<repo>/, so every root-relative
// path needs that prefix. A custom domain would use '/'.
$base   = rtrim((string) ($options['base'] ?? '/Portfolio/'), '/') . '/';
$siteUrl = rtrim((string) ($options['url'] ?? 'https://afifa637.github.io/Portfolio'), '/');
$outDir = (string) ($options['out'] ?? dirname(__DIR__) . '/dist');

// Render from config/profile.php alone: no database in CI, and this guarantees
// the deployed site matches exactly what is committed.
putenv('DB_ENABLED=false');
$_ENV['DB_ENABLED'] = 'false';
putenv('APP_ENV=production');
$_ENV['APP_ENV'] = 'production';
putenv('APP_URL=' . $siteUrl);
$_ENV['APP_URL'] = $siteUrl;

define('STATIC_BUILD', true);

$root = dirname(__DIR__);

echo "\n  Static build\n";
echo "  ────────────\n";
echo "  base : {$base}\n";
echo "  url  : {$siteUrl}\n";
echo "  out  : {$outDir}\n\n";

/* -------------------------------------------------------------- helpers --- */

function rmrf(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    foreach (scandir($dir) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $path = $dir . '/' . $entry;
        is_dir($path) ? rmrf($path) : @unlink($path);
    }

    @rmdir($dir);
}

/** Recursively copy, skipping anything that should not be published. */
function copyTree(string $from, string $to, array $skip = []): int
{
    if (!is_dir($from)) {
        return 0;
    }

    @mkdir($to, 0775, true);
    $count = 0;

    foreach (scandir($from) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..' || in_array($entry, $skip, true)) {
            continue;
        }

        $src = $from . '/' . $entry;
        $dst = $to . '/' . $entry;

        if (is_dir($src)) {
            $count += copyTree($src, $dst, $skip);
        } elseif (@copy($src, $dst)) {
            $count++;
        }
    }

    return $count;
}

/* ---------------------------------------------------------------- build --- */

rmrf($outDir);
@mkdir($outDir, 0775, true);

require_once $root . '/includes/bootstrap.php';

$identity = Content::get('identity', []);

/* 1. Render the home page through the real view layer. */

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST']      = parse_url($siteUrl, PHP_URL_HOST) ?: 'localhost';
$_SERVER['SCRIPT_NAME']    = '/index.php';
$_SERVER['REQUEST_URI']    = '/';

ob_start();
require $root . '/index.php';
$html = (string) ob_get_clean();

/* 2. Rewrite the pieces that assumed a PHP runtime. */

$replacements = 0;

// download_cv.php streams the file through PHP; link the asset directly.
$html = str_replace('href="download_cv.php"', 'href="' . $base . 'assets/pdf/CV.pdf" download', $html, $n);
$replacements += $n;

// sitemap.php is generated at build time as sitemap.xml.
$html = str_replace('sitemap.xml', 'sitemap.xml', $html);

/*
 * 3. Replace the contact form.
 *
 * Leaving a form whose action 404s would be worse than having no form: a
 * recruiter would type a message, press send, and lose it. A direct-email
 * panel is honest about what it does and takes one click.
 */
$emailHref = 'mailto:' . $identity['email']
    . '?subject=' . rawurlencode('Opportunity for ' . $identity['name'])
    . '&body=' . rawurlencode("Hi Afifa,\n\n");

$panel = <<<HTML
<div class="form" data-reveal>
    <div class="card" style="display:grid;gap:var(--sp-4)">
        <h3 style="font-size:var(--fs-lg)">Send me a message</h3>
        <p class="text-dim" style="font-size:var(--fs-base);line-height:1.7">
            The quickest way to reach me is email — I read everything and reply within a day or two.
            Tell me about the role or project and I will come back with specifics.
        </p>
        <div class="hero-actions" style="margin:0">
            <a class="btn btn-primary" href="{$emailHref}">Email me</a>
            <button class="btn btn-ghost" type="button" data-copy="{$identity['email']}">
                <span data-copy-label>Copy address</span>
            </button>
        </div>
        <p class="form-note" style="margin:0">
            <span>Prefer LinkedIn? The link is in the footer and at the top of this page.</span>
        </p>
    </div>
</div>
HTML;

$html = preg_replace(
    '#<form class="form" id="contact-form".*?</form>#s',
    $panel,
    $html,
    1,
    $formReplaced
);

/* 4. Drop the background-refresh script; there is no endpoint to call. */
$html = preg_replace('#<script>\s*/\* The GitHub payload.*?</script>#s', '', (string) $html) ?? $html;
$html = preg_replace("#<script>\s*addEventListener\('load'.*?</script>#s", '', (string) $html) ?? $html;

/* 5. Root-relative paths need the project-pages prefix. */
$html = str_replace('href="/"', 'href="' . $base . '"', (string) $html);

file_put_contents($outDir . '/index.html', $html);
printf("  ✓ index.html                      %6.1f KB\n", strlen($html) / 1024);
printf("    contact form replaced: %s · cv link rewritten: %d\n", $formReplaced ? 'yes' : 'NO', $replacements);

/* 6. A 404 page that keeps the design language. */

ob_start();
$_SERVER['REQUEST_URI'] = '/404';
require $root . '/404.php';
$notFound = (string) ob_get_clean();
$notFound = str_replace('href="download_cv.php"', 'href="' . $base . 'assets/pdf/CV.pdf"', $notFound);
file_put_contents($outDir . '/404.html', $notFound);
printf("  ✓ 404.html                        %6.1f KB\n", strlen($notFound) / 1024);

/* 7. Assets. */

$copied = copyTree($root . '/assets', $outDir . '/assets', ['original', 'fonts']);
printf("  ✓ assets/                         %6d files\n", $copied);

/* 8. Sitemap and robots, pointing at the real origin. */

ob_start();
require $root . '/sitemap.php';
$sitemap = (string) ob_get_clean();
file_put_contents($outDir . '/sitemap.xml', $sitemap);

file_put_contents($outDir . '/robots.txt', implode("\n", [
    '# robots.txt — ' . $identity['name'],
    '',
    'User-agent: *',
    'Allow: /',
    '',
    'Sitemap: ' . $siteUrl . '/sitemap.xml',
    '',
]));

copy($root . '/site.webmanifest', $outDir . '/site.webmanifest');

// Tells GitHub Pages not to run the output through Jekyll, which would
// otherwise ignore any file or directory beginning with an underscore.
touch($outDir . '/.nojekyll');

echo "  ✓ sitemap.xml, robots.txt, site.webmanifest, .nojekyll\n";

/* 9. Report. */

$bytes = 0;
$files = 0;

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($outDir, FilesystemIterator::SKIP_DOTS));

foreach ($iterator as $file) {
    $bytes += $file->getSize();
    $files++;
}

printf("\n  %d files, %.1f MB total\n", $files, $bytes / 1048576);
echo "  Preview:  php -S localhost:8001 -t dist\n\n";
