<?php

/**
 * Skills, grouped by domain.
 *
 * No percentage bars: a self-assigned score is unverifiable and tells a
 * reviewer nothing. Grouping by domain and listing what is actually used in
 * the repositories below is the honest version of the same section.
 */

declare(strict_types=1);

$skills = Content::get('skills', []);

// The marquee is duplicated in the markup because the CSS animation translates
// the track by -50%; two identical halves make that loop seamless.
$marquee = [];
foreach ($skills as $group) {
    foreach ($group['items'] as $item) {
        $marquee[] = $item;
    }
}
$marquee = array_slice(array_values(array_unique($marquee)), 0, 22);

?>
<section class="section" id="skills">
    <div class="container">
        <header class="section-head" data-reveal>
            <p class="eyebrow"><?= icon('layers', 13) ?> Stack</p>
            <h2 class="section-title">Tools I actually <em>use</em>.</h2>
            <p class="section-lead">
                Grouped by what they are for, not scored out of a hundred.
                Everything here appears in a public repository or on my CV.
            </p>
        </header>

        <div class="skills-grid">
            <?php foreach ($skills as $group): ?>
                <article class="card card-glow skill-card" data-reveal>
                    <div class="skill-head">
                        <span class="skill-icon"><?= icon($group['icon'], 18) ?></span>
                        <h3>
                            <?= e($group['group']) ?>
                            <?php if (!empty($group['note'])): ?>
                                <span class="note"><?= e($group['note']) ?></span>
                            <?php endif; ?>
                        </h3>
                    </div>

                    <ul class="skill-items" role="list">
                        <?php foreach ($group['items'] as $item): ?>
                            <li><?= e($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($marquee !== []): ?>
            <div class="marquee" aria-hidden="true">
                <div class="marquee-track">
                    <?php for ($pass = 0; $pass < 2; $pass++): ?>
                        <?php foreach ($marquee as $item): ?>
                            <span><?= e($item) ?></span>
                        <?php endforeach; ?>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
