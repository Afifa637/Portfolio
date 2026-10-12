<?php

/**
 * 01 — How I think.
 *
 * Principles instead of a biography, each linked to the projects that
 * evidence it, beside an annotated request lifecycle that shows backend
 * knowledge rather than claiming it.
 */

declare(strict_types=1);

$identity   = Content::get('identity', []);
$about      = Content::get('about', []);
$principles = Content::get('principles', []);
$blueprint  = Content::get('blueprint', []);
$bySlug     = Knowledge::projectsBySlug();

?>
<section class="section think" id="about" data-section="about" data-label="01 / About">
    <div class="container">
        <div class="g12 think-grid">
            <div class="c-7 c-md-12">
                <header class="sh">
                    <span class="ghost-word" aria-hidden="true">SYSTEMS</span>
                    <p class="label"><b>01</b> / How I think</p>
                    <h2 data-reveal="lines">
                        <span class="ln" style="--i:0"><span>I build the parts</span></span>
                        <span class="ln" style="--i:1"><span>users <span class="serif">don’t always</span> see.</span></span>
                    </h2>
                </header>

                <div class="profile-strip" data-reveal>
                    <?= picture($identity['avatar'], 'Portrait of ' . $identity['name'], ['width' => 64, 'height' => 64]) ?>
                    <p><strong><?= e($identity['name']) ?></strong> — <?= e($about['lead'] ?? '') ?></p>
                </div>

                <ol class="principles" role="list">
                    <?php foreach ($principles as $i => $principle):
                        // An icon chosen from what the principle is about, so it
                        // still fits after the principles are edited or reordered.
                        // The title decides; the body is only a fallback.
                        $pick = static fn(string $topic): ?string => match (true) {
                            str_contains($topic, 'secur') || str_contains($topic, 'auth') => 'shield',
                            str_contains($topic, 'data') || str_contains($topic, 'schema') => 'database',
                            str_contains($topic, 'ship') || str_contains($topic, 'deploy') || str_contains($topic, 'measure') => 'zap',
                            str_contains($topic, 'system') || str_contains($topic, 'architect') => 'layers',
                            str_contains($topic, 'test') => 'check',
                            default => null,
                        };
                        $glyph = $pick(strtolower($principle['title'])) ?? $pick(strtolower($principle['body'])) ?? 'sparkle';
                        $evidence = array_values(array_filter(
                            $principle['evidence'],
                            static fn(string $slug): bool => isset($bySlug[$slug])
                        ));
                    ?>
                        <li class="principle spot" data-reveal style="--delay:<?= $i * 70 ?>ms">
                            <div class="principle-top">
                                <span class="principle-glyph" aria-hidden="true"><?= icon($glyph, 22) ?></span>
                                <span class="num"><?= sprintf('%02d', $i + 1) ?></span>
                            </div>
                            <h3><?= e($principle['title']) ?></h3>
                            <p><?= e($principle['body']) ?></p>

                            <?php if ($evidence !== []): ?>
                                <div class="principle-proof" aria-label="Seen in">
                                    <?php foreach ($evidence as $slug): ?>
                                        <a class="pchip" href="<?= e(project_url($slug)) ?>">
                                            <?= project_thumb($bySlug[$slug], 24) ?><?= e($bySlug[$slug]['title']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>

            <aside class="c-5 c-md-12">
                <div class="panel blueprint" id="blueprint" data-reveal>
                    <div class="panel-head">
                        <span class="label">request.lifecycle</span>
                        <span class="label t-3">hover a stage</span>
                    </div>

                    <ol class="bp-flow" role="list">
                        <?php foreach ($blueprint as $i => $stage): ?>
                            <li class="bp-stage<?= $i === 0 ? ' is-open' : '' ?>">
                                <button class="bp-btn" type="button" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>"
                                        aria-controls="bp-<?= $i ?>">
                                    <strong><?= e($stage['stage']) ?></strong>
                                    <span><?= sprintf('%02d', $i + 1) ?></span>
                                </button>
                                <div class="bp-body" id="bp-<?= $i ?>">
                                    <div>
                                        <p><?= e($stage['body']) ?></p>
                                        <div class="tchips">
                                            <?php foreach ($stage['tech'] as $tech): ?>
                                                <?= tech_mark($tech, 16, true) ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>

                    <p class="bp-legend">The path a request takes through the backends I build — client to database and back.</p>
                </div>
            </aside>
        </div>
    </div>
</section>
