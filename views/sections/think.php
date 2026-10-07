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
                    <?php foreach ($principles as $i => $principle): ?>
                        <li class="principle" data-reveal>
                            <span class="num"><?= sprintf('%02d', $i + 1) ?></span>
                            <h3><?= e($principle['title']) ?></h3>
                            <p><?= e($principle['body']) ?></p>

                            <?php
                            $evidence = array_values(array_filter(
                                $principle['evidence'],
                                static fn(string $slug): bool => isset($bySlug[$slug])
                            ));
                            ?>
                            <?php if ($evidence !== []): ?>
                                <div class="tags" aria-label="Seen in">
                                    <?php foreach ($evidence as $slug): ?>
                                        <a class="tag" href="<?= e(project_url($slug)) ?>">
                                            <?= e($bySlug[$slug]['title']) ?>
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
                                        <div class="tags">
                                            <?php foreach ($stage['tech'] as $tech): ?>
                                                <span class="tag"><?= e($tech) ?></span>
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
