/**
 * Theme: dark ("technical lab") and light ("engineering notebook").
 *
 * The inline script in <head> applies the stored or system preference before
 * first paint; this module only handles changes. The OS preference is followed
 * until the visitor makes an explicit choice.
 */

import { $$, store } from '@/core/util.js';

const KEY = 'portfolio-theme';

export function currentTheme() {
    return document.documentElement.dataset.theme === 'light' ? 'light' : 'dark';
}

function apply(theme) {
    const root = document.documentElement;
    root.dataset.theme = theme;

    $$('[data-action="theme"]').forEach((btn) => {
        btn.setAttribute('aria-pressed', String(theme === 'light'));
        btn.setAttribute('aria-label', theme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme');
    });

    $$('[data-theme-label]').forEach((el) => { el.textContent = theme; });

    document.dispatchEvent(new CustomEvent('afifa:theme', { detail: { theme } }));
}

export function toggleTheme() {
    const next = currentTheme() === 'dark' ? 'light' : 'dark';

    // The View Transitions API makes the switch a cross-fade where supported.
    const swap = () => apply(next);

    if (document.startViewTransition && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.startViewTransition(swap);
    } else {
        swap();
    }

    store.set(KEY, next);
}

export function initTheme() {
    apply(currentTheme());

    document.addEventListener('click', (e) => {
        if (e.target.closest('[data-action="theme"]')) toggleTheme();
    });

    matchMedia('(prefers-color-scheme: light)').addEventListener('change', (e) => {
        if (!store.get(KEY)) apply(e.matches ? 'light' : 'dark');
    });
}
