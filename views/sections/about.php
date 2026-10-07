<?php

declare(strict_types=1);

$about    = Content::get('about', []);
$identity = Content::get('identity', []);

?>
<section class="section" id="about">
    <div class="container">
        <header class="section-head" data-reveal>
            <p class="eyebrow"><?= icon('terminal', 13) ?> About</p>
            <h2 class="section-title">Engineering is the part I <em>enjoy</em>.</h2>
        </header>

        <div class="about-grid">
            <div class="about-body" data-reveal>
                <p><?= e($about['lead']) ?></p>
                <?php foreach ($about['body'] as $paragraph): ?>
                    <p><?= e($paragraph) ?></p>
                <?php endforeach; ?>

                <div class="hero-actions" style="margin-top: var(--sp-6)">
                    <a class="btn btn-ghost" href="<?= e($identity['resume']) ?>">
                        <?= icon('download', 17) ?> Download CV
                    </a>
                    <a class="btn btn-ghost" href="https://github.com/<?= e($identity['github_user']) ?>"
                       target="_blank" rel="noopener noreferrer">
                        <?= icon('github', 17) ?> GitHub profile
                    </a>
                </div>
            </div>

            <aside class="about-aside" data-reveal>
                <figure class="portrait">
                    <?= picture($identity['avatar'], 'Portrait of ' . $identity['name'], [
                        'width' => 480, 'height' => 600,
                    ]) ?>
                </figure>

                <dl class="fact-list">
                    <?php foreach ($about['facts'] as $fact): ?>
                        <div class="fact">
                            <dt><?= e($fact['label']) ?></dt>
                            <dd><?= e($fact['value']) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </aside>
        </div>
    </div>
</section>
