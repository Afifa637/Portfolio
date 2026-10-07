/**
 * Scroll-linked motion: reveals, counters, and lines that draw as you read.
 *
 * Each effect communicates something — order, quantity, progress — rather than
 * decorating. Under prefers-reduced-motion everything resolves to its final
 * state immediately.
 */

import { $, $$, clamp, reducedMotion } from '@/core/util.js';

function countUp(el) {
    const target = parseInt(el.dataset.count, 10);
    if (Number.isNaN(target)) return;

    if (reducedMotion.matches) {
        el.textContent = String(target);
        return;
    }

    const duration = 1000;
    const start = performance.now();

    const step = (now) => {
        const t = clamp((now - start) / duration, 0, 1);
        const eased = 1 - Math.pow(1 - t, 4);

        el.textContent = String(Math.round(target * eased));
        if (t < 1) requestAnimationFrame(step);
    };

    el.textContent = '0';
    requestAnimationFrame(step);
}

/** Progress (0–1) of an element's journey through the viewport. */
function progressThrough(el, offset = 0.65) {
    const rect = el.getBoundingClientRect();
    const vh = window.innerHeight;

    return clamp((vh * offset - rect.top) / rect.height, 0, 1);
}

export function initReveal() {
    const items = $$('[data-reveal]');
    const counters = $$('[data-count]');

    if (reducedMotion.matches || !('IntersectionObserver' in window)) {
        items.forEach((el) => el.classList.add('is-in'));
        counters.forEach((el) => { el.textContent = el.dataset.count; });
    } else {
        // Stagger siblings that enter together.
        items.forEach((el) => {
            if (el.style.getPropertyValue('--delay')) return;

            const siblings = Array.from(el.parentElement?.children ?? []).filter((c) => c.hasAttribute('data-reveal'));
            const index = siblings.indexOf(el);

            if (index > 0) el.style.setProperty('--delay', `${Math.min(index, 6) * 70}ms`);
        });

        const io = new IntersectionObserver((entries, obs) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                entry.target.classList.add('is-in');
                $$('[data-count]', entry.target).forEach(countUp);
                if (entry.target.hasAttribute('data-count')) countUp(entry.target);

                obs.unobserve(entry.target);
            });
        }, { rootMargin: '0px 0px -10% 0px', threshold: 0.12 });

        items.forEach((el) => io.observe(el));
    }

    /* Lines that draw with reading progress: the journey and the career map. */
    const journey = $('#journey-line');
    const steps   = journey ? $$('.jr-step', journey) : [];
    const cmap    = $('#cmap');

    if (!journey && !cmap) return;

    let ticking = false;

    const reduced = (value) => (reducedMotion.matches ? true : value);

    const update = () => {
        ticking = false;

        if (journey) {
            const p = reducedMotion.matches ? 1 : progressThrough(journey);
            journey.style.setProperty('--jp', p.toFixed(3));

            steps.forEach((step) => {
                const reached = step.getBoundingClientRect().top < window.innerHeight * 0.66;
                step.classList.toggle('is-reached', reduced(reached));
            });
        }

        if (cmap) {
            const p = reducedMotion.matches ? 1 : progressThrough(cmap, 0.7);
            cmap.style.setProperty('--cp', p.toFixed(3));
        }
    };

    addEventListener('scroll', () => {
        if (!ticking) {
            ticking = true;
            requestAnimationFrame(update);
        }
    }, { passive: true });

    update();
}
