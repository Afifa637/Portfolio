<?php

/**
 * Custom 404 — "Looks like this route never compiled."
 *
 * Included directly by project.php and router.php for unknown routes, and used
 * by Apache via ErrorDocument, so it bootstraps only when needed.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (!headers_sent()) {
    http_response_code(404);
}

$identity        = Content::get('identity', []);
$pageTitle       = 'Route not found — ' . $identity['name'];
$pageDescription = 'This route never compiled. Head back to the portfolio.';
$isHome          = false;
$requested       = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

?>
<!doctype html>
<html lang="en" class="no-js" data-theme="dark">
<head>
    <?php view('layout/head', compact('pageTitle', 'pageDescription')); ?>
    <meta name="robots" content="noindex, follow">
</head>
<body data-home="<?= e(base_path()) ?>/">
    <?php view('layout/header', compact('isHome')); ?>

    <main class="site" id="main">
        <section class="section" style="min-height:100svh;display:grid;align-items:center;padding-top:calc(var(--nav-h) + 3rem)">
            <div class="container">
                <p class="label" style="margin-bottom:1.5rem"><span class="live" style="background:var(--red)"></span> HTTP 404 · route not found</p>

                <h1 style="font-size:clamp(3rem,1.2rem + 7vw,7.5rem);line-height:.9;letter-spacing:-.06em;max-width:16ch">
                    Looks like this route <span class="serif">never compiled.</span>
                </h1>

                <pre class="code-out" style="margin-top:2.2rem;max-width:40rem" aria-label="Error output"><span class="t-3">$</span> GET <?= e($requested) ?>

<span style="color:var(--red)">error[E0404]</span>: no route matches this path
  <span class="t-3">--></span> router
  <span class="t-3">|</span>
  <span class="t-3">=</span> help: the page may have moved, or the link is out of date</pre>

                <div class="hero-actions" style="margin-top:2rem">
                    <a class="btn btn-primary" href="<?= e(home_url()) ?>"><?= icon('arrow-right', 16, 'i-shift') ?> Go home</a>
                    <a class="btn btn-ghost" href="<?= e(home_url('work')) ?>"><?= icon('layers', 16) ?> View projects</a>
                    <button class="btn btn-ghost" type="button" data-open="palette"><?= icon('search', 16) ?> Search the site</button>
                </div>
            </div>
        </section>
    </main>

    <?php view('layout/footer', compact('isHome')); ?>
</body>
</html>
