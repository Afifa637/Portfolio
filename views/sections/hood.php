<?php

/**
 * 04 — Under the hood, and Build Mode.
 *
 * Under the hood: pick a system, follow one request through its layers, and
 * watch an unauthorised call stop at the security layer. Layers come from the
 * project's own architecture record; the timings are illustrative and labelled
 * as such everywhere they appear.
 *
 * Build Mode: choose a stack and get its architecture, plus the closest thing
 * actually built. Every option is a technology used in a real project.
 */

declare(strict_types=1);

$projects  = Content::get('projects', []);
$systems   = array_values(array_filter(
    $projects,
    static fn(array $p): bool => $p['demo_request'] !== '' && count($p['architecture']) >= 3
));

$first = $systems[0] ?? null;

$build = [
    'client'   => ['label' => 'Client',         'options' => ['React', 'Flutter', 'Android', 'Thymeleaf', 'Blade']],
    'backend'  => ['label' => 'Backend',        'options' => ['Spring Boot', 'Laravel', 'Node.js', 'Firebase']],
    'auth'     => ['label' => 'Authentication', 'options' => ['JWT', 'Spring Security', 'Laravel middleware', 'Firebase Auth']],
    'database' => ['label' => 'Database',       'options' => ['PostgreSQL', 'MySQL', 'Cloud Firestore']],
];

$defaults = ['client' => 'Thymeleaf', 'backend' => 'Spring Boot', 'auth' => 'JWT', 'database' => 'PostgreSQL'];

/*
 * Closest real build for the default choice, computed the same way the
 * client does it: count how many chosen technologies a project actually used.
 */
$techIndex  = Knowledge::techIndex();
$bySlug     = Knowledge::projectsBySlug();
$scores     = [];
$matchedBy  = [];

foreach ($defaults as $choice) {
    foreach ([Knowledge::key($choice), Knowledge::key(strtok($choice, ' '))] as $k) {
        foreach ($techIndex[$k] ?? [] as $slug) {
            $matchedBy[$slug][$choice] = true;
        }
    }
}

foreach ($matchedBy as $slug => $choices) {
    $scores[$slug] = count($choices);
}

arsort($scores);
$bestSlug = array_key_first($scores);

$defaultLayers = [
    ['Client', $defaults['client']],
    ['REST API', 'HTTP · JSON'],
    ['Backend', $defaults['backend']],
    ['Authentication', $defaults['auth']],
    ['Database', $defaults['database']],
];

