<?php

/**
 * 03 — Featured builds.
 *
 * The first four featured projects (in admin order) as pinned, near-full
 * viewport showcases that stack as you scroll. A backend service has nothing
 * to screenshot, so where there is no image the visual is the project's own
 * architecture — which is the more honest picture of that work anyway.
 */

declare(strict_types=1);

$projects   = Content::get('projects', []);
$categories = Content::get('project_categories', []);
$identity   = Content::get('identity', []);
$featured   = array_slice(array_values(array_filter($projects, static fn(array $p): bool => !empty($p['featured']))), 0, 4);

if ($featured === []) {
    return;
}

?>
<section class="section featured" id="work" data-section="work" data-label="03 / Work">
    <div class="container">
        <header class="sh">
            <span class="ghost-word" aria-hidden="true">BUILD</span>
            <p class="label"><b>03</b> / Featured builds</p>
            <h2 data-reveal="lines">
                <span class="ln" style="--i:0"><span>Work that had to</span></span>
                <span class="ln" style="--i:1"><span><span class="serif">hold together.</span></span></span>
            </h2>
            <p class="lead" data-reveal>
                Four builds, each with the problem it solved, the decision that mattered, and the
                architecture underneath. Every one opens into a full case study.
            </p>
        </header>

        <div class="feat-list">
            <?php foreach ($featured as $i => $project):
                $slug    = $project['slug'];
                $repoUrl = $project['repo'] !== '' ? 'https://github.com/' . $identity['github_user'] . '/' . $project['repo'] : '';
                $status  = $project['demo'] !== '' ? 'Deployed' : ($repoUrl !== '' ? 'Open source' : '—');
                $image   = $project['image'] ?? '';
                $hasShot = $image !== '' && is_file(APP_ROOT . '/' . $image);
            ?>
                <article class="feat" style="--stack: <?= $i ?>" aria-labelledby="feat-<?= e($slug) ?>">
                    <div class="feat-info">
                        <p class="feat-num">
                            <span><?= e($categories[$project['category']] ?? 'Project') ?> · <?= e($project['year']) ?></span>
                            <b aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></b>
                        </p>

                        <div>
                            <h3 id="feat-<?= e($slug) ?>" style="view-transition-name: title-<?= e($slug) ?>">
                                <?= e($project['title']) ?>
                            </h3>
                            <?php if ($project['subtitle'] !== ''): ?>
                                <p class="label" style="margin-top:0.9rem;color:var(--amber)"><?= e($project['subtitle']) ?></p>
                            <?php endif; ?>
                        </div>

                        <p class="feat-impact"><?= e($project['summary']) ?></p>

                        <dl class="kv feat-meta">
                            <?php if ($project['role'] !== ''): ?>
                                <div><dt>Role</dt><dd><?= e($project['role']) ?></dd></div>
                            <?php endif; ?>
                            <div><dt>Status</dt><dd><?= e($status) ?></dd></div>
                            <div>
                                <dt>Stack</dt>
                                <dd class="tags">
                                    <?php foreach (array_slice($project['stack'], 0, 6) as $tech): ?>
                                        <span class="tag"><?= e($tech) ?></span>
                                    <?php endforeach; ?>
                                </dd>
                            </div>
                        </dl>

                        <div class="hero-actions">
                            <a class="btn btn-primary" href="<?= e(project_url($slug)) ?>" data-cursor="view">
                                Read the case study <?= icon('arrow-right', 16, 'i-shift') ?>
                            </a>
                            <?php if ($repoUrl !== ''): ?>
                                <a class="btn btn-ghost" href="<?= e($repoUrl) ?>" target="_blank" rel="noopener noreferrer"
                                   data-cursor="external" data-track="github">
                                    <?= icon('github', 16) ?> Source
                                </a>
                            <?php endif; ?>
                            <?php if ($project['demo'] !== ''): ?>
                                <a class="btn btn-ghost" href="<?= e($project['demo']) ?>" target="_blank" rel="noopener noreferrer"
                                   data-cursor="external">
                                    <?= icon('external', 16, 'i-up') ?> Live
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="feat-visual">
                        <?php if ($hasShot): ?>
                            <figure class="frame" style="view-transition-name: shot-<?= e($slug) ?>">
                                <div class="frame-bar" aria-hidden="true"><i></i><i></i><i></i><span><?= e(strtolower($project['title'])) ?></span></div>
                                <?= picture($image, 'Screenshot of ' . $project['title'], ['width' => 1280, 'height' => 800]) ?>
                            </figure>
                        <?php elseif ($project['architecture'] !== []): ?>
                            <div style="position:relative;z-index:1;width:100%;max-width:30rem">
                                <p class="label" style="margin-bottom:1rem">architecture · <?= count($project['architecture']) ?> layers</p>
                                <ol class="arch" role="list" aria-label="<?= e($project['title']) ?> architecture">
                                    <?php foreach ($project['architecture'] as $li => $layer): ?>
                                        <li class="arch-layer">
                                            <span class="arch-pin" aria-hidden="true"><?= $li + 1 ?></span>
                                            <div class="arch-node" tabindex="0">
                                                <strong><?= e($layer['layer']) ?></strong>
                                                <span><?= e($layer['tech']) ?></span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                        <?php else: ?>
                            <div class="cover" style="position:relative;inset:auto;width:100%;min-height:16rem;border-radius:var(--r-2)">
                                <b><?= e($project['title']) ?></b>
                                <code><?= e(implode(' · ', array_slice($project['stack'], 0, 3))) ?></code>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
