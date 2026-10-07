<?php

/**
 * 08 — Journey: how I got here.
 *
 * Growth rather than chronology. Each stage names what it taught, the tools,
 * and the projects that came out of it. The line draws as you scroll and each
 * stage lights when it is reached.
 */

declare(strict_types=1);

$journey = Content::get('journey', []);
$bySlug  = Knowledge::projectsBySlug();

if ($journey === []) {
    return;
}

?>
<section class="section journey" id="journey" data-section="journey" data-label="08 / Journey">
    <div class="container">
        <header class="sh">
            <span class="ghost-word" aria-hidden="true">ENGINEER</span>
            <p class="label"><b>08</b> / Journey</p>
            <h2 data-reveal="lines">
                <span class="ln" style="--i:0"><span>How I <span class="serif">got here.</span></span></span>
            </h2>
            <p class="lead" data-reveal>From C on a console to services with authentication, migrations and a deploy pipeline.</p>
        </header>

        <ol class="jr" id="journey-line" role="list">
            <?php foreach ($journey as $step): ?>
                <li class="jr-step">
                    <time><?= e($step['period']) ?></time>
                    <h3><?= e($step['title']) ?></h3>
                    <p><?= e($step['body']) ?></p>

                    <?php if ($step['tech'] !== []): ?>
                        <div class="tags">
                            <?php foreach ($step['tech'] as $tech): ?><span class="tag"><?= e($tech) ?></span><?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php $linked = array_values(array_filter($step['projects'], static fn(string $s): bool => isset($bySlug[$s]))); ?>
                    <?php if ($linked !== []): ?>
                        <p class="jr-proj">
                            <span>Built</span>
                            <?php foreach ($linked as $slug): ?>
                                <a class="ulink" href="<?= e(project_url($slug)) ?>"><?= e($bySlug[$slug]['title']) ?></a>
                            <?php endforeach; ?>
                        </p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
