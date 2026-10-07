/**
 * Shared utilities for every module.
 *
 * Modules import each other through the import map ('@/core/util.js'), which
 * the server generates with a fingerprinted URL per file — so a deploy busts
 * the cache for nested imports too, without a bundler.
 */

export const $  = (sel, ctx = document) => ctx.querySelector(sel);
export const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

export const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
export const finePointer   = matchMedia('(hover: hover) and (pointer: fine)');

export const clamp = (v, min, max) => Math.min(max, Math.max(min, v));
export const lerp  = (a, b, t) => a + (b - a) * t;
export const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

/** Run fn, logging rather than throwing, so one broken feature cannot stop the rest. */
export function safely(name, fn) {
    try {
        return fn();
    } catch (err) {
        console.error(`[afifa.dev] ${name} failed:`, err);
        return undefined;
    }
}

/** Escape text for insertion into HTML. */
export function esc(value) {
    return String(value ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
}

/** localStorage that never throws — private mode and blocked storage are common. */
export const store = {
    get(key, fallback = null) {
        try { return localStorage.getItem(key) ?? fallback; } catch { return fallback; }
    },
    set(key, value) {
        try { localStorage.setItem(key, value); } catch { /* storage unavailable */ }
    },
    session: {
        get(key) { try { return sessionStorage.getItem(key); } catch { return null; } },
        set(key, value) { try { sessionStorage.setItem(key, value); } catch { /* ignore */ } },
    },
};

let payload = null;

/** The content payload rendered by the server (Knowledge::payload()). */
export function data() {
    if (payload) return payload;

    try {
        payload = JSON.parse(document.getElementById('portfolio-data')?.textContent || '{}');
    } catch {
        payload = {};
    }

    payload.projects ??= [];
    payload.skills ??= [];
    payload.bySlug = Object.fromEntries(payload.projects.map((p) => [p.slug, p]));

    return payload;
}

/** An inline SVG icon from the server-rendered sprite, so JS and PHP share one set. */
export function icon(name) {
    const tpl = document.getElementById('icon-sprite');
    return tpl?.content.querySelector(`[data-icon="${name}"]`)?.innerHTML ?? '';
}

let toastTimer;

export function toast(message, ms = 2200) {
    const el = document.getElementById('toast');
    if (!el) return;

    el.textContent = message;
    el.classList.add('is-shown');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove('is-shown'), ms);
}

/**
 * Privacy-friendly event tracking. A no-op unless a cookieless analytics
 * script (Plausible) is present; otherwise events are only dispatched locally.
 */
export function track(event, props = {}) {
    try {
        window.plausible?.(event, { props });
    } catch { /* never let analytics break the page */ }

    document.dispatchEvent(new CustomEvent('afifa:track', { detail: { event, props } }));
}

/**
 * Load a module when an element approaches the viewport. This is the code
 * splitting: the Lab, the archive and the architecture explorer cost nothing
 * until a visitor scrolls near them.
 */
export function lazy(selector, loader, { margin = '600px' } = {}) {
    const el = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!el) return;

    let done = false;

    const run = () => {
        if (done) return;
        done = true;

        loader()
            .then((mod) => mod?.init?.(el))
            .catch((err) => console.error('[afifa.dev] module failed to load:', err));
    };

    if (!('IntersectionObserver' in window)) {
        run();
        return;
    }

    const io = new IntersectionObserver((entries) => {
        if (entries.some((e) => e.isIntersecting)) {
            io.disconnect();
            run();
        }
    }, { rootMargin: margin });

    io.observe(el);
}

/** Smooth-scroll to a section id, honouring reduced motion. */
export function goTo(id) {
    const el = document.getElementById(id.replace(/^#/, ''));

    if (!el) {
        // Not on this page — go home with the fragment.
        location.href = (document.body.dataset.home || '/') + '#' + id.replace(/^#/, '');
        return;
    }

    el.scrollIntoView({ behavior: reducedMotion.matches ? 'auto' : 'smooth', block: 'start' });
    history.replaceState(null, '', '#' + el.id);
}

/** Copy text, falling back to a selection for non-secure contexts. */
export async function copyText(text) {
    try {
        await navigator.clipboard.writeText(text);
        return true;
    } catch {
        const ta = Object.assign(document.createElement('textarea'), { value: text });
        ta.setAttribute('readonly', '');
        ta.style.cssText = 'position:fixed;opacity:0;pointer-events:none';
        document.body.append(ta);
        ta.select();

        let ok = false;
        try { ok = document.execCommand('copy'); } catch { /* ignore */ }
        ta.remove();

        return ok;
    }
}
