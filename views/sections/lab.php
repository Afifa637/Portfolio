<?php

/**
 * 06 — Lab.
 *
 * Three experiments, chosen for depth over count, each tied to something the
 * portfolio's projects actually use: JWTs (Timeless, Student Management), SQL
 * over a real dataset (this site's own project table), and sorting (the
 * algorithms coursework underneath all of it).
 *
 * The code for each experiment is a separate module, loaded only when the
 * section comes near the viewport.
 */

declare(strict_types=1);

$experiments = [
    'jwt'  => ['JWT inspector',     'decode · explain claims'],
    'sql'  => ['SQL playground',    'query my projects table'],
    'sort' => ['Sorting, visible',  'four algorithms, step by step'],
];

?>
<section class="section lab" id="lab" data-section="lab" data-label="06 / Lab">
    <div class="container">
        <header class="sh">
            <span class="ghost-word" aria-hidden="true">CREATE</span>
            <p class="label"><b>06</b> / Lab</p>
            <h2 data-reveal="lines">
                <span class="ln" style="--i:0"><span>Things I build</span></span>
                <span class="ln" style="--i:1"><span><span class="serif">because I’m curious.</span></span></span>
            </h2>
            <p class="lead" data-reveal>
                Small, working instruments. Each one runs entirely in your browser — nothing you type
                leaves this page.
            </p>
        </header>

        <div class="lab-bench" id="lab-bench" data-reveal>
            <div class="lab-tabs" role="tablist" aria-label="Experiments">
                <?php $i = 0; foreach ($experiments as $key => [$name, $sub]): ?>
                    <button class="lab-tab" type="button" role="tab" id="lab-tab-<?= e($key) ?>"
                            data-lab="<?= e($key) ?>" aria-controls="lab-<?= e($key) ?>"
                            aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" tabindex="<?= $i === 0 ? '0' : '-1' ?>">
                        <b><?= e($name) ?></b><span><?= e($sub) ?></span>
                    </button>
                <?php $i++; endforeach; ?>
            </div>

            <?php $i = 0; foreach ($experiments as $key => [$name]): ?>
                <div class="lab-panel" id="lab-<?= e($key) ?>" role="tabpanel"
                     aria-labelledby="lab-tab-<?= e($key) ?>" <?= $i > 0 ? 'hidden' : '' ?>>
                    <div class="lab-loading">
                        <noscript>This experiment runs in the browser and needs JavaScript.</noscript>
                        <span class="js-only">Loading <?= e(strtolower($name)) ?>…</span>
                    </div>
                </div>
            <?php $i++; endforeach; ?>
        </div>
    </div>
</section>
