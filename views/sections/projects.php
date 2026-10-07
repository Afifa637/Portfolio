<?php

/**
 * Project showcase.
 *
 * Each card carries a curated summary from config/profile.php merged with live
 * GitHub metadata (language, stars, last push). Full case studies live in
 * hidden templates at the end of the section and are cloned into the modal on
 * demand, so the detail costs no extra request and remains crawlable.
 */

declare(strict_types=1);

$projects   = Content::get('projects', []);
$categories = Content::get('project_categories', []);
$identity   = Content::get('identity', []);

// Featured work first, then most recent.
usort($projects, static function (array $a, array $b): int {
    $byFeatured = (int) ($b['featured'] ?? false) <=> (int) ($a['featured'] ?? false);

    return $byFeatured !== 0
        ? $byFeatured
        : strcmp((string) ($b['year'] ?? ''), (string) ($a['year'] ?? ''));
});

// The widest cards carry the visual hierarchy. Beyond a couple, every card is
// wide and the emphasis stops meaning anything, so the two-column span is
// capped while the Featured badge stays on all of them.
$spanBudget = 2;

// Only offer a filter for categories that actually have projects.
$used = array_unique(array_column($projects, 'category'));
$categories = array_filter(
    $categories,
    static fn(string $key): bool => $key === 'all' || in_array($key, $used, true),
    ARRAY_FILTER_USE_KEY
);

?>
<section class="section" id="projects">
    <div class="container">
        <header class="section-head" data-reveal>
            <p class="eyebrow"><?= icon('code', 13) ?> Work</p>
            <h2 class="section-title">Things I have <em>built</em>.</h2>
            <p class="section-lead">
                <span id="projects-count"><?= count($projects) ?></span> projects, each with the problem it
                solved and what it taught me. Open one for the full case study.
            </p>
        </header>

        <div class="projects-bar" data-reveal>
            <div class="filters" role="group" aria-label="Filter projects by category">
                <?php foreach ($categories as $key => $label): ?>
                    <button class="filter" type="button"
                            data-filter="<?= e($key) ?>"
                            aria-pressed="<?= $key === 'all' ? 'true' : 'false' ?>">
                        <?= e($label) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="search">
                <?= icon('search', 16) ?>
                <label class="visually-hidden" for="project-search">Search projects</label>
                <input type="search" id="project-search" placeholder="Search by name or technology…"
                       autocomplete="off" spellcheck="false">
            </div>
        </div>

        <div class="projects-grid" id="projects-grid">
            <?php foreach ($projects as $project): ?>
                <?php
                $repo     = GitHub::repo((string) ($project['repo'] ?? ''));
                $slug     = $project['slug'];
                $stack    = $project['stack'] ?? [];
                $demo     = $project['demo'] ?? ($repo['homepage'] ?? '');
                $repoUrl  = $repo['url'] ?? ($project['repo'] ? 'https://github.com/' . $identity['github_user'] . '/' . $project['repo'] : '');
                $language = $repo['language'] ?? ($stack[0] ?? null);
                $image    = $project['image'] ?? '';

                // Everything the client-side search should match on.
                $haystack = strtolower(implode(' ', array_merge(
                    [$project['title'], $project['subtitle'], $project['summary'], $project['category']],
                    $stack
                )));
                ?>
                <?php $wide = !empty($project['featured']) && $spanBudget-- > 0; ?>
                <article class="card card-glow tilt project<?= $wide ? ' is-featured' : '' ?>"
                         data-category="<?= e($project['category']) ?>"
                         data-search="<?= e($haystack) ?>"
                         data-reveal>

                    <?php $hasCover = $image !== '' && is_file(APP_ROOT . '/' . $image); ?>
                    <div class="project-media<?= $hasCover ? '' : ' no-cover' ?>"
                         style="--lang: <?= e(lang_color($language)) ?>">
                        <?php if ($hasCover): ?>
                            <?= picture($image, 'Screenshot of ' . $project['title'], [
                                'width' => 640, 'height' => 360,
                            ]) ?>
                        <?php else: ?>
                            <div class="project-media-fallback" aria-hidden="true">
                                <span class="glyph"><?= e($project['title']) ?></span>
                                <span class="glyph-sub"><?= e(implode(' · ', array_slice($stack, 0, 3))) ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="project-flags">
                            <?php if (!empty($project['featured'])): ?>
                                <span class="badge badge-accent"><?= icon('star', 11) ?> Featured</span>
                            <?php endif; ?>
                            <?php if ($demo): ?>
                                <span class="badge"><span class="dot" aria-hidden="true"></span> Live</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="project-body">
                        <div class="project-meta">
                            <?php if ($language): ?>
                                <span class="lang" style="--lang: <?= e(lang_color($language)) ?>"><?= e($language) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($project['year'])): ?>
                                <span><?= e($project['year']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($repo['stars'])): ?>
                                <span><?= icon('star', 11) ?> <?= e((string) $repo['stars']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($repo['pushed_at'])): ?>
                                <span>Updated <?= e(time_ago($repo['pushed_at'])) ?></span>
                            <?php endif; ?>
                        </div>

                        <div>
                            <h3 class="project-title"><?= e($project['title']) ?></h3>
                            <?php if (!empty($project['subtitle'])): ?>
                                <p class="project-sub"><?= e($project['subtitle']) ?></p>
                            <?php endif; ?>
                        </div>

                        <p class="project-summary"><?= e($project['summary']) ?></p>

                        <?php if ($stack !== []): ?>
                            <div class="chip-row">
                                <?php foreach (array_slice($stack, 0, 5) as $tech): ?>
                                    <span class="badge"><?= e($tech) ?></span>
                                <?php endforeach; ?>
                                <?php if (count($stack) > 5): ?>
                                    <span class="badge">+<?= count($stack) - 5 ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="project-actions">
                            <button class="btn btn-sm btn-primary" type="button" data-case="<?= e($slug) ?>">
                                Case study <?= icon('arrow-right', 15) ?>
                            </button>
                            <?php if ($repoUrl): ?>
                                <a class="btn btn-sm btn-ghost" href="<?= e($repoUrl) ?>"
                                   target="_blank" rel="noopener noreferrer">
                                    <?= icon('github', 15) ?> Code
                                </a>
                            <?php endif; ?>
                            <?php if ($demo): ?>
                                <a class="btn btn-sm btn-ghost" href="<?= e($demo) ?>"
                                   target="_blank" rel="noopener noreferrer">
                                    <?= icon('external', 15) ?> Live
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>

            <p class="projects-empty" id="projects-empty" hidden>
                No projects match that filter. Try a different category or clear the search.
            </p>
        </div>
    </div>
