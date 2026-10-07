<?php

/**
 * Case-study page: /projects/<slug>  (or project.php?slug=<slug>).
 *
 * A section appears only when it has content, and sections are numbered in
 * the order they appear — so a project whose goal has not been written yet
 * shows no empty "02 Goal" heading. Everything is editable in /admin.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$slug     = strtolower(trim((string) ($_GET['slug'] ?? '')));
$projects = array_values(Content::get('projects', []));
$index    = null;

foreach ($projects as $i => $candidate) {
    if ($candidate['slug'] === $slug) {
        $index = $i;
        break;
    }
}

if ($index === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$project    = $projects[$index];
$identity   = Content::get('identity', []);
$categories = Content::get('project_categories', []);
$repoUrl    = $project['repo'] !== '' ? 'https://github.com/' . $identity['github_user'] . '/' . $project['repo'] : '';
$image      = $project['image'] ?? '';
$hasShot    = $image !== '' && is_file(APP_ROOT . '/' . $image);
$prev       = $projects[($index - 1 + count($projects)) % count($projects)];
$next       = $projects[($index + 1) % count($projects)];
$repo       = GitHub::repo($project['repo']);

$paragraphs = static fn(string $text): array => array_values(array_filter(array_map('trim', preg_split('/\n{2,}/', $text) ?: [])));

/* Sections in reading order; empty ones are dropped and the rest renumbered. */
$sections = array_filter([
    'problem'    => $project['problem'] !== '' ? 'The problem' : null,
    'goal'       => $project['goal'] !== '' ? 'Goal' : null,
    'architecture' => $project['architecture'] !== [] ? 'Architecture' : null,
    'features'   => $project['features'] !== [] ? 'Key features' : null,
    'decisions'  => $project['decisions'] !== '' ? 'Engineering decisions' : null,
    'security'   => $project['security'] !== '' ? 'Security & validation' : null,
    'challenges' => $project['challenges'] !== '' ? 'The hardest part' : null,
    'screens'    => $hasShot ? 'Screens' : null,
    'lessons'    => ($project['outcome'] !== '' || $project['learned'] !== '') ? 'Outcome & lessons' : null,
    'future'     => $project['future'] !== '' ? 'What I would improve' : null,
]);

$number = static function (string $key) use ($sections): string {
    return sprintf('%02d', array_search($key, array_keys($sections), true) + 1);
};

$pageTitle       = $project['title'] . ' — case study · ' . $identity['name'];
$pageDescription = str_excerpt($project['summary'], 155);
$canonical       = origin() . project_url($project['slug']);
$pageImage       = $hasShot ? $image : null;
$pageType        = 'article';
$schemaExtra     = [
    '@type'       => 'CreativeWork',
    'name'        => $project['title'],
    'description' => $project['summary'],
    'author'      => ['@type' => 'Person', 'name' => $identity['name']],
    'dateCreated' => $project['year'],
    'keywords'    => implode(', ', $project['stack']),
    'url'         => $canonical,
] + ($repoUrl !== '' ? ['codeRepository' => $repoUrl] : []);

$isHome = false;

?>
<!doctype html>
<html lang="en" class="no-js" data-theme="dark">
<head>
    <?php view('layout/head', compact('pageTitle', 'pageDescription', 'canonical', 'pageImage', 'pageType', 'schemaExtra')); ?>