?>
<section class="section hood" id="hood" data-section="hood" data-label="04 / Under the hood">
    <div class="container">
        <header class="sh">
            <span class="ghost-word" aria-hidden="true">SHIP</span>
            <p class="label"><b>04</b> / Under the hood</p>
            <h2 data-reveal="lines">
                <span class="ln" style="--i:0"><span>Follow a request</span></span>
                <span class="ln" style="--i:1"><span><span class="serif">through the system.</span></span></span>
            </h2>
            <p class="lead" data-reveal>
                Pick a build and run a request through its layers. Then run it again without
                permission and watch where it is refused.
            </p>
        </header>

        <?php if ($first): ?>
            <div class="hood-tabs" role="tablist" aria-label="Systems" data-reveal>
                <?php foreach ($systems as $i => $system): ?>
                    <button class="hood-tab" type="button" role="tab" data-system="<?= e($system['slug']) ?>"
                            aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" aria-controls="hood-stage">
                        <?= e($system['title']) ?> <code><?= e(strtok($system['demo_request'], ' ')) ?></code>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="hood-stage" id="hood-stage" role="tabpanel" data-reveal>
                <div class="hood-diagram">
                    <div class="hood-req" id="hood-req">
                        <?php [$method, $path] = array_pad(explode(' ', $first['demo_request'], 2), 2, ''); ?>
                        <span class="method"><?= e($method) ?></span>
                        <span class="path"><?= e($path) ?></span>
                        <span class="hint"><?= e($first['title']) ?></span>
                    </div>

                    <div class="hood-body">
                        <span class="packet" id="hood-packet" aria-hidden="true"></span>
                        <ol class="arch" id="hood-arch" role="list" aria-label="Layers">
                            <?php foreach ($first['architecture'] as $li => $layer): ?>
                                <li class="arch-layer<?= $li === 0 ? ' is-on' : '' ?>">
                                    <span class="arch-pin" aria-hidden="true"><?= $li + 1 ?></span>
                                    <button class="arch-node" type="button" data-layer="<?= $li ?>" data-cursor="explore">
                                        <strong><?= e($layer['layer']) ?></strong>
                                        <span><?= e($layer['tech']) ?></span>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>

                    <div class="hood-controls">
                        <button class="btn btn-primary btn-sm" type="button" id="hood-run">
                            <?= icon('play', 14) ?> Run request
                        </button>
                        <button class="btn btn-ghost btn-sm" type="button" id="hood-deny">
                            <?= icon('lock', 14) ?> Run without permission
                        </button>
                        <span class="demo-note" style="margin-left:auto">Illustrative timings</span>
                    </div>
                </div>

                <div class="hood-side">
                    <div class="hood-insp" id="hood-insp">
                        <p class="label">Layer 01</p>
                        <h3><?= e($first['architecture'][0]['layer']) ?></h3>
                        <p><?= e($first['architecture'][0]['role']) ?></p>
                        <div class="tags">
                            <?php foreach (array_map('trim', explode(',', $first['architecture'][0]['tech'])) as $tech): ?>
                                <span class="tag"><?= e($tech) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="hood-log" id="hood-log" aria-live="polite">
                        <p class="empty">$ awaiting request — press “Run request”.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ------------------------------------------------- Build Mode -->

        <div class="build" id="build">
            <div class="build-head" data-reveal>
                <p class="label"><?= icon('sparkle', 13) ?> Build mode</p>
                <h3>Assemble a stack.</h3>
                <p>
                    Choose a client, backend, auth and database. You get the architecture — and the
                    closest thing I have actually built with it.
                </p>

                <form class="build-form" id="build-form">
                    <?php foreach ($build as $key => $dimension): ?>
                        <fieldset class="choices">
                            <legend class="field-label"><?= e($dimension['label']) ?></legend>
                            <?php foreach ($dimension['options'] as $option): ?>
                                <label class="choice">
                                    <input type="radio" name="<?= e($key) ?>" value="<?= e($option) ?>"
                                        <?= $defaults[$key] === $option ? 'checked' : '' ?>>
                                    <span><?= e($option) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                    <?php endforeach; ?>
                </form>
            </div>

            <div class="panel build-out" id="build-out" aria-live="polite" data-reveal>
                <p class="label">generated.architecture</p>
                <ol class="arch" id="build-arch" role="list">
                    <?php foreach ($defaultLayers as $li => [$layer, $tech]): ?>
                        <li class="arch-layer">
                            <span class="arch-pin" aria-hidden="true"><?= $li + 1 ?></span>
                            <div class="arch-node"><strong><?= e($layer) ?></strong><span><?= e($tech) ?></span></div>
                        </li>
                    <?php endforeach; ?>
                </ol>
                <div class="build-match" id="build-match">
                    <p class="label">Closest build</p>
                    <p id="build-match-text">
                        <?php if ($bestSlug !== null && isset($bySlug[$bestSlug])): ?>
                            <a class="ulink" href="<?= e(project_url($bestSlug)) ?>"><?= e($bySlug[$bestSlug]['title']) ?></a>
                            — uses <?= e(implode(', ', array_keys($matchedBy[$bestSlug]))) ?>
                            (<?= (int) $scores[$bestSlug] ?> of <?= count($defaults) ?>).
                        <?php else: ?>
                            Nothing built with exactly this combination yet — which is a good reason to build it.
                        <?php endif; ?>
                    </p>
                </div>
                <p class="build-quote">This is the kind of stack I enjoy building.</p>
            </div>
        </div>
    </div>
</section>