</section>

<?php
/* ------------------------------------------------------------------------
   Case-study templates.
   Hidden from view and from assistive technology, but present in the HTML so
   the content is indexable and the modal opens with zero latency.
   ------------------------------------------------------------------------ */
?>
<div hidden>
    <?php foreach ($projects as $project): ?>
        <?php
        $repo    = GitHub::repo((string) ($project['repo'] ?? ''));
        $demo    = $project['demo'] ?? ($repo['homepage'] ?? '');
        $repoUrl = $repo['url'] ?? ($project['repo'] ? 'https://github.com/' . $identity['github_user'] . '/' . $project['repo'] : '');
        $image   = $project['image'] ?? '';
        ?>
        <div id="case-<?= e($project['slug']) ?>">
            <?php if ($image !== '' && is_file(APP_ROOT . '/' . $image)): ?>
                <div class="modal-hero">
                    <?= picture($image, 'Screenshot of ' . $project['title']) ?>
                </div>
            <?php endif; ?>

            <div class="modal-body">
                <p class="eyebrow"><?= icon('layers', 13) ?> <?= e($categories[$project['category']] ?? 'Project') ?></p>

                <h2><?= e($project['title']) ?></h2>
                <?php if (!empty($project['subtitle'])): ?>
                    <p class="modal-sub"><?= e($project['subtitle']) ?></p>
                <?php endif; ?>

                <p class="lead"><?= e($project['summary']) ?></p>

                <dl class="cs-facts">
                    <?php if (!empty($project['year'])): ?>
                        <div class="cs-fact"><dt>Year</dt><dd><?= e($project['year']) ?></dd></div>
                    <?php endif; ?>
                    <?php if (!empty($project['role'])): ?>
                        <div class="cs-fact"><dt>Role</dt><dd><?= e($project['role']) ?></dd></div>
                    <?php endif; ?>
                    <?php if (!empty($repo['language'])): ?>
                        <div class="cs-fact"><dt>Primary language</dt><dd><?= e($repo['language']) ?></dd></div>
                    <?php endif; ?>
                    <?php if (!empty($repo['pushed_at'])): ?>
                        <div class="cs-fact"><dt>Last commit</dt><dd><?= e(time_ago($repo['pushed_at'])) ?></dd></div>
                    <?php endif; ?>
                </dl>

                <?php if (!empty($project['problem'])): ?>
                    <div class="cs-section">
                        <h3>The problem</h3>
                        <p><?= e($project['problem']) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($project['features'])): ?>
                    <div class="cs-section">
                        <h3>What it does</h3>
                        <ul class="cs-list" role="list">
                            <?php foreach ($project['features'] as $feature): ?>
                                <li><?= icon('check', 15) ?> <span><?= e($feature) ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($project['challenges'])): ?>
                    <div class="cs-section">
                        <h3>Hardest part</h3>
                        <p><?= e($project['challenges']) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($project['outcome'])): ?>
                    <div class="cs-section">
                        <h3>Outcome</h3>
                        <p><?= e($project['outcome']) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($project['learned'])): ?>
                    <div class="cs-section">
                        <h3>What I took from it</h3>
                        <p><?= e($project['learned']) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($project['stack'])): ?>
                    <div class="cs-section">
                        <h3>Stack</h3>
                        <div class="chip-row">
                            <?php foreach ($project['stack'] as $tech): ?>
                                <span class="badge"><?= e($tech) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="modal-actions">
                    <?php if ($repoUrl): ?>
                        <a class="btn btn-primary" href="<?= e($repoUrl) ?>" target="_blank" rel="noopener noreferrer">
                            <?= icon('github', 17) ?> View source
                        </a>
                    <?php endif; ?>
                    <?php if ($demo): ?>
                        <a class="btn btn-ghost" href="<?= e($demo) ?>" target="_blank" rel="noopener noreferrer">
                            <?= icon('external', 17) ?> Live demo
                        </a>
                    <?php endif; ?>
                    <a class="btn btn-ghost" href="#contact" data-modal-close>
                        <?= icon('send', 17) ?> Ask me about it
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="modal" id="case-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Project case study">
    <div class="modal-panel" id="case-panel">
        <button class="modal-close" type="button" aria-label="Close case study"><?= icon('x', 18) ?></button>
    </div>
</div>
