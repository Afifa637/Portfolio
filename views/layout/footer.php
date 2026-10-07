<?php

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

?>
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="#top" class="nav-logo">
                    <span class="mark" aria-hidden="true"><?= e(mb_substr($identity['first_name'], 0, 1) . mb_substr($identity['last_name'], 0, 1)) ?></span>
                    <?= e($identity['name']) ?>
                </a>
                <p><?= e($identity['tagline']) ?> Currently studying Computer Science at KUET and open to
                   internships and junior engineering roles.</p>

                <div class="hero-socials" style="margin-top:var(--sp-4)">
                    <?php foreach ($socials as $social): ?>
                        <a class="social-link" href="<?= e($social['url']) ?>" aria-label="<?= e($social['label']) ?>"
                           <?= str_starts_with($social['url'], 'mailto:') ? '' : 'target="_blank" rel="noopener noreferrer"' ?>>
                            <?= icon($social['icon'], 18) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <nav aria-label="Footer">
                <h3 class="footer-heading">Sections</h3>
                <ul class="footer-links" role="list">
                    <?php foreach ($navItems as $id => $label): ?>
                        <li><a href="#<?= e($id) ?>"><?= e($label) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <div>
                <h3 class="footer-heading">Elsewhere</h3>
                <ul class="footer-links" role="list">
                    <li>
                        <a href="https://github.com/<?= e($identity['github_user']) ?>" target="_blank" rel="noopener noreferrer">
                            GitHub profile
                        </a>
                    </li>
                    <li><a href="<?= e($identity['resume']) ?>">Download CV</a></li>
                    <li><a href="mailto:<?= e($identity['email']) ?>"><?= e($identity['email']) ?></a></li>
                    <li><a href="<?= e(url('sitemap.xml')) ?>">Sitemap</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© <span data-year><?= date('Y') ?></span> <?= e($identity['name']) ?>. All rights reserved.</p>
            <p class="mono">Built with PHP, no frameworks, no trackers.</p>
        </div>
    </div>
</footer>

<button class="to-top" id="to-top" type="button" aria-label="Back to top">
    <?= icon('arrow-up', 19) ?>
</button>

<?php /* Command palette. The item list is built from the rendered page at open
         time, so it always matches whatever the CMS published. */ ?>
<div class="cmdk" id="cmdk" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Command palette">
    <div class="cmdk-panel">
        <div class="cmdk-search">
            <?= icon('search', 18) ?>
            <label class="visually-hidden" for="cmdk-input">Search sections, projects and actions</label>
            <input type="text" id="cmdk-input" placeholder="Jump to a section or project…"
                   autocomplete="off" spellcheck="false">
        </div>

        <div class="cmdk-list" id="cmdk-list" role="listbox" aria-label="Results"></div>

        <div class="cmdk-foot">
            <span><kbd>↑</kbd><kbd>↓</kbd> navigate</span>
            <span><kbd>↵</kbd> open</span>
            <span><kbd>esc</kbd> close</span>
        </div>
    </div>
</div>

<script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
<script src="<?= e(asset('assets/js/enhance.js')) ?>" defer></script>

<?php if (GitHub::needsRefresh()): ?>
    <script>
        /* The GitHub payload on disk is stale. Refresh it after the page has
           settled so the next visitor gets fresh data, without this visitor
           paying for the API call. */
        addEventListener('load', () => {
            const run = () => fetch('api/github.php', { credentials: 'same-origin' }).catch(() => {});
            'requestIdleCallback' in window ? requestIdleCallback(run, { timeout: 3000 }) : setTimeout(run, 1200);
        }, { once: true });
    </script>
<?php endif; ?>