</head>
<body data-home="<?= e(base_path()) ?>/">
    <?php view('layout/header', compact('isHome')); ?>

    <main class="site" id="main">
        <article id="cs-page" data-slug="<?= e($project['slug']) ?>">
            <header class="container cs-hero" data-section="top" data-label="Case study">
                <a class="cs-back" href="<?= e(home_url('work')) ?>"><?= icon('arrow-right', 15) ?> All work</a>

                <p class="label">
                    <span style="color:var(--amber)"><?= e($categories[$project['category']] ?? $project['category']) ?></span>
                    <span>· <?= e($project['year']) ?></span>
                    <?php if (!empty($project['featured'])): ?><span>· featured</span><?php endif; ?>
                </p>

                <h1 class="cs-title" style="view-transition-name: title-<?= e($project['slug']) ?>"><?= e($project['title']) ?></h1>

                <?php if ($project['subtitle'] !== ''): ?>
                    <p class="label" style="color:var(--text-2);margin-bottom:1rem"><?= e($project['subtitle']) ?></p>
                <?php endif; ?>

                <p class="cs-impact"><?= e($project['summary']) ?></p>

                <dl class="cs-meta">
                    <div><dt>Role</dt><dd><?= e($project['role'] !== '' ? $project['role'] : '—') ?></dd></div>
                    <div><dt>Year</dt><dd><?= e($project['year']) ?></dd></div>
                    <div><dt>Status</dt><dd><?= $project['demo'] !== '' ? 'Deployed' : ($repoUrl !== '' ? 'Open source' : '—') ?></dd></div>
                    <div><dt>Last commit</dt><dd><?= !empty($repo['pushed_at']) ? e(time_ago($repo['pushed_at'])) : '—' ?></dd></div>
                </dl>

                <div class="tags" style="margin-top:1.4rem">
                    <?php foreach ($project['stack'] as $tech): ?><span class="tag"><?= e($tech) ?></span><?php endforeach; ?>
                </div>

                <div class="hero-actions" style="margin-top:1.6rem">
                    <?php if ($repoUrl !== ''): ?>
                        <a class="btn btn-primary" href="<?= e($repoUrl) ?>" target="_blank" rel="noopener noreferrer" data-cursor="external" data-track="github">
                            <?= icon('github', 16) ?> Source code
                        </a>
                    <?php endif; ?>
                    <?php if ($project['demo'] !== ''): ?>
                        <a class="btn btn-ghost" href="<?= e($project['demo']) ?>" target="_blank" rel="noopener noreferrer" data-cursor="external">
                            <?= icon('external', 16, 'i-up') ?> Live demo
                        </a>
                    <?php endif; ?>
                    <button class="btn btn-ghost" type="button" data-open="ask"><span class="orb" style="width:14px;height:14px" aria-hidden="true"></span> Ask about it</button>
                </div>

                <?php if ($hasShot): ?>
                    <figure class="cs-visual" style="view-transition-name: shot-<?= e($project['slug']) ?>">
                        <?= picture($image, 'Screenshot of ' . $project['title'], ['width' => 1280, 'height' => 800, 'loading' => 'eager', 'fetchpriority' => 'high']) ?>
                    </figure>
                <?php endif; ?>
            </header>

            <div class="container cs-body">
                <nav aria-label="Case study sections">
                    <ol class="cs-toc" role="list">
                        <?php foreach ($sections as $key => $title): ?>
                            <li><a href="#<?= e($key) ?>"><span><?= $number($key) ?></span><?= e($title) ?></a></li>
                        <?php endforeach; ?>
                    </ol>
                </nav>

                <div>
                    <?php if (isset($sections['problem'])): ?>
                        <section class="cs-sec" id="problem">
                            <p class="label"><b style="color:var(--amber);font-weight:500"><?= $number('problem') ?></b> / The problem</p>
                            <h2>Why this was built</h2>
                            <?php foreach ($paragraphs($project['problem']) as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
                        </section>
                    <?php endif; ?>

                    <?php if (isset($sections['goal'])): ?>
                        <section class="cs-sec" id="goal">
                            <p class="label"><b style="color:var(--amber);font-weight:500"><?= $number('goal') ?></b> / Goal</p>
                            <h2>What it set out to do</h2>
                            <?php foreach ($paragraphs($project['goal']) as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
                        </section>
                    <?php endif; ?>

                    <?php if (isset($sections['architecture'])): ?>
                        <section class="cs-sec" id="architecture">
                            <p class="label"><b style="color:var(--amber);font-weight:500"><?= $number('architecture') ?></b> / Architecture</p>
                            <h2>How data moves</h2>
                            <p>Select a layer to see its role<?= $project['demo_request'] !== '' ? ', or run a request through the system' : '' ?>.</p>

                            <div class="cs-arch" style="margin-top:1.5rem">
                                <div class="hood-diagram panel">
                                    <?php if ($project['demo_request'] !== ''): ?>
                                        <?php [$method, $path] = array_pad(explode(' ', $project['demo_request'], 2), 2, ''); ?>
                                        <div class="hood-req"><span class="method"><?= e($method) ?></span><span class="path"><?= e($path) ?></span></div>
                                    <?php endif; ?>
                                    <div class="hood-body">
                                        <span class="packet" id="cs-packet" aria-hidden="true"></span>
                                        <ol class="arch" id="cs-arch" role="list" aria-label="Layers">
                                            <?php foreach ($project['architecture'] as $li => $layer): ?>
                                                <li class="arch-layer<?= $li === 0 ? ' is-on' : '' ?>">
                                                    <span class="arch-pin" aria-hidden="true"><?= $li + 1 ?></span>
                                                    <button class="arch-node" type="button" data-layer="<?= $li ?>" data-cursor="explore">
                                                        <strong><?= e($layer['layer']) ?></strong><span><?= e($layer['tech']) ?></span>
                                                        <?php if ($layer['role'] !== ''): ?><span class="visually-hidden"> — <?= e($layer['role']) ?></span><?php endif; ?>
                                                    </button>
                                                </li>
                                            <?php endforeach; ?>
                                        </ol>
                                    </div>
                                    <?php if ($project['demo_request'] !== ''): ?>
                                        <div class="hood-controls">
                                            <button class="btn btn-primary btn-sm" type="button" id="cs-run"><?= icon('play', 14) ?> Run request</button>
                                            <button class="btn btn-ghost btn-sm" type="button" id="cs-deny"><?= icon('lock', 14) ?> Without permission</button>
                                            <span class="demo-note" style="margin-left:auto">Illustrative timings</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="panel">
                                    <div class="hood-insp" id="cs-insp">
                                        <p class="label">Layer 01</p>
                                        <h3><?= e($project['architecture'][0]['layer']) ?></h3>
                                        <p><?= e($project['architecture'][0]['role']) ?></p>
                                    </div>
                                    <?php if ($project['demo_request'] !== ''): ?>
                                        <div class="hood-log" id="cs-log" aria-live="polite"><p class="empty">$ awaiting request</p></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </section>
                    <?php endif; ?>

                    <?php if (isset($sections['features'])): ?>
                        <section class="cs-sec" id="features">
                            <p class="label"><b style="color:var(--amber);font-weight:500"><?= $number('features') ?></b> / Key features</p>
                            <h2>What it does</h2>
                            <ol class="cs-list" role="list">
                                <?php foreach ($project['features'] as $feature): ?><li><?= e($feature) ?></li><?php endforeach; ?>
                            </ol>
                        </section>
                    <?php endif; ?>

                    <?php if (isset($sections['decisions'])): ?>
                        <section class="cs-sec" id="decisions">
                            <p class="label"><b style="color:var(--amber);font-weight:500"><?= $number('decisions') ?></b> / Engineering decisions</p>
                            <h2>Choices that mattered</h2>
                            <div class="cs-decisions">
                                <?php foreach ($paragraphs($project['decisions']) as $p): ?><div><?= e($p) ?></div><?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <?php if (isset($sections['security'])): ?>
                        <section class="cs-sec" id="security">
                            <p class="label"><b style="color:var(--amber);font-weight:500"><?= $number('security') ?></b> / Security &amp; validation</p>
                            <h2>Who may do what</h2>
                            <?php foreach ($paragraphs($project['security']) as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
                        </section>
                    <?php endif; ?>

                    <?php if (isset($sections['challenges'])): ?>
                        <section class="cs-sec" id="challenges">
                            <p class="label"><b style="color:var(--amber);font-weight:500"><?= $number('challenges') ?></b> / Challenges</p>
                            <h2>The hardest part</h2>
                            <?php foreach ($paragraphs($project['challenges']) as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
                        </section>
                    <?php endif; ?>

                    <?php if (isset($sections['screens'])): ?>
                        <section class="cs-sec" id="screens">
                            <p class="label"><b style="color:var(--amber);font-weight:500"><?= $number('screens') ?></b> / Screens</p>
                            <h2>What it looks like</h2>
                            <figure class="frame" style="--ry:0deg;--rx:0deg;max-width:none">
                                <div class="frame-bar" aria-hidden="true"><i></i><i></i><i></i><span><?= e(strtolower($project['title'])) ?></span></div>
                                <?= picture($image, 'Screenshot of ' . $project['title'], ['width' => 1280, 'height' => 800]) ?>
                            </figure>
                        </section>
                    <?php endif; ?>

                    <?php if (isset($sections['lessons'])): ?>
                        <section class="cs-sec" id="lessons">
                            <p class="label"><b style="color:var(--amber);font-weight:500"><?= $number('lessons') ?></b> / Outcome &amp; lessons</p>
                            <h2>What came out of it</h2>
                            <?php foreach ($paragraphs($project['outcome']) as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
                            <?php if ($project['learned'] !== ''): ?>
                                <p class="serif" style="font-size:1.45rem;line-height:1.4;color:var(--text);margin-top:1.4rem"><?= e($project['learned']) ?></p>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>

                    <?php if (isset($sections['future'])): ?>
                        <section class="cs-sec" id="future">
                            <p class="label"><b style="color:var(--amber);font-weight:500"><?= $number('future') ?></b> / Next</p>
                            <h2>What I would improve</h2>
                            <?php foreach ($paragraphs($project['future']) as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
                        </section>
                    <?php endif; ?>

                    <nav class="cs-pager" aria-label="More projects">
                        <a href="<?= e(project_url($prev['slug'])) ?>"><span class="label">← Previous</span><strong><?= e($prev['title']) ?></strong></a>
                        <a href="<?= e(project_url($next['slug'])) ?>"><span class="label">Next →</span><strong><?= e($next['title']) ?></strong></a>
                    </nav>
                </div>
            </div>
        </article>
    </main>

    <?php view('layout/footer', compact('isHome')); ?>
</body>
</html>
