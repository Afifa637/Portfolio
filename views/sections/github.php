<?php

/**
 * Live GitHub activity.
 *
 * Rendered server-side from the cached API payload so there is no client-side
 * token, no loading spinner on first paint, and no layout shift. When GitHub is
 * unreachable and no cache exists, the whole section is skipped rather than
 * showing zeroes.
 */

declare(strict_types=1);

$gh       = GitHub::data();
$identity = Content::get('identity', []);

if (empty($gh['ok'])) {
    return;
}

$stats     = $gh['stats'];
$languages = $gh['languages'];
$recent    = $gh['recent'];

?>
<section class="section" id="github">
    <div class="container">
        <header class="section-head" data-reveal>
            <p class="eyebrow"><?= icon('activity', 13) ?> Activity</p>
            <h2 class="section-title">What I have been <em>committing</em>.</h2>
            <p class="section-lead">
                Pulled from the GitHub API and cached server-side. Forks and scratch repositories are filtered out.
            </p>
        </header>

        <div class="gh-grid">
            <div data-reveal>
                <dl class="gh-stats">
                    <div class="gh-stat">
                        <dt class="visually-hidden">Public repositories</dt>
                        <dd style="margin:0">
                            <span class="n" data-count="<?= e((string) $stats['repos']) ?>">0</span>
                            <span class="l">Public repositories</span>
                        </dd>
                    </div>
                    <div class="gh-stat">
                        <dt class="visually-hidden">Languages used</dt>
                        <dd style="margin:0">
                            <span class="n" data-count="<?= e((string) $stats['languages']) ?>">0</span>
                            <span class="l">Languages used</span>
                        </dd>
                    </div>
                    <div class="gh-stat">
                        <dt class="visually-hidden">Stars received</dt>
                        <dd style="margin:0">
                            <span class="n" data-count="<?= e((string) $stats['stars']) ?>">0</span>
                            <span class="l">Stars received</span>
                        </dd>
                    </div>
                    <div class="gh-stat">
                        <dt class="visually-hidden">On GitHub since</dt>
                        <dd style="margin:0">
                            <span class="n"><?= e($stats['since']) ?></span>
                            <span class="l">On GitHub since</span>
                        </dd>
                    </div>
                </dl>

                <?php if ($languages !== []): ?>
                    <h3 style="font-size:var(--fs-md);margin-bottom:var(--sp-3)">Language distribution</h3>

                    <div class="lang-bar" role="img"
                         aria-label="Language distribution across public repositories">
                        <?php foreach ($languages as $lang): ?>
                            <span style="flex: <?= e((string) $lang['count']) ?>; background: <?= e($lang['color']) ?>"></span>
                        <?php endforeach; ?>
                    </div>

                    <ul class="lang-legend" role="list">
                        <?php foreach (array_slice($languages, 0, 8) as $lang): ?>
                            <li>
                                <span class="swatch" style="background: <?= e($lang['color']) ?>" aria-hidden="true"></span>
                                <?= e($lang['name']) ?>
                                <span class="pct"><?= e((string) $lang['percent']) ?>%</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <p class="gh-note">
                    <?php if (!empty($gh['stale'])): ?>
                        <?= icon('activity', 14) ?>
                        Showing cached data — GitHub was unreachable at last refresh.
                    <?php else: ?>
                        <?= icon('check', 14) ?>
                        Synced <?= e(time_ago(date('c', $gh['fetched_at']))) ?>.
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($recent !== []): ?>
                <div data-reveal>
                    <h3 style="font-size:var(--fs-md);margin-bottom:var(--sp-3)">Recently pushed</h3>

                    <div class="repo-list">
                        <?php foreach ($recent as $repo): ?>
                            <a class="repo" href="<?= e($repo['url']) ?>" target="_blank" rel="noopener noreferrer">
                                <div class="repo-top">
                                    <span class="repo-name">
                                        <?= icon('github', 15) ?>
                                        <span><?= e($repo['name']) ?></span>
                                    </span>
                                    <?= icon('arrow-up-right', 15) ?>
                                </div>

                                <?php if ($repo['description'] !== ''): ?>
                                    <p class="repo-desc"><?= e($repo['description']) ?></p>
                                <?php endif; ?>

                                <div class="repo-meta">
                                    <?php if ($repo['language']): ?>
                                        <span>
                                            <span class="swatch" aria-hidden="true"
                                                  style="width:9px;height:9px;border-radius:50%;background:<?= e(lang_color($repo['language'])) ?>"></span>
                                            <?= e($repo['language']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($repo['stars'] > 0): ?>
                                        <span><?= icon('star', 12) ?> <?= e((string) $repo['stars']) ?></span>
                                    <?php endif; ?>
                                    <?php if ($repo['forks'] > 0): ?>
                                        <span><?= icon('git-fork', 12) ?> <?= e((string) $repo['forks']) ?></span>
                                    <?php endif; ?>
                                    <span><?= e(time_ago($repo['pushed_at'])) ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <div style="margin-top:var(--sp-4)">
                        <a class="link-arrow" href="https://github.com/<?= e($identity['github_user']) ?>?tab=repositories"
                           target="_blank" rel="noopener noreferrer">
                            All <?= e((string) ($gh['profile']['public_repos'] ?? $stats['repos'])) ?> repositories
                            <?= icon('arrow-right', 15) ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
