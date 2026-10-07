<?php

/**
 * Hero: who she is, what she builds, and the two actions that matter.
 */

declare(strict_types=1);

$identity = Content::get('identity', []);
$socials  = Content::get('socials', []);
$gh       = GitHub::data();

$projectCount = count(Content::get('projects', []));
$repoCount    = $gh['stats']['repos'] ?: $projectCount;
$langCount    = $gh['stats']['languages'] ?: count(Content::get('skills.0.items', []));
$sinceYear    = $gh['stats']['since'] ?: '2022';

?>
<section class="hero" id="top">
    <?php /* Decorative constellation field. Painted only while the hero is
             on screen and the tab is visible, and skipped entirely under
             prefers-reduced-motion. */ ?>
    <canvas class="hero-canvas" id="hero-canvas" aria-hidden="true"></canvas>

    <div class="container">
        <div class="hero-grid">

            <div class="hero-copy">
                <p class="hero-status" data-reveal>
                    <?php if (!empty($identity['available'])): ?>
                        <span class="dot dot-live" aria-hidden="true"></span>
                    <?php endif; ?>
                    <?= e($identity['availability']) ?>
                </p>

                <h1 class="hero-title" data-reveal>
                    <span class="line"><?= e($identity['first_name']) ?></span>
                    <span class="line accent-text"><?= e($identity['last_name']) ?></span>
                </h1>

                <p class="hero-role" data-reveal>
                    <span class="prompt" aria-hidden="true">&gt;</span>
                    <span>
                        <span class="typed" id="typed"
                              data-roles="<?= e(json_encode(array_values($identity['roles']), JSON_UNESCAPED_UNICODE)) ?>"><?= e($identity['roles'][0] ?? '') ?></span><span class="caret" aria-hidden="true"></span>
                    </span>
                </p>

                <p class="hero-pitch" data-reveal><?= e($identity['pitch']) ?></p>

                <div class="hero-actions" data-reveal>
                    <a class="btn btn-primary" href="#projects" data-magnetic>
                        <?= icon('layers', 17) ?> View my work
                    </a>
                    <a class="btn btn-ghost" href="<?= e($identity['resume']) ?>">
                        <?= icon('download', 17) ?> Download CV
                    </a>
                </div>

                <div class="hero-socials" data-reveal>
                    <?php foreach ($socials as $social): ?>
                        <a class="social-link" href="<?= e($social['url']) ?>"
                           aria-label="<?= e($social['label']) ?>"
                           <?= str_starts_with($social['url'], 'mailto:') ? '' : 'target="_blank" rel="noopener noreferrer"' ?>>
                            <?= icon($social['icon'], 18) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="hero-panel tilt" data-reveal>
                <div class="panel-bar">
                    <span class="panel-dots" aria-hidden="true"><span></span><span></span><span></span></span>
                    <span class="panel-name">afifa.profile.json</span>
                </div>

                <div class="panel-body" aria-hidden="true">
<?php
/* A static, escaped code illustration. Rendered as decorative — the same
   information is available as real text elsewhere on the page. */
$lines = [
    ['{'],
    ['  ', 'prop:name', ': ', 'str:"' . $identity['name'] . '"', ','],
    ['  ', 'prop:role', ': ', 'str:"' . $identity['title'] . '"', ','],
    ['  ', 'prop:based', ': ', 'str:"' . $identity['location'] . '"', ','],
    ['  ', 'prop:focus', ': [', 'str:"backend"', ', ', 'str:"full-stack"', '],'],
    ['  ', 'prop:repos', ': ', 'num:' . $repoCount, ','],
    ['  ', 'prop:languages', ': ', 'num:' . $langCount, ','],
    ['  ', 'prop:open_to_work', ': ', 'key:' . ($identity['available'] ? 'true' : 'false')],
    ['}'],
];

foreach ($lines as $i => $parts) {
    echo '<span class="code-line"><span class="ln">' . ($i + 1) . '</span><span>';
    foreach ($parts as $part) {
        if (str_contains($part, ':') && preg_match('/^(prop|str|num|key):(.*)$/s', $part, $m)) {
            echo '<span class="tok-' . $m[1] . '">' . e($m[2]) . '</span>';
        } else {
            echo '<span class="tok-punc">' . e($part) . '</span>';
        }
    }
    echo '</span></span>';
}
?>
                </div>

                <div class="hero-avatar">
                    <?= picture($identity['avatar'], $identity['name'], [
                        'width' => 54, 'height' => 54,
                        'fetchpriority' => 'high', 'loading' => 'eager',
                    ]) ?>
                    <span class="meta">
                        <strong><?= e($identity['name']) ?></strong>
                        <span><?= e($identity['subtitle']) ?></span>
                    </span>
                </div>
            </div>
        </div>

        <dl class="hero-metrics" data-reveal>
            <div class="metric">
                <dt>Public repositories</dt>
                <dd><span data-count="<?= e((string) $repoCount) ?>" data-count-suffix="">0</span></dd>
            </div>
            <div class="metric">
                <dt>Languages shipped</dt>
                <dd><span data-count="<?= e((string) $langCount) ?>">0</span></dd>
            </div>
            <div class="metric">
                <dt>Case studies</dt>
                <dd><span data-count="<?= e((string) $projectCount) ?>">0</span></dd>
            </div>
            <div class="metric">
                <dt>Building since</dt>
                <dd><?= e($sinceYear) ?></dd>
            </div>
        </dl>
    </div>

    <a class="scroll-cue" href="#about" aria-label="Scroll to About">
        <span class="bar" aria-hidden="true"></span>
        Scroll
    </a>
</section>
