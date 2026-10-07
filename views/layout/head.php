<?php

/**
 * Document head.
 *
 * Per-page variables (all optional):
 *   $pageTitle, $pageDescription, $canonical, $pageImage
 *   $pageType     'website' | 'article'
 *   $withSections whether to load sections.css (public pages)
 *   $schemaExtra  additional JSON-LD node for this page
 *   $withPayload  whether to embed the content payload the modules read
 */

declare(strict_types=1);

$identity = Content::get('identity', []);
$seo      = Content::get('seo', []);
$socials  = Content::get('socials', []);

$title        = $pageTitle       ?? ($seo['title'] ?? $identity['name']);
$description  = $pageDescription ?? ($seo['description'] ?? '');
$canonical    = $canonical       ?? url();
$ogImage      = asset_url($pageImage ?? ($seo['image'] ?? $identity['avatar']));
$ogType       = $pageType        ?? 'website';
$withSections = $withSections    ?? true;
$withPayload  = $withPayload     ?? true;

$fonts = 'https://fonts.googleapis.com/css2'
    . '?family=Bricolage+Grotesque:opsz,wght@12..96,400..800'
    . '&family=Instrument+Serif:ital@1'
    . '&family=Inter:wght@400..600'
    . '&family=JetBrains+Mono:wght@400..500'
    . '&display=swap';

/*
 * Person schema, so search engines can tie the site to a real person and
 * surface job title, education and profiles in a knowledge panel.
 */
$schema = [
    '@context'    => 'https://schema.org',
    '@type'       => 'Person',
    'name'        => $identity['name'],
    'jobTitle'    => $identity['title'],
    'url'         => url(),
    'image'       => asset_url($identity['avatar']),
    'email'       => 'mailto:' . $identity['email'],
    'description' => $seo['description'] ?? $description,
    'address'     => ['@type' => 'PostalAddress', 'addressLocality' => 'Khulna', 'addressCountry' => 'BD'],
    'alumniOf'    => ['@type' => 'CollegeOrUniversity', 'name' => 'Khulna University of Engineering & Technology'],
    'knowsAbout'  => array_values(array_unique(array_merge(
        ...array_map(static fn(array $g): array => $g['items'], Content::get('skills', [])) ?: [[]]
    ))),
    'sameAs' => array_values(array_filter(array_map(
        static fn(array $s): string => str_starts_with($s['url'], 'mailto:') ? '' : $s['url'],
        $socials
    ))),
];

$graph = isset($schemaExtra) ? [$schema, $schemaExtra] : [$schema];

?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

<title><?= e($title) ?></title>
<meta name="description" content="<?= e($description) ?>">
<meta name="author" content="<?= e($identity['name']) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta name="robots" content="index, follow, max-image-preview:large">
<meta name="theme-color" content="#0d0c0b" media="(prefers-color-scheme: dark)">
<meta name="theme-color" content="#f5f1e8" media="(prefers-color-scheme: light)">

<meta property="og:type" content="<?= e($ogType) ?>">
<meta property="og:site_name" content="<?= e($identity['name']) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta property="og:image:alt" content="<?= e($identity['name'] . ' — ' . $identity['title']) ?>">
<meta property="og:locale" content="<?= e($seo['locale'] ?? 'en_US') ?>">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($title) ?>">
<meta name="twitter:description" content="<?= e($description) ?>">
<meta name="twitter:image" content="<?= e($ogImage) ?>">

<link rel="icon" href="<?= e(asset('assets/favicon.svg')) ?>" type="image/svg+xml">
<link rel="apple-touch-icon" href="<?= e(asset('assets/apple-touch-icon.png')) ?>">
<link rel="manifest" href="<?= e(asset('site.webmanifest')) ?>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="<?= e($fonts) ?>" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="<?= e($fonts) ?>"></noscript>

<link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
<?php if ($withSections): ?>
<link rel="stylesheet" href="<?= e(asset('assets/css/sections.css')) ?>">
<?php endif; ?>

<script>
    /* Theme and JS flag resolved before first paint, so a light-theme visitor
       never sees a dark flash and no-JS visitors never see hidden content. */
    (function (d) {
        var r = d.documentElement;
        r.classList.remove('no-js');
        r.classList.add('js');
        try {
            var t = localStorage.getItem('portfolio-theme');
            if (t !== 'light' && t !== 'dark') {
                t = matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
            }
            r.dataset.theme = t;
        } catch (e) { r.dataset.theme = 'dark'; }
    })(document);
</script>

<?= import_map() ?>

<script type="application/ld+json"><?= json_encode(
    ['@context' => 'https://schema.org', '@graph' => array_map(static function (array $n): array {
        unset($n['@context']);
        return $n;
    }, $graph)],
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG
) ?></script>

<?php if ($withPayload): ?>
<script type="application/json" id="portfolio-data"><?= json_encode(
    Knowledge::payload(),
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
) ?></script>
<?php endif; ?>
