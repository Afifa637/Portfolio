/**
 * Entry point.
 *
 * Core behaviour (navigation, theme, reveals, palette, shortcuts) starts
 * immediately. Everything heavier is a separate module loaded when its section
 * approaches the viewport, or when a visitor asks for it — the code splitting
 * a bundler would give, from native ES modules and an import map.
 */

import { $, $$, safely, lazy, copyText, track } from '@/core/util.js';
import { initTheme } from '@/core/theme.js';
import { initOverlays } from '@/core/overlay.js';
import { initNav } from '@/core/nav.js';
import { initReveal } from '@/core/reveal.js';
import { initPointer } from '@/core/pointer.js';
import { initPalette, openAssistant, openRecruiter } from '@/core/palette.js';
import { initKeys } from '@/core/keys.js';

safely('theme', initTheme);
safely('overlays', initOverlays);
safely('nav', initNav);
safely('reveal', initReveal);
safely('pointer', initPointer);
safely('palette', initPalette);
safely('keys', initKeys);

/* --- section modules, loaded on approach -------------------------------- */

lazy('#core', () => import('@/modules/core-graph.js'), { margin: '0px' });
lazy('#blueprint', () => import('@/modules/blueprint.js'));
lazy('#stack', () => import('@/modules/stack.js'));
lazy('#hood', () => import('@/modules/hood.js'));
lazy('#archive', () => import('@/modules/archive.js'));
lazy('#lab', () => import('@/modules/lab.js'));
lazy('#contact-form', () => import('@/modules/contact.js'));
lazy('#cs-page', () => import('@/modules/case-study.js'));

/* --- small global behaviours -------------------------------------------- */

safely('assistant trigger', () => {
    const fab = $('#ask-open');
    fab?.addEventListener('click', openAssistant);

    // Warm the assistant module on intent, so opening it feels instant.
    fab?.addEventListener('pointerenter', () => import('@/modules/assistant.js'), { once: true });
});

safely('copy', () => {
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-copy]');
        if (!btn) return;

        if (await copyText(btn.dataset.copy)) {
            btn.classList.add('is-copied');
            btn.setAttribute('aria-label', 'Copied');
            setTimeout(() => {
                btn.classList.remove('is-copied');
                btn.setAttribute('aria-label', 'Copy email address');
            }, 1800);
        }
    });
});

safely('clock', () => {
    const clocks = $$('[data-clock]');
    if (!clocks.length) return;

    const tick = () => {
        const now = new Date();

        clocks.forEach((el) => {
            try {
                el.textContent = new Intl.DateTimeFormat('en-GB', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: el.hasAttribute('data-seconds') ? '2-digit' : undefined,
                    hour12: false,
                    timeZone: el.dataset.tz || undefined,
                }).format(now);
            } catch { /* unknown time zone: keep the server value */ }
        });
    };

    tick();
    setInterval(tick, 1000);
});

safely('track', () => {
    document.addEventListener('click', (e) => {
        const link = e.target.closest('[data-track]');
        if (!link) return;

        const map = { github: 'github clicked', resume: 'resume downloaded' };
        track(map[link.dataset.track] || link.dataset.track);
    });
});

/* Recruiter view can be linked directly: /?view=recruiter */
safely('recruiter link', () => {
    if (new URLSearchParams(location.search).get('view') === 'recruiter') openRecruiter();
});
