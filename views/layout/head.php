<?php

/**
 * Document head: metadata, social cards, structured data, and asset loading.
 *
 * @var string $pageTitle
 * @var string $pageDescription
 * @var string $canonical
 */

declare(strict_types=1);

$identity = Content::get('identity', []);
$seo      = Content::get('seo', []);
$socials  = Content::get('socials', []);

$title       = $pageTitle       ?? $seo['title'] ?? $identity['name'];
$description = $pageDescription ?? $seo['description'] ?? '';
$canonical   = $canonical       ?? url();
$ogImage     = url(asset($seo['image'] ?? $identity['avatar']));

/* Person schema — lets search engines associate the site with a real person
   and surface the knowledge-panel fields (job title, alma mater, profiles). */
$schema = [
    '@context'     => 'https://schema.org',
    '@type'        => 'Person',
    'name'         => $identity['name'],
    'jobTitle'     => $identity['title'],
    'url'          => url(),
    'image'        => url(asset($identity['avatar'])),
    'email'        => 'mailto:' . $identity['email'],
    'description'  => $description,
    'address'      => [
        '@type'           => 'PostalAddress',
        'addressLocality' => 'Khulna',
        'addressCountry'  => 'BD',
    ],
    'alumniOf' => [
        '@type' => 'CollegeOrUniversity',
        'name'  => 'Khulna University of Engineering & Technology',
    ],
    'knowsAbout' => array_values(array_unique(array_merge(
        ...array_map(static fn(array $g): array => $g['items'], Content::get('skills', []))
    ))),
    'sameAs' => array_values(array_filter(array_map(
        static fn(array $s): string => str_starts_with($s['url'], 'mailto:') ? '' : $s['url'],
        $socials
    ))),
];

?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

<title><?= e($title) ?></title>
<meta name="description" content="<?= e($description) ?>">
<?php if (!empty($seo['keywords'])): ?>
<meta name="keywords" content="<?= e($seo['keywords']) ?>">
<?php endif; ?>
<meta name="author" content="<?= e($identity['name']) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
<meta name="theme-color" content="#0b0d14">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($identity['name']) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta property="og:image:alt" content="<?= e($identity['name'] . ' — ' . $identity['title']) ?>">
<meta property="og:locale" content="<?= e($seo['locale'] ?? 'en_US') ?>">

<!-- X / Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($title) ?>">
<meta name="twitter:description" content="<?= e($description) ?>">
<meta name="twitter:image" content="<?= e($ogImage) ?>">
<?php if (!empty($seo['twitter'])): ?>
<meta name="twitter:creator" content="@<?= e(ltrim($seo['twitter'], '@')) ?>">
<?php endif; ?>

<!-- Icons -->
<link rel="icon" href="<?= e(asset('assets/favicon.svg')) ?>" type="image/svg+xml">
<link rel="apple-touch-icon" href="<?= e(asset('assets/apple-touch-icon.png')) ?>">
<link rel="manifest" href="<?= e(asset('site.webmanifest')) ?>">

<!--
  Fonts: preconnect opens the TLS handshake early, and `display=swap` renders
  fallback text immediately rather than blocking first paint on the webfont.
-->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style"
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400..700&family=JetBrains+Mono:wght@400..500&family=Sora:wght@600..700&display=swap">
<link rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400..700&family=JetBrains+Mono:wght@400..500&family=Sora:wght@600..700&display=swap"
      media="print" onload="this.media='all'">
<noscript>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400..700&family=JetBrains+Mono:wght@400..500&family=Sora:wght@600..700&display=swap">
</noscript>

<link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">

<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<script>
    /* Resolve the theme before first paint so a light-mode visitor never sees
       a dark flash. Kept inline and tiny; a separate request would defeat it. */
    (function () {
        try {
            var stored = localStorage.getItem('portfolio-theme');
            var system = matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
            var theme  = stored === 'light' || stored === 'dark' ? stored : system;
            document.documentElement.dataset.theme = theme;
            document.documentElement.style.colorScheme = theme;
        } catch (e) {
            document.documentElement.dataset.theme = 'dark';
        }
    })();
</script>
