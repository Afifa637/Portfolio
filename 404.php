<?php

/**
 * Custom 404. Kept to the same design language so a wrong URL still reads as
 * part of the site rather than a server default.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

http_response_code(404);

$identity = Content::get('identity', []);

$pageTitle       = 'Page not found — ' . $identity['name'];
$pageDescription = 'That page does not exist. Head back to the portfolio home page.';

?>
<!doctype html>
<html lang="en" data-theme="dark">
<head>
    <?php view('layout/head', compact('pageTitle', 'pageDescription')); ?>
    <meta name="robots" content="noindex, follow">
</head>
<body>
    <main id="main">
        <section class="section" style="min-height:100svh;display:grid;place-items:center;text-align:center">
            <div class="container" style="max-width:38rem">
                <p class="eyebrow" style="margin-inline:auto"><?= icon('terminal', 13) ?> Error 404</p>

                <h1 style="font-size:var(--fs-5xl);margin-bottom:var(--sp-4)">
                    <span class="accent-text">Nothing</span> here.
                </h1>

                <p class="lead" style="margin-bottom:var(--sp-7)">
                    That address does not match anything on this site. It may have moved,
                    or the link that brought you here may be out of date.
                </p>

                <div class="hero-actions" style="justify-content:center">
                    <a class="btn btn-primary" href="<?= e(url()) ?>">
                        <?= icon('arrow-right', 17) ?> Back to the portfolio
                    </a>
                    <a class="btn btn-ghost" href="<?= e(url('#projects')) ?>">
                        <?= icon('layers', 17) ?> See the projects
                    </a>
                </div>

                <p class="mono text-mute" style="margin-top:var(--sp-8)">
                    <?= e($identity['name']) ?> · <?= e($identity['title']) ?>
                </p>
            </div>
        </section>
    </main>

    <script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
</body>
</html>
