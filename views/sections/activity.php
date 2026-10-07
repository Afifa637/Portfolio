<?php

/**
 * 07 — Activity: recently shipped.
 *
 * From the GitHub API cache. Only real values are shown: the month chart
 * counts the most recent push of each owned repository, and is labelled as
 * exactly that rather than passed off as a contribution or commit count.
 * Without GitHub data the section falls back to dated portfolio projects.
 */

declare(strict_types=1);

$gh       = GitHub::data();
$identity = Content::get('identity', []);
$projects = Content::get('projects', []);

$months = [];

if (!empty($gh['ok'])) {
    $start = new DateTimeImmutable('first day of this month');

    for ($i = 11; $i >= 0; $i--) {
        $months[$start->modify("-{$i} months")->format('Y-m')] = 0;
    }

    foreach ($gh['repos'] as $repo) {
        if (!empty($repo['is_fork']) || empty($repo['pushed_at'])) {
            continue;
        }

        $key = substr((string) $repo['pushed_at'], 0, 7);

        if (isset($months[$key])) {
            $months[$key]++;
        }
    }
}

$peak = max(1, ...array_values($months ?: [0]));

?>
<section class="section activity" id="activity" data-section="activity" data-label="07 / Activity">
    <div class="container">
        <header class="sh">
            <p class="label"><b>07</b> / Development activity</p>
            <h2 data-reveal="lines">
                <span class="ln" style="--i:0"><span>Recently <span class="serif">shipped.</span></span></span>
            </h2>
            <?php if (!empty($gh['ok'])): ?>
                <p class="lead" data-reveal>
                    Live from the GitHub API, cached on the server.
                    <?= !empty($gh['stale']) ? 'Showing the last successful sync.' : 'Synced ' . e(time_ago(date('c', (int) $gh['fetched_at']))) . '.' ?>
                </p>
            <?php endif; ?>
        </header>

        <div class="act-grid">
            <ol class="shipped" role="list" data-reveal>
                <?php if (!empty($gh['ok'])): ?>
                    <?php foreach ($gh['recent'] as $repo): ?>
                        <li>
                            <time datetime="<?= e($repo['pushed_at']) ?>"><?= e(date('M j, Y', strtotime($repo['pushed_at']))) ?></time>
                            <a href="<?= e($repo['url']) ?>" target="_blank" rel="noopener noreferrer" data-cursor="external" data-track="github">
                                <?= e($repo['name']) ?>
                            </a>
                            <?php if ($repo['language']): ?>
                                <span class="lang" style="--lc: <?= e(lang_color($repo['language'])) ?>"><?= e($repo['language']) ?></span>
                            <?php else: ?>
                                <span></span>
                            <?php endif; ?>
                            <?php if ($repo['description'] !== ''): ?>
                                <p><?= e(str_excerpt($repo['description'], 120)) ?></p>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php
                    $dated = $projects;
                    usort($dated, static fn(array $a, array $b): int => strcmp((string) $b['year'], (string) $a['year']));
                    ?>
                    <?php foreach (array_slice($dated, 0, 6) as $project): ?>
                        <li>
                            <time><?= e($project['year']) ?></time>
                            <a href="<?= e(project_url($project['slug'])) ?>"><?= e($project['title']) ?></a>
                            <span class="lang"><?= e($project['stack'][0] ?? '') ?></span>
                            <p><?= e(str_excerpt($project['summary'], 110)) ?></p>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ol>

            <?php if (!empty($gh['ok'])): ?>
                <div class="panel" data-reveal>
                    <div class="panel-head">
                        <span class="label">languages · public repos</span>
                        <span class="label t-3"><?= (int) $gh['stats']['repos'] ?> repos</span>
                    </div>

                    <div style="padding:1.2rem 1.1rem" class="lang-chart">
                        <?php
                        $langs = array_slice($gh['languages'], 0, 7);
                        $max   = max(1, ...array_column($langs, 'count'));
                        foreach ($langs as $i => $lang):
                        ?>
                            <div class="lang-row">
                                <span><?= e($lang['name']) ?></span>
                                <span class="lang-track">
                                    <span class="lang-fill" style="--lc: <?= e($lang['color']) ?>; --w: <?= round($lang['count'] / $max, 3) ?>; --delay: <?= $i * 60 ?>ms"></span>
                                </span>
                                <span><?= (int) $lang['count'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="padding:0 1.1rem 1.2rem">
                        <p class="label" style="margin-bottom:0.3rem">Last push per repository, by month</p>
                        <div class="pulse" role="img"
                             aria-label="Repositories most recently pushed in each of the last twelve months">
                            <?php $i = 0; foreach ($months as $month => $count): ?>
                                <span style="--v: <?= round($count / $peak, 3) ?>; --delay: <?= $i++ * 40 ?>ms"
                                      title="<?= e(date('M Y', strtotime($month . '-01'))) ?>: <?= (int) $count ?>"></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="pulse-axis" aria-hidden="true">
                            <span><?= e(date('M Y', strtotime(array_key_first($months) . '-01'))) ?></span>
                            <span><?= e(date('M Y', strtotime(array_key_last($months) . '-01'))) ?></span>
                        </div>
                    </div>

                    <div style="padding:0.9rem 1.1rem;border-top:1px solid var(--line)">
                        <a class="arrow-link" href="https://github.com/<?= e($identity['github_user']) ?>?tab=repositories"
                           target="_blank" rel="noopener noreferrer" data-cursor="external" data-track="github">
                            All repositories on GitHub <?= icon('arrow-right', 15) ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
