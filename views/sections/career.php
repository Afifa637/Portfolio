<?php

/**
 * 09 — Résumé as a career map.
 *
 * Education on one side, activities and experience on the other, joined by a
 * line that draws as you scroll. Three ways to take it away: the uploaded PDF,
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
            <div class="cmap-col" data-reveal>
                <h3 class="label">Education</h3>
                <?php foreach ($education as $item): ?>
                    <article class="cmap-item">
                        <time><?= e($item['start']) ?> — <?= e($item['end']) ?></time>
                        <?php if (!empty($item['current'])): ?><span class="cmap-now"><span class="live"></span> now</span><?php endif; ?>
                        <h4><?= e($item['degree']) ?></h4>
                        <p class="org"><?= e($item['institution']) ?></p>
                        <?php if ($item['grade'] !== ''): ?>
                            <p class="grade"><?= e($item['grade']) ?>
                                <?php if ($item['grade_note'] !== ''): ?><small><?= e($item['grade_note']) ?></small><?php endif; ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($item['detail'] !== ''): ?><p><?= e($item['detail']) ?></p><?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="cmap-spine" aria-hidden="true"></div>

            <div class="cmap-col" data-reveal>
                <?php if ($experience !== []): ?>
                    <h3 class="label">Experience</h3>
                    <?php foreach ($experience as $item): ?>
                        <article class="cmap-item">
                            <time><?= e($item['start']) ?> — <?= e($item['end']) ?></time>
                            <h4><?= e($item['title']) ?></h4>
                            <p class="org"><?= e($item['org']) ?></p>
                            <?php if ($item['body'] !== ''): ?><p><?= e($item['body']) ?></p><?php endif; ?>
                            <?php if ($item['stack'] !== []): ?>
                                <div class="tags"><?php foreach ($item['stack'] as $t): ?><span class="tag"><?= e($t) ?></span><?php endforeach; ?></div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                    <div style="height:2rem"></div>
                <?php endif; ?>

                <h3 class="label">Activities &amp; involvement</h3>
                <?php foreach ($activities as $item): ?>
                    <article class="cmap-item">
                        <time><?= e($item['role']) ?></time>
                        <h4><?= e($item['org']) ?></h4>
                        <?php if ($item['detail'] !== ''): ?><p><?= e($item['detail']) ?></p><?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
