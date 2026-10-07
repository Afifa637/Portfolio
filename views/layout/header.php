<?php

/**
 * Site header: fixed navigation, theme toggle, and the mobile drawer.
 *
 * @var array<string, string> $navItems  section id => label
 */

declare(strict_types=1);

$identity = Content::get('identity', []);
$socials  = Content::get('socials', []);

$navItems = $navItems ?? [
    'about'    => 'About',
    'skills'   => 'Skills',
    'projects' => 'Projects',
    'github'   => 'Activity',
    'resume'   => 'Resume',
    'contact'  => 'Contact',
];

$initials = mb_substr($identity['first_name'], 0, 1) . mb_substr($identity['last_name'], 0, 1);

?>
<a class="skip-link" href="#main">Skip to main content</a>

<div class="progress" id="progress" aria-hidden="true"></div>

<header class="header" id="header">
    <nav class="nav container" aria-label="Primary">
        <a href="#top" class="nav-logo">
            <span class="mark" aria-hidden="true"><?= e($initials) ?></span>
            <?= e($identity['name']) ?>
        </a>

        <ul class="nav-list" role="list">
            <?php foreach ($navItems as $id => $label): ?>
                <li>
                    <a href="#<?= e($id) ?>" class="nav-link" data-spy="<?= e($id) ?>"><?= e($label) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="nav-actions">
            <?php /* Keyboard-first navigation. The shortcut works everywhere;
                     this button exists so visitors discover it. */ ?>
            <button class="cmdk-hint" type="button" data-cmdk-open aria-label="Open the command palette">
                <?= icon('search', 14) ?>
                <kbd>Ctrl K</kbd>
            </button>

            <button class="btn btn-icon theme-toggle" id="theme-toggle" type="button"
                    aria-label="Switch theme" aria-pressed="false">
                <span data-theme-icon="sun"><?= icon('sun', 18) ?></span>
                <span data-theme-icon="moon" hidden><?= icon('moon', 18) ?></span>
            </button>

            <a class="btn btn-sm btn-primary nav-cta" href="#contact">
                <?= icon('send', 15) ?> Hire me
            </a>

            <button class="btn btn-icon nav-toggle" id="nav-toggle" type="button"
                    aria-label="Open menu" aria-expanded="false" aria-controls="nav-drawer">
                <?= icon('menu', 20) ?>
            </button>
        </div>
    </nav>
</header>

<div class="nav-drawer" id="nav-drawer" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Site menu">
    <div class="nav-drawer-head">
        <span class="nav-logo">
            <span class="mark" aria-hidden="true"><?= e($initials) ?></span>
            <?= e($identity['name']) ?>
        </span>
        <button class="btn btn-icon" id="nav-close" type="button" aria-label="Close menu">
            <?= icon('x', 20) ?>
        </button>
    </div>

    <ul class="nav-drawer-list" role="list">
        <?php $n = 1; foreach ($navItems as $id => $label): ?>
            <li>
                <a href="#<?= e($id) ?>">
                    <span class="num"><?= str_pad((string) $n++, 2, '0', STR_PAD_LEFT) ?></span>
                    <?= e($label) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="nav-drawer-foot">
        <?php foreach ($socials as $social): ?>
            <a class="btn btn-sm btn-ghost" href="<?= e($social['url']) ?>"
               <?= str_starts_with($social['url'], 'mailto:') ? '' : 'target="_blank" rel="noopener noreferrer"' ?>>
                <?= icon($social['icon'], 16) ?> <?= e($social['label']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
