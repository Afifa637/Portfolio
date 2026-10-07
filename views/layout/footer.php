<?php

/**
 * Footer, global interface layers, and the module entry point.
 *
 * @var bool $isHome
 */

declare(strict_types=1);

$identity = Content::get('identity', []);
$socials  = Content::get('socials', []);
$status   = Content::get('status', []);
$isHome   = $isHome ?? true;
$href     = static fn(string $id): string => $isHome ? '#' . $id : home_url($id);

// Server-rendered first value for the live clock, so it is right before JS runs.
try {
    $now = new DateTimeImmutable('now', new DateTimeZone((string) ($status['timezone'] ?? 'Asia/Dhaka')));
} catch (Throwable) {
    $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
}

?>
<footer class="footer">
    <div class="container">
        <div class="foot-grid">
            <div class="foot-brand">
                <h2><?= e($identity['name']) ?></h2>
                <p><?= e($identity['title']) ?> · <?= e($identity['subtitle']) ?></p>
                <p class="serif">Designed and engineered with curiosity.</p>
            </div>

            <nav class="foot-col" aria-label="Footer">
                <h3>Explore</h3>
                <ul>
                    <li><a class="ulink" href="<?= e($href('work')) ?>">Featured work</a></li>
                    <li><a class="ulink" href="<?= e($href('hood')) ?>">Under the hood</a></li>
                    <li><a class="ulink" href="<?= e($href('lab')) ?>">Lab</a></li>
                    <li><a class="ulink" href="<?= e($href('journey')) ?>">Journey</a></li>
                    <li><a class="ulink" href="<?= e(base_path() . '/resume.php') ?>">Résumé</a></li>
                </ul>
            </nav>

            <div class="foot-col">
                <h3>Elsewhere</h3>
                <ul>
                    <?php foreach ($socials as $social): ?>
                        <li>
                            <a class="ulink" href="<?= e($social['url']) ?>"
                               <?= str_starts_with($social['url'], 'mailto:') ? '' : 'target="_blank" rel="noopener noreferrer"' ?>>
                                <?= e($social['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li><a class="ulink" href="<?= e(url(ltrim((string) $identity['resume'], '/'))) ?>">Download CV</a></li>
                </ul>
            </div>

            <div class="foot-col">
                <h3>System</h3>
                <div class="foot-status">
                    <div><span>status</span><b><span class="live"></span> operational</b></div>
                    <div><span>availability</span><b><?= !empty($identity['available']) ? 'open' : 'busy' ?></b></div>
                    <div><span>local time</span>
                        <b><time data-clock data-tz="<?= e((string) ($status['timezone'] ?? 'Asia/Dhaka')) ?>"><?= e($now->format('H:i')) ?></time></b>
                    </div>
                    <div><span>focus</span><b><?= e((string) ($status['focus'] ?? '')) ?></b></div>
                </div>
            </div>
        </div>

        <div class="foot-bottom">
            <span>© <span data-year><?= date('Y') ?></span> <?= e($identity['name']) ?> · <?= e($identity['location']) ?></span>
            <span><code>PORTFOLIO.VERSION = <?= e((string) ($status['version'] ?? '2.0')) ?></code></span>
            <span>Press <kbd>Ctrl</kbd> <kbd>K</kbd> · <kbd>~</kbd> for a terminal</span>
        </div>
    </div>
</footer>

<!-- ============================================== global interface layers -->

<div class="overlay" id="palette" role="dialog" aria-modal="true" aria-label="Command palette" data-overlay>
    <div class="overlay-panel cmdk-panel">
        <div class="cmdk-search">
            <?= icon('search', 18) ?>
            <label class="visually-hidden" for="palette-input">Search commands, sections and projects</label>
            <input type="text" id="palette-input" placeholder="Type a command, a project, or a technology…"
                   autocomplete="off" spellcheck="false" role="combobox" aria-expanded="true"
                   aria-controls="palette-list" aria-autocomplete="list">
        </div>
        <div class="cmdk-list" id="palette-list" role="listbox" aria-label="Results"></div>
        <div class="cmdk-foot">
            <span><kbd>↑</kbd><kbd>↓</kbd> move</span>
            <span><kbd>↵</kbd> run</span>
            <span><kbd>esc</kbd> close</span>
            <span style="margin-left:auto">Fuzzy search</span>
        </div>
    </div>
</div>

<button class="ask-fab no-print" type="button" id="ask-open" aria-expanded="false" aria-controls="ask">
    <span class="orb" aria-hidden="true"></span><span class="t">Ask Afifa</span>
</button>

<section class="ask no-print" id="ask" aria-label="Ask Afifa — portfolio assistant">
    <header class="ask-head">
        <span class="orb" aria-hidden="true"></span>
        <div>
            <strong>Ask Afifa</strong>
            <small>Answers grounded in this portfolio</small>
        </div>
        <button class="btn btn-icon" type="button" id="ask-close" aria-label="Close assistant"><?= icon('x', 18) ?></button>
    </header>
    <div class="ask-log" id="ask-log" aria-live="polite"></div>
    <div class="ask-chips" id="ask-chips"></div>
    <form class="ask-form" id="ask-form">
        <label class="visually-hidden" for="ask-input">Ask a question about Afifa’s work</label>
        <input type="text" id="ask-input" placeholder="e.g. Which projects use Spring Boot?" autocomplete="off">
        <button class="btn btn-primary btn-icon" type="submit" aria-label="Send" style="border-radius:50%">
            <?= icon('send', 16) ?>
        </button>
    </form>
    <p class="ask-note">Retrieval over this site’s own content — every answer links its source. No external AI service.</p>
</section>

<div class="overlay" id="terminal" role="dialog" aria-modal="true" aria-label="Terminal" data-overlay></div>
<div class="overlay rec" id="recruiter" role="dialog" aria-modal="true" aria-label="Recruiter mode" data-overlay></div>
<div class="toast" id="toast" role="status" aria-live="polite"></div>

<?php
/* Icon sprite for the client modules, so JS-rendered UI uses the same set. */
$sprite = ['arrow-right', 'arrow-up-right', 'external', 'github', 'layers', 'code', 'download', 'mail',
    'sun', 'copy', 'terminal', 'user', 'zap', 'shuffle', 'search', 'file', 'eye', 'route', 'flask',
    'message', 'server', 'database', 'shield', 'printer', 'check', 'x', 'sparkle', 'command'];
?>
<template id="icon-sprite">
    <?php foreach ($sprite as $name): ?><i data-icon="<?= e($name) ?>"><?= icon($name, 16) ?></i><?php endforeach; ?>
</template>

<script type="module" src="<?= e(asset('assets/js/main.js')) ?>"></script>
<noscript><style>.ask-fab { display: none; }</style></noscript>
