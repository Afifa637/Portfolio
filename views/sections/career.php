<?php

/**
 * 09 — Résumé as a career map.
 *
 * Education as cards with each grade drawn as a ring, then experience and
 * activities as icon cards. Three ways to take it away: the uploaded PDF,
 * the live résumé page generated from this same content, and a print view of
 * that page — so the printable résumé can never fall behind the site.
 */

declare(strict_types=1);

$identity   = Content::get('identity', []);
$education  = Content::get('education', []);
$activities = Content::get('activities', []);
$experience = Content::get('experience', []);

?>
<section class="section career" id="resume" data-section="resume" data-label="09 / Résumé">
    <div class="container">
        <header class="sh">
            <p class="label"><b>09</b> / Résumé</p>
            <h2 data-reveal="lines">
                <span class="ln" style="--i:0"><span>Career <span class="serif">map.</span></span></span>
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

        <div class="cmap" id="cmap">
            <h3 class="label cmap-label">Education</h3>
            <div class="cmap-grid">
                <?php foreach ($education as $i => $item):
                    // "CGPA 3.77 / 4.00" -> 3.77 of 4.00, drawn as a ring.
                    $score = preg_match('/(\d+(?:\.\d+)?)\s*\/\s*(\d+(?:\.\d+)?)/', (string) $item['grade'], $g)
                        ? ['value' => $g[1], 'max' => $g[2], 'ratio' => min(1, (float) $g[1] / max(0.01, (float) $g[2]))]
                        : null;
                    $extra = trim((string) preg_replace('/^.*?\d+(?:\.\d+)?\s*\/\s*\d+(?:\.\d+)?\s*/', '', (string) $item['grade']), " ()");
                ?>
                    <article class="edu-card spot" data-reveal style="--delay:<?= $i * 80 ?>ms">
                        <?php if ($score): ?>
                            <div class="gauge" style="--v:<?= round($score['ratio'], 4) ?>" role="img"
                                 aria-label="<?= e($item['grade']) ?>">
                                <svg viewBox="0 0 72 72" aria-hidden="true">
                                    <circle class="gauge-track" cx="36" cy="36" r="31"/>
                                    <circle class="gauge-fill" cx="36" cy="36" r="31" pathLength="100"/>
                                </svg>
                                <span><b><?= e($score['value']) ?></b><small>/ <?= e($score['max']) ?></small></span>
                            </div>
                        <?php else: ?>
                            <div class="gauge gauge-icon" aria-hidden="true"><?= icon('graduation', 26) ?></div>
                        <?php endif; ?>

                        <div class="edu-text">
                            <p class="edu-when">
                                <time><?= e($item['start']) ?> — <?= e($item['end']) ?></time>
                                <?php if (!empty($item['current'])): ?><span class="cmap-now"><span class="live"></span> now</span><?php endif; ?>
                            </p>
                            <h4><?= e($item['degree']) ?></h4>
                            <p class="org"><?= e($item['institution']) ?></p>
                            <?php if ($item['grade_note'] !== '' || $extra !== ''): ?>
                                <p class="edu-note">
                                    <?= e(trim($item['grade_note'] . ($extra !== '' && !str_contains(strtolower($extra), 'gpa') ? ' · ' . $extra : ''), ' ·')) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($experience !== []): ?>
                <h3 class="label cmap-label">Experience</h3>
                <div class="cmap-grid">
                    <?php foreach ($experience as $item): ?>
                        <article class="act-card spot" data-reveal>
                            <span class="act-glyph" aria-hidden="true"><?= icon('briefcase', 20) ?></span>
                            <div>
                                <p class="edu-when"><time><?= e($item['start']) ?> — <?= e($item['end']) ?></time></p>
                                <h4><?= e($item['title']) ?></h4>
                                <p class="org"><?= e($item['org']) ?></p>
                                <?php if ($item['stack'] !== []): ?>
                                    <div class="tlogos" style="margin-top:0.6rem"><?php foreach ($item['stack'] as $t): ?><?= tech_mark($t, 18) ?><?php endforeach; ?></div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h3 class="label cmap-label">Activities &amp; involvement</h3>
            <div class="cmap-grid">
                <?php foreach ($activities as $i => $item):
                    $about = strtolower($item['org'] . ' ' . $item['detail']);
                    $glyph = match (true) {
                        str_contains($about, 'hackathon')                                   => 'zap',
                        str_contains($about, 'programming') || str_contains($about, 'contest') => 'code',
                        str_contains($about, 'hardware') || str_contains($about, 'embedded')  => 'cpu',
                        default                                                             => 'award',
                    };
                ?>
                    <article class="act-card spot" data-reveal style="--delay:<?= $i * 80 ?>ms">
                        <span class="act-glyph" aria-hidden="true"><?= icon($glyph, 20) ?></span>
                        <div>
                            <p class="edu-when"><?= e($item['role']) ?></p>
                            <h4><?= e($item['org']) ?></h4>
                            <?php if ($item['detail'] !== ''): ?><p class="act-detail"><?= e($item['detail']) ?></p><?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
