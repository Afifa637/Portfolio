<?php

/**
 * Hero and System Core.
 *
 * The graph is rendered here as SVG with a deterministic layout, so it is
 * complete without JavaScript. Every count and every edge comes from
 * Knowledge::coreGraph(), which derives them from project stacks — an edge
 * means two technologies were actually used in the same project.
 */

declare(strict_types=1);

$identity = Content::get('identity', []);
$socials  = Content::get('socials', []);
$graph    = Knowledge::coreGraph();
$metrics  = Knowledge::metrics();
$status   = Content::get('status', []);

/* ------------------------------------------------------------ layout ---- */

$groupOrder = ['backend' => 0, 'data' => 1, 'tooling' => 2, 'mobile' => 3, 'frontend' => 4, 'systems' => 5];

$nodes = $graph['nodes'];
usort($nodes, static fn(array $a, array $b): int =>
    ($groupOrder[$a['group']] ?? 9) <=> ($groupOrder[$b['group']] ?? 9));

$cx = 0.0;
$cy = 14.0;
$n  = max(1, count($nodes));
$positions = [];

foreach ($nodes as $i => $node) {
    $angle  = deg2rad(-90 + $i * (360 / $n));
    // Alternate radii so neighbouring labels do not collide.
    $radius = $i % 2 === 0 ? 182 : 236;

    $cos = cos($angle);
    $sin = sin($angle);

    $r = 5 + min($node['count'], 4) * 1.7 + ($node['repos'] ? 3 : 0);

    // Labels sit outside the node: above it at the top of the ring (with the
    // count line still clear of the dot), below it at the bottom, beside it
    // at the sides.
    $ly = match (true) {
        $sin < -0.3 => -$r - 22,
        $sin > 0.3  => $r + 14,
        default     => 4,
    };

    $positions[$node['id']] = [
        'x'      => round($cx + $cos * $radius, 1),
        'y'      => round($cy + $sin * $radius * 0.86, 1),
        'r'      => $r,
        'anchor' => $cos > 0.3 ? 'start' : ($cos < -0.3 ? 'end' : 'middle'),
        'lx'     => abs($sin) > 0.3 ? round($cos * 8, 1) : round($cos * ($r + 9), 1),
        'ly'     => $ly,
    ];
}

/** How much evidence a node has, in words. */
$countLabel = static function (array $node): string {
    if ($node['repos']) {
        return $node['repos'] . ' repos';
    }

    return $node['count'] === 1 ? '1 project' : $node['count'] . ' projects';
};

$roles = array_values($identity['roles'] ?? []);

