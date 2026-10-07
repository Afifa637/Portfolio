<?php

/**
 * Resume: education, activities, and services in one screen, with the CV download.
 */

declare(strict_types=1);

$education  = Content::get('education', []);
$activities = Content::get('activities', []);
$services   = Content::get('services', []);
$experience = Content::get('experience', []);
$identity   = Content::get('identity', []);

?>
<section class="section" id="resume">
    <div class="container">
        <header class="section-head" data-reveal>
            <p class="eyebrow"><?= icon('book', 13) ?> Background</p>
            <h2 class="section-title">Where I have <em>studied</em> and what I do.</h2>
        </header>

        <div class="two-col">
            <div data-reveal>
                <h3 style="display:flex;align-items:center;gap:var(--sp-3);margin-bottom:var(--sp-5)">
                    <?= icon('graduation', 20, 'text-dim') ?> Education
                </h3>

                <div class="timeline">
                    <?php foreach ($education as $item): ?>
                        <article class="tl-item<?= !empty($item['current']) ? ' is-current' : '' ?>">
                            <p class="tl-period">
                                <?= icon('calendar', 12) ?>
                                <?= e($item['start']) ?> — <?= e($item['end']) ?>
                                <?php if (!empty($item['current'])): ?>
                                    <span class="badge badge-accent" style="margin-left:4px">Current</span>
                                <?php endif; ?>
                            </p>

                            <h4 class="tl-title"><?= e($item['degree']) ?></h4>
                            <p class="tl-org"><?= e($item['institution']) ?></p>

                            <?php if (!empty($item['detail'])): ?>
                                <p class="tl-detail"><?= e($item['detail']) ?></p>
                            <?php endif; ?>

                            <div class="chip-row tl-tags">
                                <?php if (!empty($item['grade'])): ?>
                                    <span class="badge badge-accent"><?= icon('star', 11) ?> <?= e($item['grade']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($item['grade_note'])): ?>
                                    <span class="badge"><?= e($item['grade_note']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($item['location'])): ?>
                                    <span class="badge"><?= icon('map-pin', 11) ?> <?= e($item['location']) ?></span>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <div data-reveal>
                <h3 style="display:flex;align-items:center;gap:var(--sp-3);margin-bottom:var(--sp-5)">
                    <?= icon('award', 20, 'text-dim') ?> Activities &amp; involvement
                </h3>

                <div class="timeline">
                    <?php foreach ($activities as $item): ?>
                        <article class="tl-item">
                            <p class="tl-period"><?= icon($item['icon'], 12) ?> <?= e($item['role']) ?></p>
                            <h4 class="tl-title" style="font-size:var(--fs-md)"><?= e($item['org']) ?></h4>
                            <p class="tl-detail"><?= e($item['detail']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($experience !== []): ?>
                    <h3 style="display:flex;align-items:center;gap:var(--sp-3);margin:var(--sp-8) 0 var(--sp-5)">
                        <?= icon('briefcase', 20, 'text-dim') ?> Experience
                    </h3>

                    <div class="timeline">
                        <?php foreach ($experience as $item): ?>
                            <article class="tl-item<?= !empty($item['current']) ? ' is-current' : '' ?>">
                                <p class="tl-period">
                                    <?= icon('calendar', 12) ?>
                                    <?= e($item['start']) ?> — <?= e($item['end']) ?>
                                </p>
                                <h4 class="tl-title"><?= e($item['title']) ?></h4>
                                <p class="tl-org"><?= e($item['org']) ?></p>
                                <?php if (!empty($item['body'])): ?>
                                    <p class="tl-detail"><?= e($item['body']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($item['stack'])): ?>
                                    <div class="chip-row tl-tags">
                                        <?php foreach ($item['stack'] as $tech): ?>
                                            <span class="badge"><?= e($tech) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="card" style="margin-top:var(--sp-6)">
                    <h4 style="margin-bottom:var(--sp-2)">Full CV</h4>
                    <p class="text-dim" style="font-size:var(--fs-base);margin-bottom:var(--sp-4)">
                        Education, skills, activities, and contact details in one PDF.
                    </p>
                    <a class="btn btn-primary" href="<?= e($identity['resume']) ?>">
                        <?= icon('download', 17) ?> Download CV (PDF)
                    </a>
                </div>
            </div>
        </div>

        <?php if ($services !== []): ?>
            <div style="margin-top:var(--section-y)">
                <header class="section-head" data-reveal>
                    <p class="eyebrow"><?= icon('zap', 13) ?> Services</p>
                    <h2 class="section-title">What I can <em>build</em> for you.</h2>
                </header>

                <div class="services-grid">
                    <?php foreach ($services as $i => $service): ?>
                        <article class="card card-glow service-card" data-reveal>
                            <span class="service-num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                            <span class="skill-icon"><?= icon($service['icon'], 18) ?></span>
                            <h3><?= e($service['title']) ?></h3>
                            <p><?= e($service['body']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
