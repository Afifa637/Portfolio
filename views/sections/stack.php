<?php

/**
 * 02 — Stack explorer.
 *
 * No percentages. Each technology shows how many projects actually use it,
 * and selecting one shows which projects and what it was used alongside — all
 * computed by Knowledge from project stacks. The inspector is server-rendered
 * for the default selection so the section is useful without JavaScript.
 */

declare(strict_types=1);

$payload = Knowledge::payload();
$skills  = $payload['skills'];
$bySlug  = Knowledge::projectsBySlug();

// Default selection: Spring Boot (the stack the portfolio leads with), or
// failing that whichever technology has the most evidence.
$default = null;
$best    = null;

foreach ($skills as $group) {
    foreach ($group['items'] as $item) {
        $candidate = ['item' => $item, 'group' => $group['group']];

        if ($item['key'] === 'spring boot') {
            $default = $candidate;
        }

        if ($best === null || count($item['projects']) > count($best['item']['projects'])) {
            $best = $candidate;
        }
    }
}

$default ??= $best;

$totalTech = array_sum(array_map(static fn(array $g): int => count($g['items']), $skills));

?>
<section class="section stack" id="stack" data-section="stack" data-label="02 / Stack">
    <div class="container">
        <header class="sh">
            <span class="ghost-word" aria-hidden="true">STACK</span>
            <p class="label"><b>02</b> / Stack explorer</p>
            <h2 data-reveal="lines">
                <span class="ln" style="--i:0"><span>A stack you can</span></span>
                <span class="ln" style="--i:1"><span><span class="serif">interrogate.</span></span></span>
            </h2>
            <p class="lead" data-reveal>
                No self-scored percentages. Pick any of the <?= $totalTech ?> technologies to see where
                I actually used it and what it was built alongside.
            </p>
        </header>

        <div class="stack-layout">
            <div class="stack-tree" id="stack-tree" data-reveal>
                <?php foreach ($skills as $group): ?>
                    <div class="stack-group">
                        <h3>
                            <?= icon($group['icon'], 15) ?>
                            <span><?= e($group['group']) ?>
                                <?php if ($group['note'] !== ''): ?><small><?= e($group['note']) ?></small><?php endif; ?>
                            </span>
                        </h3>

                        <ul class="skill-list" role="list">
                            <?php foreach ($group['items'] as $item): ?>
                                <?php $selected = $default && $item['key'] === $default['item']['key']; ?>
                                <li>
                                    <button class="skill" type="button" data-skill="<?= e($item['key']) ?>"
                                            data-count="<?= count($item['projects']) ?>"
                                            aria-pressed="<?= $selected ? 'true' : 'false' ?>" aria-controls="stack-insp">
                                        <?= e($item['name']) ?>
                                        <span class="c" aria-label="<?= count($item['projects']) ?> projects"><?= count($item['projects']) ?></span>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>

            <aside class="panel stack-insp" id="stack-insp" aria-live="polite" data-reveal>
                <div class="panel-head">
                    <span class="label">inspector</span>
                    <span class="label t-3" id="stack-insp-group"><?= e($default['group'] ?? '') ?></span>
                </div>

                <div class="insp-body" id="stack-insp-body">
                    <?php if ($default): $item = $default['item']; ?>
                        <h3 class="insp-title"><?= e($item['name']) ?></h3>

                        <p class="insp-used">
                            <b><?= count($item['projects']) ?></b>
                            <span>project<?= count($item['projects']) === 1 ? '' : 's' ?> use it</span>
                        </p>

                        <?php if ($item['projects'] !== []): ?>
                            <ul class="insp-projects" role="list">
                                <?php foreach ($item['projects'] as $slug): if (!isset($bySlug[$slug])) { continue; } ?>
                                    <li>
                                        <a href="<?= e(project_url($slug)) ?>">
                                            <?= e($bySlug[$slug]['title']) ?>
                                            <span><?= e($bySlug[$slug]['year'] ?? '') ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <?php if ($item['related'] !== []): ?>
                            <div>
                                <p class="label" style="margin-bottom:0.6rem">Used alongside</p>
                                <div class="tags">
                                    <?php foreach ($item['related'] as $related): ?>
                                        <span class="tag"><?= e($related) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
</section>