?>
<section class="hero" id="top" data-section="top" data-label="System core">
    <div class="container">
        <div class="hero-grid">

            <div class="hero-copy">
                <p class="hero-boot label" data-reveal>
                    <span class="live" aria-hidden="true"></span>
                    <span>system.core online</span>
                    <span class="sep" aria-hidden="true">/</span>
                    <span><?= e($identity['location']) ?></span>
                </p>

                <h1 class="hero-name" data-reveal="lines">
                    <span class="ln" style="--i:0"><span><?= e($identity['first_name']) ?></span></span>
                    <span class="ln" style="--i:1"><span><?= e($identity['last_name']) ?><span class="dot">.</span></span></span>
                </h1>

                <p class="hero-role" data-reveal style="--delay:220ms">
                    <span class="fixed"><?= e($identity['title']) ?></span>
                    <span class="visually-hidden">— <?= e(implode(', ', $roles)) ?></span>
                    <span class="slot" aria-hidden="true">
                        <span class="slot-track" id="role-slot">
                            <?php foreach ($roles as $role): ?><span><?= e($role) ?></span><?php endforeach; ?>
                        </span>
                    </span>
                </p>

                <p class="hero-tracks" data-reveal style="--delay:300ms">
                    Backend systems<i>•</i>Full-stack products<i>•</i>Experimental builds
                </p>

                <p class="hero-pitch" data-reveal style="--delay:360ms"><?= e($identity['pitch']) ?></p>

                <div class="hero-actions hero-cta" data-reveal style="--delay:440ms">
                    <a class="btn btn-primary" href="#work" data-magnetic>
                        Explore my work <?= icon('arrow-right', 16, 'i-shift') ?>
                    </a>
                    <button class="btn btn-ghost" type="button" data-open="ask">
                        <span class="orb" aria-hidden="true" style="width:14px;height:14px"></span> Ask Afifa
                    </button>
                    <?php foreach ($socials as $social): ?>
                        <?php if (in_array($social['icon'], ['github', 'linkedin'], true)): ?>
                            <a class="btn btn-icon btn-ghost" href="<?= e($social['url']) ?>" target="_blank"
                               rel="noopener noreferrer" aria-label="<?= e($social['label']) ?>" data-cursor="external"
                               style="border-radius:50%;width:44px;height:44px">
                                <?= icon($social['icon'], 17) ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($identity['available'])): ?>
                    <p class="hero-avail" data-reveal style="--delay:520ms">
                        <span class="live" aria-hidden="true"></span>
                        Currently <?= e(lcfirst((string) $identity['availability'])) ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="core" id="core" data-reveal style="--delay:200ms">
                <div class="core-head">
                    <span class="label">system.core</span>
                    <span class="label"><?= count($nodes) ?> nodes · <?= count($graph['edges']) ?> links</span>
                </div>

                <svg class="core-svg" id="core-svg" viewBox="-330 -300 660 620" preserveAspectRatio="xMidYMid meet"
                     role="group" aria-label="Technology graph. Each technology links to the others it was used alongside.">
                    <circle class="orbit" cx="<?= $cx ?>" cy="<?= $cy ?>" r="128" aria-hidden="true"/>

                    <g class="edges" aria-hidden="true">
                        <?php foreach ($nodes as $node): $p = $positions[$node['id']]; ?>
                            <line class="edge spoke" data-a="core" data-b="<?= e($node['id']) ?>"
                                  x1="<?= $cx ?>" y1="<?= $cy ?>" x2="<?= $p['x'] ?>" y2="<?= $p['y'] ?>"/>
                        <?php endforeach; ?>

                        <?php foreach ($graph['edges'] as [$a, $b, $weight]):
                            $pa = $positions[$a] ?? null;
                            $pb = $positions[$b] ?? null;
                            if (!$pa || !$pb) { continue; }
                            // Curve toward the centre so links read as relationships, not a mesh.
                            $qx = round(($pa['x'] + $pb['x']) / 2 * 0.45, 1);
                            $qy = round(($pa['y'] + $pb['y']) / 2 * 0.45 + $cy * 0.55, 1);
                        ?>
                            <path class="edge" data-a="<?= e($a) ?>" data-b="<?= e($b) ?>" data-w="<?= (int) $weight ?>"
                                  d="M<?= $pa['x'] ?> <?= $pa['y'] ?> Q<?= $qx ?> <?= $qy ?> <?= $pb['x'] ?> <?= $pb['y'] ?>"/>
                        <?php endforeach; ?>
                    </g>

                    <g class="center" aria-hidden="true">
                        <circle class="ring" cx="<?= $cx ?>" cy="<?= $cy ?>" r="54"/>
                        <circle class="body" cx="<?= $cx ?>" cy="<?= $cy ?>" r="40"/>
                        <text x="<?= $cx ?>" y="<?= $cy + 5 ?>" text-anchor="middle">AFIFA</text>
                    </g>

                    <g class="nodes">
                        <?php foreach ($nodes as $node): $p = $positions[$node['id']]; ?>
                            <g class="node" tabindex="0" role="button"
                               data-id="<?= e($node['id']) ?>" data-group="<?= e($node['group']) ?>" data-cursor="explore"
                               aria-label="<?= e($node['label'] . ' — ' . $countLabel($node)) ?>">
                                <circle class="halo" cx="<?= $p['x'] ?>" cy="<?= $p['y'] ?>" r="<?= $p['r'] + 12 ?>"/>
                                <circle class="dot" cx="<?= $p['x'] ?>" cy="<?= $p['y'] ?>" r="<?= $p['r'] ?>"/>
                                <text x="<?= $p['x'] + $p['lx'] ?>" y="<?= $p['y'] + $p['ly'] ?>" text-anchor="<?= $p['anchor'] ?>"><?= e($node['label']) ?></text>
                                <text class="count" x="<?= $p['x'] + $p['lx'] ?>" y="<?= $p['y'] + $p['ly'] + 13 ?>" text-anchor="<?= $p['anchor'] ?>"><?= e($countLabel($node)) ?></text>
                            </g>
                        <?php endforeach; ?>
                    </g>
                </svg>

                <ul class="core-rail" aria-label="Technologies">
                    <?php foreach ($nodes as $node): ?>
                        <li><strong><?= e($node['label']) ?></strong><span><?= e($countLabel($node)) ?></span></li>
                    <?php endforeach; ?>
                </ul>

                <div class="core-card" id="core-card" aria-live="polite"></div>

                <div class="core-foot">
                    <span class="label core-prompt">Move through my stack <?= icon('arrow-right', 13) ?></span>
                    <span class="label"><?= e((string) ($status['mode'] ?? 'Building')) ?> · <?= e((string) ($status['focus'] ?? '')) ?></span>
                </div>
            </div>
        </div>

        <dl class="metrics" data-reveal>
            <div class="metric">
                <dt>Projects</dt>
                <dd><span data-count="<?= (int) $metrics['projects'] ?>"><?= (int) $metrics['projects'] ?></span></dd>
            </div>
            <?php if ($metrics['repos'] !== null): ?>
                <div class="metric">
                    <dt>Public repositories</dt>
                    <dd><span data-count="<?= (int) $metrics['repos'] ?>"><?= (int) $metrics['repos'] ?></span></dd>
                </div>
                <div class="metric">
                    <dt>Languages on GitHub</dt>
                    <dd><span data-count="<?= (int) $metrics['languages'] ?>"><?= (int) $metrics['languages'] ?></span></dd>
                </div>
                <div class="metric">
                    <dt>Building since</dt>
                    <dd><?= e((string) $metrics['since']) ?></dd>
                </div>
            <?php else: ?>
                <div class="metric">
                    <dt>Featured case studies</dt>
                    <dd><span data-count="<?= (int) $metrics['featured'] ?>"><?= (int) $metrics['featured'] ?></span></dd>
                </div>
            <?php endif; ?>
        </dl>
    </div>
</section>
