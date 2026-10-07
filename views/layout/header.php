<?php

/**
 * Global chrome rendered at the top of <body>: environment layers, cursor,
 * progress line, navigation and the fullscreen mobile menu.
 *
 * @var bool $isHome  false on case-study and other pages, so section links
 *                    point back to the home page rather than to this one.
 */

declare(strict_types=1);

$identity = Content::get('identity', []);
$socials  = Content::get('socials', []);
$isHome   = $isHome ?? true;

$href = static fn(string $id): string => $isHome ? '#' . $id : home_url($id);

$primary = [
    'about'   => 'About',
    'stack'   => 'Stack',
    'work'    => 'Work',
    'lab'     => 'Lab',
    'journey' => 'Journey',
    'contact' => 'Contact',
];

$everything = [
    'about'    => 'How I think',
    'stack'    => 'Stack',
    'work'     => 'Featured work',
    'hood'     => 'Under the hood',
    'archive'  => 'Archive',
    'lab'      => 'Lab',
    'activity' => 'Activity',
    'journey'  => 'Journey',
    'resume'   => 'Résumé',
    'contact'  => 'Contact',
];

?>
<a class="skip-link" href="#main">Skip to content</a>

<div class="env" aria-hidden="true">
    <div class="env-light"></div>
    <div class="env-grid"></div>
    <div class="env-noise"></div>
</div>

<div class="cursor" aria-hidden="true">
    <div class="cursor-ring">
        <span></span>
        <?= icon('arrow-up-right', 16) ?>
    </div>
    <div class="cursor-dot"></div>
</div>

<div class="progress" id="progress" aria-hidden="true"></div>
<div class="section-toast" id="section-toast" aria-hidden="true"></div>

<header class="nav" id="nav">
    <div class="container">
        <nav class="nav-bar" aria-label="Primary">
            <a class="logo" href="<?= e($isHome ? '#top' : home_url()) ?>" id="logo"
               aria-label="<?= e($identity['name']) ?> — home">
                <span class="logo-mark" aria-hidden="true">AS</span>
                <span class="logo-text">afifa<b>.dev</b></span>
            </a>

            <ul class="nav-links" id="nav-links" role="list">
                <li class="nav-pill" aria-hidden="true"></li>
                <?php $n = 1; foreach ($primary as $id => $label): ?>
                    <li>
                        <a class="nav-link" href="<?= e($href($id)) ?>" data-spy="<?= e($id) ?>">
                            <span class="n"><?= sprintf('%02d', $n++) ?></span><?= e($label) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="nav-actions">
                <button class="nav-k" type="button" data-open="palette" aria-label="Open command palette (Control K)">
                    <?= icon('search', 14) ?> <span>Search</span> <kbd>Ctrl K</kbd>
                </button>

                <button class="btn btn-icon theme-btn" type="button" data-action="theme" aria-label="Toggle colour theme">
                    <span class="sun"><?= icon('sun', 17) ?></span>
                    <span class="moon"><?= icon('moon', 17) ?></span>
                </button>

                <a class="btn btn-primary btn-sm nav-cta" href="<?= e($href('contact')) ?>" data-magnetic>
                    Let’s talk <?= icon('arrow-right', 14, 'i-shift') ?>
                </a>

                <button class="btn btn-icon nav-menu-btn" type="button" id="menu-open"
                        aria-label="Open menu" aria-expanded="false" aria-controls="mnav">
                    <?= icon('menu', 20) ?>
                </button>
            </div>
        </nav>
    </div>
</header>

<div class="mnav" id="mnav" role="dialog" aria-modal="true" aria-label="Site menu">
    <div class="mnav-head">
        <span class="logo"><span class="logo-mark" aria-hidden="true">AS</span> afifa<b>.dev</b></span>
        <button class="btn btn-icon" type="button" id="menu-close" aria-label="Close menu"><?= icon('x', 20) ?></button>
    </div>

    <ul class="mnav-list" role="list">
        <?php $n = 1; foreach ($everything as $id => $label): ?>
            <li>
                <a href="<?= e($href($id)) ?>" style="--i: <?= $n - 1 ?>">
                    <span class="n"><?= sprintf('%02d', $n++) ?></span><?= e($label) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="mnav-foot">
        <p class="label"><span class="live"></span> <?= e($identity['availability']) ?></p>
        <div class="hero-actions">
            <?php foreach ($socials as $social): ?>
                <a class="btn btn-ghost btn-sm" href="<?= e($social['url']) ?>"
                   <?= str_starts_with($social['url'], 'mailto:') ? '' : 'target="_blank" rel="noopener noreferrer"' ?>>
                    <?= icon($social['icon'], 15) ?> <?= e($social['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
