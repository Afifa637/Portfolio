<?php

/**
 * 05 — Archive: every build, filterable, sortable and searchable.
 *
 * Cards are server-rendered and fully usable without JavaScript (each links to
 * its case study). JS adds instant filtering, sorting, the live count, and the
 * side inspector, which reads each build's architecture record.
 */

declare(strict_types=1);

$projects   = Content::get('projects', []);
$categories = Content::get('project_categories', []);
$identity   = Content::get('identity', []);

// Only offer filters for categories that are actually in use.
$used    = array_unique(array_column($projects, 'category'));
$filters = array_filter(
    $categories,
    static fn(string $k): bool => $k === 'all' || in_array($k, $used, true),
    ARRAY_FILTER_USE_KEY
);

?>
<section class="section archive" id="archive" data-section="archive" data-label="05 / Archive">
    <div class="container">
        <header class="sh">
            <p class="label"><b>05</b> / Archive</p>
            <h2 data-reveal="lines">
                <span class="ln" style="--i:0"><span>Every build,</span></span>
                <span class="ln" style="--i:1"><span><span class="serif">searchable.</span></span></span>
            </h2>
        </header>

        <div class="arc-bar" data-reveal>
            <div class="arc-filters" role="group" aria-label="Filter by category">
                <?php foreach ($filters as $key => $label): ?>
                    <button class="arc-filter" type="button" data-filter="<?= e($key) ?>"
                            aria-pressed="<?= $key === 'all' ? 'true' : 'false' ?>"><?= e($label) ?></button>
                <?php endforeach; ?>
            </div>

            <label class="arc-sort">
                <span class="visually-hidden">Sort builds</span>
                <select id="arc-sort">
                    <option value="order">Curated order</option>
                    <option value="newest">Newest first</option>
                    <option value="scope">Largest scope</option>
                    <option value="tech">By technology</option>
                    <option value="name">A – Z</option>
                </select>
            </label>

            <div class="arc-search">
                <?= icon('search', 15) ?>
                <label class="visually-hidden" for="arc-q">Search builds by name or technology</label>
                <input type="search" id="arc-q" placeholder="Search name or tech…" autocomplete="off" spellcheck="false">
            </div>
        </div>

        <p class="arc-count" id="arc-count" aria-live="polite">
            Showing <b><?= count($projects) ?></b> of <?= count($projects) ?> builds
        </p>

        <div class="arc-layout">
            <div class="arc-grid" id="arc-grid">
                <?php foreach ($projects as $index => $project):
                    $slug    = $project['slug'];
                    $image   = $project['image'] ?? '';
                    $hasShot = $image !== '' && is_file(APP_ROOT . '/' . $image);
                    $repoUrl = $project['repo'] !== '' ? 'https://github.com/' . $identity['github_user'] . '/' . $project['repo'] : '';
                    $lang    = GitHub::repo($project['repo'])['language'] ?? ($project['stack'][0] ?? '');
                    // "Scope" is a plain count — architecture layers, stack size and
                    // features — so the sort is explainable rather than a judgement.
                    $scope   = count($project['architecture']) * 2 + count($project['stack']) + count($project['features']);
                    $search  = strtolower(implode(' ', array_merge(
                        [$project['title'], $project['subtitle'], $project['summary'], $project['category']],
                        $project['stack']
                    )));
                ?>
                    <article class="arc-card spot" data-slug="<?= e($slug) ?>" data-category="<?= e($project['category']) ?>"
                             data-year="<?= e($project['year']) ?>" data-scope="<?= $scope ?>" data-order="<?= $index ?>"
                             data-tech="<?= e(strtolower((string) ($project['stack'][0] ?? ''))) ?>"
                             data-name="<?= e(strtolower($project['title'])) ?>" data-search="<?= e($search) ?>">
                        <div class="arc-media">
                            <span class="arc-cat"><?= e($categories[$project['category']] ?? $project['category']) ?></span>
                            <?php if ($hasShot): ?>
                                <?= picture($image, '', ['width' => 640, 'height' => 360]) ?>
                            <?php else: ?>
                                <div class="cover" style="--lang: <?= e(lang_color($lang)) ?>" aria-hidden="true">
                                    <b><?= e($project['title']) ?></b>
                                    <code><?= e(implode(' · ', array_slice($project['stack'], 0, 3))) ?></code>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="arc-body">
                            <p class="arc-meta"><span><?= e($project['year']) ?></span><span><?= e((string) $lang) ?></span></p>
                            <h3><a href="<?= e(project_url($slug)) ?>" data-cursor="view"><?= e($project['title']) ?></a></h3>
                            <p><?= e(str_excerpt($project['summary'], 120)) ?></p>

                            <div class="arc-detail">
                                <div>
                                    <div class="tags" style="padding-top:0.3rem">
                                        <?php foreach (array_slice($project['stack'], 0, 4) as $tech): ?>
                                            <span class="tag"><?= e($tech) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="arc-actions">
                                <a class="btn btn-sm btn-ghost" href="<?= e(project_url($slug)) ?>">
                                    Case study <?= icon('arrow-right', 13, 'i-shift') ?>
                                </a>
                                <?php if ($repoUrl !== ''): ?>
                                    <a class="btn btn-sm btn-quiet" href="<?= e($repoUrl) ?>" target="_blank" rel="noopener noreferrer"
                                       aria-label="<?= e($project['title']) ?> source on GitHub" data-cursor="external" data-track="github">
                                        <?= icon('github', 15) ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>

                <div class="arc-empty" id="arc-empty" hidden>
                    <strong>No build matches that.</strong>
                    Try another category, or search a technology like “Spring” or “Flutter”.
                </div>
            </div>

            <aside class="panel arc-insp" id="arc-insp" aria-live="polite">
                <div class="panel-head">
                    <span class="label">tech.inspector</span>
                    <span class="label t-3">hover a build</span>
                </div>
                <p class="arc-insp-empty">Hover or focus a build to inspect how it is put together.</p>
            </aside>
        </div>
    </div>
</section>
