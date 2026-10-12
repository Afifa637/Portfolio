<?php

/**
 * 03 — Résumé as a roadmap.
 *
 * Education, experience and activities laid out as stops along a winding road,
 * in the order they happened, ending at what I am looking for next. A marker
 * drives the road as you scroll; the route bar above jumps to any stop.
 *
 * Without JavaScript it is a plain vertical list on a straight line — every
 * stop and every word is still there. Everything is read from the CMS content
 * (education, experience, activities), so it grows as those are edited.
 */

declare(strict_types=1);

$identity   = Content::get('identity', []);
$education  = Content::get('education', []);
$activities = Content::get('activities', []);
$experience = Content::get('experience', []);

// Oldest first: a road is travelled in one direction.
usort($education, static fn(array $a, array $b): int => (int) $a['start'] <=> (int) $b['start']);

$stops = [];

foreach ($education as $item) {
    $now = !empty($item['current']) || in_array(strtolower((string) $item['end']), ['ongoing', 'present', 'now'], true);

    // "CGPA: 3.80 / 4.00" -> 3.80 of 4.00, drawn as a ring.
    $score = preg_match('/(\d+(?:\.\d+)?)\s*\/\s*(\d+(?:\.\d+)?)/', (string) $item['grade'], $g)
        ? ['value' => $g[1], 'max' => $g[2], 'ratio' => min(1, (float) $g[1] / max(0.01, (float) $g[2]))]
        : null;

    $extra = trim((string) preg_replace('/^.*?\d+(?:\.\d+)?\s*\/\s*\d+(?:\.\d+)?\s*/', '', (string) $item['grade']), " ()");

    $stops[] = [
        'kind'  => 'edu',
        'icon'  => 'graduation',
        'short' => preg_match('/\(([^)]+)\)/', (string) $item['degree'], $m) ? $m[1] : (string) $item['degree'],
        'when'  => $item['start'] . ' — ' . $item['end'],
        'now'   => $now,
        'title' => $item['degree'],
        'org'   => $item['institution'],
        'note'  => trim($item['grade_note'] . ($extra !== '' && !str_contains(strtolower($extra), 'gpa') ? ' · ' . $extra : ''), ' ·'),
        'body'  => (string) $item['detail'],
        'score' => $score,
        'grade' => (string) $item['grade'],
    ];
}

foreach ($experience as $item) {
    $stops[] = [
        'kind'  => 'work',
        'icon'  => 'briefcase',
        'short' => (string) $item['title'],
        'when'  => $item['start'] . ' — ' . $item['end'],
        'now'   => false,
        'title' => $item['title'],
        'org'   => $item['org'],
        'note'  => '',
        'body'  => (string) $item['body'],
        'score' => null,
        'grade' => '',
    ];
}

foreach ($activities as $item) {
    $about = strtolower($item['org'] . ' ' . $item['detail']);
    $plain = in_array($item['role'], ['Member', 'Participant'], true);

    $stops[] = [
        'kind'  => 'act',
        'icon'  => match (true) {
            str_contains($about, 'media') || str_contains($about, 'ieee')            => 'globe',
            str_contains($about, 'hackathon')                                       => 'zap',
            str_contains($about, 'programming') || str_contains($about, 'contest')  => 'code',
            str_contains($about, 'hardware') || str_contains($about, 'embedded')    => 'cpu',
            default                                                                 => 'award',
        },
        'short' => $plain ? trim(explode(' — ', (string) $item['org'])[0]) : (string) $item['role'],
        'when'  => (string) $item['role'],
        'now'   => false,
        'title' => $item['org'],
        'org'   => '',
        'note'  => '',
        'body'  => (string) $item['detail'],
        'score' => null,
        'grade' => '',
    ];
}

$destination = !empty($identity['available']) ? (string) ($identity['availability'] ?? '') : '';

