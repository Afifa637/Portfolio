<?php

/**
 * Live résumé, generated from the same content as the site.
 *
 *   /resume.php            read on screen
 *   /resume.php?print=1    opens the print dialog (save as PDF)
 *
 * The uploaded CV PDF can go stale; this page cannot, because it is rendered
 * from the CMS on every request. Laid out for one A4 page where content allows.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$identity   = Content::get('identity', []);
$education  = Content::get('education', []);
$skills     = Content::get('skills', []);
$activities = Content::get('activities', []);
$experience = Content::get('experience', []);
$socials    = Content::get('socials', []);
$projects   = Content::get('projects', []);

// Featured first, then the rest, capped so the page stays readable.
usort($projects, static fn(array $a, array $b): int => (int) !empty($b['featured']) <=> (int) !empty($a['featured']));
$selected = array_slice($projects, 0, 6);

$linkedin = null;
foreach ($socials as $s) {
    if ($s['icon'] === 'linkedin') {
        $linkedin = $s['url'];
    }
}

$autoPrint = isset($_GET['print']);

?>
<!doctype html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Résumé — <?= e($identity['name']) ?></title>
    <meta name="description" content="Résumé of <?= e($identity['name']) ?>, <?= e($identity['title']) ?>.">
    <link rel="canonical" href="<?= e(url('resume.php')) ?>">
    <link rel="icon" href="<?= e(asset('assets/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500..750&family=Inter:wght@400..600&family=JetBrains+Mono:wght@400..500&display=swap">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
    <style>
        body { background: var(--bg); }
        .cv { max-width: 52rem; margin: 2rem auto 4rem; padding: 3rem 3.2rem; background: var(--surface); border: 1px solid var(--line-2); border-radius: var(--r-1); font-size: 0.875rem; line-height: 1.5; color: var(--text); }
        .cv-bar { max-width: 52rem; margin: 1.5rem auto 0; display: flex; gap: .5rem; justify-content: space-between; align-items: center; padding: 0 .25rem; }
        .cv h1 { font-size: 2.6rem; letter-spacing: -0.045em; line-height: 1; }
        .cv .role { margin-top: .4rem; font-size: 1rem; color: var(--amber); font-weight: 560; }
        .cv .contact { display: flex; flex-wrap: wrap; gap: .3rem 1.1rem; margin-top: .9rem; font-family: var(--f-mono); font-size: .75rem; color: var(--text-2); }
        .cv .summary { margin-top: 1.2rem; color: var(--text-2); }
        .cv section { margin-top: 1.6rem; }
        .cv h2 { font-family: var(--f-mono); font-size: .6875rem; font-weight: 500; letter-spacing: .14em; text-transform: uppercase; color: var(--text-3); padding-bottom: .4rem; border-bottom: 1px solid var(--line-2); margin-bottom: .8rem; }
        .cv .item { display: grid; grid-template-columns: 1fr auto; gap: 0 1rem; margin-bottom: .75rem; break-inside: avoid; }
        .cv .item strong { font-weight: 600; }
        .cv .item .when { font-family: var(--f-mono); font-size: .75rem; color: var(--text-3); white-space: nowrap; }
        .cv .item .sub { grid-column: 1 / -1; color: var(--text-2); }
        .cv .item .stack { grid-column: 1 / -1; font-family: var(--f-mono); font-size: .7rem; color: var(--text-3); margin-top: .15rem; }
        .cv .skills { display: grid; grid-template-columns: 7rem 1fr; gap: .35rem 1rem; }
        .cv .skills dt { font-weight: 600; }
        .cv .skills dd { margin: 0; color: var(--text-2); }
        @page { size: A4; margin: 14mm; }
        @media print {
            body { background: #fff; }
            .cv-bar { display: none; }
            .cv { margin: 0; padding: 0; border: 0; max-width: none; font-size: 9.6pt; }
            .cv h1 { font-size: 24pt; }
            a { color: inherit; }
        }
        @media (width <= 640px) { .cv { padding: 1.6rem 1.2rem; margin-top: 1rem; } .cv .skills { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="cv-bar">
        <a class="btn btn-ghost btn-sm" href="<?= e(home_url('resume')) ?>">← Back to portfolio</a>
        <div class="hero-actions">
            <a class="btn btn-ghost btn-sm" href="<?= e(url(ltrim((string) $identity['resume'], '/'))) ?>"><?= icon('download', 14) ?> Uploaded CV</a>
            <button class="btn btn-primary btn-sm" type="button" onclick="window.print()"><?= icon('printer', 14) ?> Print / save as PDF</button>
        </div>
    </div>

    <main class="cv">
        <header>
            <h1><?= e($identity['name']) ?></h1>
            <p class="role"><?= e($identity['title']) ?> · <?= e($identity['subtitle']) ?></p>
            <p class="contact">
                <span><?= e($identity['email']) ?></span>
                <span><?= e($identity['location']) ?></span>
                <span>github.com/<?= e($identity['github_user']) ?></span>
                <?php if ($linkedin): ?><span><?= e(preg_replace('#^https?://(www\.)?#', '', rtrim($linkedin, '/'))) ?></span><?php endif; ?>
            </p>
            <p class="summary"><?= e($identity['pitch']) ?></p>
        </header>

        <section>
            <h2>Education</h2>
            <?php foreach ($education as $item): ?>
                <div class="item">
                    <strong><?= e($item['degree']) ?></strong>
                    <span class="when"><?= e($item['start']) ?> – <?= e($item['end']) ?></span>
                    <span class="sub"><?= e($item['institution']) ?><?= $item['grade'] !== '' ? ' · ' . e($item['grade']) : '' ?></span>
                </div>
            <?php endforeach; ?>
        </section>

        <?php if ($experience !== []): ?>
            <section>
                <h2>Experience</h2>
                <?php foreach ($experience as $item): ?>
                    <div class="item">
                        <strong><?= e($item['title']) ?><?= $item['org'] !== '' ? ' — ' . e($item['org']) : '' ?></strong>
                        <span class="when"><?= e($item['start']) ?> – <?= e($item['end']) ?></span>
                        <?php if ($item['body'] !== ''): ?><span class="sub"><?= e($item['body']) ?></span><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <section>
            <h2>Selected projects</h2>
            <?php foreach ($selected as $p): ?>
                <div class="item">
                    <strong><?= e($p['title']) ?><?= $p['subtitle'] !== '' ? ' — ' . e($p['subtitle']) : '' ?></strong>
                    <span class="when"><?= e($p['year']) ?></span>
                    <span class="sub"><?= e(str_excerpt($p['summary'], 170)) ?></span>
                    <span class="stack"><?= e(implode(' · ', array_slice($p['stack'], 0, 7))) ?></span>
                </div>
            <?php endforeach; ?>
        </section>

        <section>
            <h2>Technical skills</h2>
            <dl class="skills">
                <?php foreach ($skills as $group): ?>
                    <dt><?= e($group['group']) ?></dt>
                    <dd><?= e(implode(', ', $group['items'])) ?></dd>
                <?php endforeach; ?>
            </dl>
        </section>

        <?php if ($activities !== []): ?>
            <section>
                <h2>Activities</h2>
                <?php foreach ($activities as $a): ?>
                    <div class="item">
                        <strong><?= e($a['org']) ?></strong>
                        <span class="when"><?= e($a['role']) ?></span>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>

    <?php if ($autoPrint): ?>
        <script>addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
    <?php endif; ?>
</body>
</html>
