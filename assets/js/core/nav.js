/**
 * Navigation: the sliding selection pill, scroll progress, the condensed bar,
 * the "02 / STACK" section announcements, and the fullscreen mobile menu.
 */

import { $, $$, reducedMotion } from '@/core/util.js';

export function initNav() {
    const nav      = $('#nav');
    const progress = $('#progress');
    const links    = $$('.nav-link[data-spy]');
    const pill     = $('.nav-pill');
    const toast    = $('#section-toast');
    const root     = document.documentElement;

    /* --- selection pill -------------------------------------------------- */

    const placePill = (link) => {
        if (!pill) return;

        if (!link) {
            pill.style.setProperty('--po', '0');
            return;
        }

        const listRect = link.parentElement.parentElement.getBoundingClientRect();
        const rect = link.getBoundingClientRect();

        pill.style.setProperty('--px', `${rect.left - listRect.left}px`);
        pill.style.setProperty('--pw', `${rect.width}px`);
        pill.style.setProperty('--po', '1');
    };

    /* --- scroll: progress + condense ------------------------------------- */

    let ticking = false;

    const onScroll = () => {
        const y = window.scrollY;
        const max = document.documentElement.scrollHeight - window.innerHeight;

        nav?.classList.toggle('is-condensed', y > 24);
        progress?.style.setProperty('--p', max > 0 ? (y / max).toFixed(4) : '0');

        ticking = false;
    };

    addEventListener('scroll', () => {
        if (!ticking) {
            ticking = true;
            requestAnimationFrame(onScroll);
        }
    }, { passive: true });

    onScroll();

    /* --- scrollspy + section announcements ------------------------------- */

    const sections = $$('[data-section]');
    let current = null;
    let firstPass = true;
    let toastTimer;

    const announce = (section) => {
        if (!toast || firstPass || !section.dataset.label || section.dataset.section === 'top') return;

        const [num, name] = section.dataset.label.split(' / ');
        toast.innerHTML = name ? `<b>${num}</b> / ${name}` : section.dataset.label;
        toast.classList.add('is-shown');

        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('is-shown'), 1400);
    };

    const activate = (section) => {
        if (section === current) return;
        current = section;

        const id = section.dataset.section;
        root.dataset.section = id;

        let active = null;

        links.forEach((link) => {
            const on = link.dataset.spy === section.id;
            link.toggleAttribute('aria-current', on);
            if (on) {
                link.setAttribute('aria-current', 'true');
                active = link;
            }
        });

        placePill(active);
        announce(section);
    };

    if ('IntersectionObserver' in window && sections.length) {
        // A thin band across the upper middle of the viewport decides which
        // section is "current" — the one being read, not merely visible.
        const io = new IntersectionObserver((entries) => {
            const hit = entries
                .filter((e) => e.isIntersecting)
                .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

            if (hit) activate(hit.target);
            firstPass = false;
        }, { rootMargin: '-38% 0px -58% 0px', threshold: [0, 0.01] });

        sections.forEach((s) => io.observe(s));
        setTimeout(() => { firstPass = false; }, 800);
    }

    addEventListener('resize', () => {
        placePill(links.find((l) => l.hasAttribute('aria-current')));
    });

    /* --- mobile menu ----------------------------------------------------- */

    const menu    = $('#mnav');
    const openBtn = $('#menu-open');
    const closeBtn = $('#menu-close');
    let returnTo = null;

    const setMenu = (open) => {
        if (!menu) return;

        menu.classList.toggle('is-open', open);
        openBtn?.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('is-locked', open);

        if (open) {
            returnTo = document.activeElement;
            setTimeout(() => closeBtn?.focus(), reducedMotion.matches ? 0 : 200);
        } else {
            returnTo?.focus?.();
        }
    };

    openBtn?.addEventListener('click', () => setMenu(true));
    closeBtn?.addEventListener('click', () => setMenu(false));
    $$('a', menu || document.createElement('div')).forEach((a) => a.addEventListener('click', () => setMenu(false)));

    addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && menu?.classList.contains('is-open')) setMenu(false);
    });

    matchMedia('(min-width: 1081px)').addEventListener('change', (e) => {
        if (e.matches) setMenu(false);
    });
}