?>
<section class="section career" id="resume" data-section="resume" data-label="03 / Roadmap">
    <div class="container">
        <header class="sh">
            <p class="label"><b>03</b> / Roadmap</p>
            <h2 data-reveal="lines">
                <span class="ln" style="--i:0"><span>The road <span class="serif">so far.</span></span></span>
            </h2>
            <div class="resume-actions" data-reveal>
                <a class="btn btn-primary" href="<?= e(url(ltrim((string) $identity['resume'], '/'))) ?>" data-track="resume">
                    <?= icon('download', 16) ?> Download CV
                </a>
                <a class="btn btn-ghost" href="<?= e(base_path() . '/resume.php') ?>">
                    <?= icon('file', 16) ?> Open résumé
                </a>
                <a class="btn btn-ghost" href="<?= e(base_path() . '/resume.php?print=1') ?>">
                    <?= icon('printer', 16) ?> Print
                </a>
            </div>
        </header>

        <div class="road" id="road">
            <nav class="route" aria-label="Roadmap stops" data-reveal>
                <button class="route-step" type="button" data-step="-1" aria-label="Previous stop"><?= icon('arrow-right', 15) ?></button>
                <ol class="route-list">
                    <?php foreach ($stops as $i => $stop): ?>
                        <li>
                            <button type="button" class="route-pin" data-goto="stop-<?= $i ?>"<?= $i === 0 ? ' aria-current="step"' : '' ?>>
                                <?= e($stop['short']) ?>
                            </button>
                        </li>
                    <?php endforeach; ?>
                    <?php if ($destination !== ''): ?>
                        <li><button type="button" class="route-pin route-end" data-goto="stop-end">Next</button></li>
                    <?php endif; ?>
                </ol>
                <button class="route-step" type="button" data-step="1" aria-label="Next stop"><?= icon('arrow-right', 15) ?></button>
            </nav>

            <div class="road-stage">
                <svg class="road-svg" id="road-svg" aria-hidden="true" focusable="false">
                    <path class="road-bed"/>
                    <path class="road-mid"/>
                    <path class="road-trip"/>
                    <g class="road-car">
                        <circle class="car-ring" r="15"/>
                        <circle class="car-dot" r="7"/>
                        <path class="car-arrow" d="M-3 3 L0 -4 L3 3 Z"/>
                    </g>
                </svg>

                <ol class="stops">
                    <?php foreach ($stops as $i => $stop): ?>
                        <li class="stop is-<?= e($stop['kind']) ?>" id="stop-<?= $i ?>">
                            <span class="stop-pin" aria-hidden="true"><?= icon($stop['icon'], 18) ?></span>

                            <article class="stop-card spot" tabindex="-1">
                                <?php if ($stop['score']): ?>
                                    <div class="gauge" style="--v:<?= round($stop['score']['ratio'], 4) ?>" role="img"
                                         aria-label="<?= e($stop['grade']) ?>">
                                        <svg viewBox="0 0 72 72" aria-hidden="true">
                                            <circle class="gauge-track" cx="36" cy="36" r="31"/>
                                            <circle class="gauge-fill" cx="36" cy="36" r="31" pathLength="100"/>
                                        </svg>
                                        <span><b><?= e($stop['score']['value']) ?></b><small>/ <?= e($stop['score']['max']) ?></small></span>
                                    </div>
                                <?php endif; ?>

                                <div class="stop-text">
                                    <p class="stop-when">
                                        <time><?= e($stop['when']) ?></time>
                                        <?php if ($stop['now']): ?><span class="cmap-now"><span class="live"></span> now</span><?php endif; ?>
                                    </p>
                                    <h3><?= e($stop['title']) ?></h3>
                                    <?php if ($stop['org'] !== ''): ?><p class="org"><?= e($stop['org']) ?></p><?php endif; ?>
                                    <?php if ($stop['note'] !== ''): ?><p class="stop-note"><?= e($stop['note']) ?></p><?php endif; ?>
                                    <?php if ($stop['body'] !== ''): ?><p class="stop-body"><?= e($stop['body']) ?></p><?php endif; ?>
                                </div>
                            </article>
                        </li>
                    <?php endforeach; ?>

                    <?php if ($destination !== ''): ?>
                        <li class="stop is-end" id="stop-end">
                            <span class="stop-pin" aria-hidden="true"><?= icon('map-pin', 18) ?></span>
                            <article class="stop-card stop-dest" tabindex="-1">
                                <div class="stop-text">
                                    <p class="stop-when"><span class="live" aria-hidden="true"></span> Next stop</p>
                                    <h3><?= e($destination) ?></h3>
                                    <a class="btn btn-primary btn-sm" href="<?= e(home_url('contact')) ?>" data-magnetic>
                                        Get in touch <?= icon('arrow-right', 14, 'i-shift') ?>
                                    </a>
                                </div>
                            </article>
                        </li>
                    <?php endif; ?>
                </ol>
            </div>
        </div>
    </div>
</section>
