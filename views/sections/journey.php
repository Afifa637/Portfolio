<?php

/**
 * 08 — Journey: how I got here.
 *
 * Growth rather than chronology. A track of stages — period, title and the
 * tools as logos — over a panel for the selected stage with what it taught and
 * pictures of the projects that came out of it. Without JavaScript every
 * panel is shown; with it, the track works as tabs. The track fills as you
 * scroll through the section.
 */

declare(strict_types=1);

$journey = Content::get('journey', []);
$bySlug  = Knowledge::projectsBySlug();

if ($journey === []) {
    return;
}

?>
<section class="section journey" id="journey" data-section="journey" data-label="09 / Journey">
    <div class="container">
        <header class="sh">
            <span class="ghost-word" aria-hidden="true">ENGINEER</span>
            <p class="label"><b>09</b> / Journey</p>
            <h2 data-reveal="lines">
                <span class="ln" style="--i:0"><span>How I <span class="serif">got here.</span></span></span>
            </h2>
            <p class="lead" data-reveal>From C on a console to services with authentication, migrations and a deploy pipeline.</p>
        </header>

        <div class="jr">
            <ol class="jr-track" id="journey-line" role="tablist" aria-label="Stages">
                <?php foreach ($journey as $i => $step): ?>
                    <li class="jr-stop" role="presentation">
                        <button class="jr-btn" type="button" role="tab" id="jr-tab-<?= $i ?>" aria-controls="jr-panel-<?= $i ?>"
                                aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" tabindex="<?= $i === 0 ? '0' : '-1' ?>">
                            <span class="jr-dot" aria-hidden="true"></span>
                            <time><?= e($step['period']) ?></time>
                            <strong><?= e($step['title']) ?></strong>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ol>

            <div class="jr-panels">
                <?php foreach ($journey as $i => $step):
                    $linked = array_values(array_filter($step['projects'], static fn(string $s): bool => isset($bySlug[$s])));
                ?>
                    <article class="jr-panel<?= $i === 0 ? ' is-on' : '' ?>" role="tabpanel" id="jr-panel-<?= $i ?>"
                             aria-labelledby="jr-tab-<?= $i ?>">
                        <div class="jr-text">
                            <p class="label"><b><?= sprintf('%02d', $i + 1) ?></b> / <?= e($step['period']) ?></p>
                            <h3><?= e($step['title']) ?></h3>
                            <p><?= e($step['body']) ?></p>
                            <?php if ($step['tech'] !== []): ?>
                                <div class="tchips">
                                    <?php foreach ($step['tech'] as $tech): ?><?= tech_tag($tech) ?><?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($linked !== []): ?>
                            <div class="jr-builds" aria-label="Built in this stage">
                                <?php foreach (array_slice($linked, 0, 4) as $slug): $proj = $bySlug[$slug];
                                    $shot = ltrim((string) ($proj['image'] ?? ''), '/');
                                ?>
                                    <a class="jr-build" href="<?= e(project_url($slug)) ?>" data-cursor="view">
                                        <span class="jr-build-media">
                                            <?php if ($shot !== '' && is_file(APP_ROOT . '/' . $shot)): ?>
                                                <?= picture($shot, '', ['width' => 480, 'height' => 300]) ?>
                                            <?php else: ?>
                                                <?= project_cover($proj) ?>
                                            <?php endif; ?>
                                        </span>
                                        <span class="jr-build-name"><?= e($proj['title']) ?> <?= icon('arrow-up-right', 14) ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif ($step['tech'] !== []): ?>
                            <div class="jr-art">
                                <?= project_cover(['stack' => $step['tech']]) ?>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
