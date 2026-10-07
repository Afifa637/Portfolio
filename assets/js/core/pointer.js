/**
 * Pointer-driven effects: the environment grid that brightens around the
 * cursor, spotlight borders, magnetic buttons, card tilt, and the custom
 * cursor with contextual labels.
 *
 * Desktop with a fine pointer only, and nothing at all under reduced motion.
 * All writes are batched into one requestAnimationFrame per frame.
 */

import { $, $$, finePointer, reducedMotion, lerp } from '@/core/util.js';

export function initPointer() {
    if (reducedMotion.matches || !finePointer.matches) return;

    const root = document.documentElement;
    let mx = innerWidth / 2;
    let my = innerHeight / 3;
    let frame = 0;

    /* --- environment grid follows the cursor ------------------------------ */

    const paintEnv = () => {
        frame = 0;
        root.style.setProperty('--mx', `${mx}px`);
        root.style.setProperty('--my', `${my}px`);
    };

    addEventListener('pointermove', (e) => {
        mx = e.clientX;
        my = e.clientY;
        if (!frame) frame = requestAnimationFrame(paintEnv);
    }, { passive: true });

    /* --- spotlight borders ---------------------------------------------- */

    document.addEventListener('pointermove', (e) => {
        const card = e.target.closest?.('.spot');
        if (!card) return;

        const r = card.getBoundingClientRect();
        card.style.setProperty('--x', `${e.clientX - r.left}px`);
        card.style.setProperty('--y', `${e.clientY - r.top}px`);
    }, { passive: true });

    /* --- card tilt (archive) -------------------------------------------- */

    const MAX_TILT = 3.5;

    document.addEventListener('pointermove', (e) => {
        const card = e.target.closest?.('.arc-card');
        if (!card) return;

        const r = card.getBoundingClientRect();
        const px = (e.clientX - r.left) / r.width - 0.5;
        const py = (e.clientY - r.top) / r.height - 0.5;

        card.style.setProperty('--ty', `${px * MAX_TILT * 2}deg`);
        card.style.setProperty('--tx', `${-py * MAX_TILT * 2}deg`);
    }, { passive: true });

    document.addEventListener('pointerout', (e) => {
        const card = e.target.closest?.('.arc-card');
        if (card && !card.contains(e.relatedTarget)) {
            card.style.setProperty('--tx', '0deg');
            card.style.setProperty('--ty', '0deg');
        }
    });

    /* --- magnetic buttons ----------------------------------------------- */

    $$('[data-magnetic]').forEach((el) => {
        const strength = 0.25;

        el.addEventListener('pointermove', (e) => {
            const r = el.getBoundingClientRect();
            const x = e.clientX - r.left - r.width / 2;
            const y = e.clientY - r.top - r.height / 2;
            el.style.translate = `${x * strength}px ${y * strength}px`;
        });

        el.addEventListener('pointerleave', () => { el.style.translate = ''; });
    });

    initCursor();
}

/* ======================================================= custom cursor === */

function initCursor() {
    const cursor = $('.cursor');
    if (!cursor) return;

    const dot  = $('.cursor-dot', cursor);
    const ring = $('.cursor-ring', cursor);
    const text = $('.cursor-ring span', cursor);

    document.documentElement.classList.add('has-cursor');

    let x = innerWidth / 2;
    let y = innerHeight / 2;
    let rx = x;
    let ry = y;
    let visible = false;

    addEventListener('pointermove', (e) => {
        if (e.pointerType !== 'mouse') return;

        x = e.clientX;
        y = e.clientY;

        // The dot is exact; only the ring trails, and only slightly.
        dot.style.transform = `translate3d(${x}px, ${y}px, 0)`;

        if (!visible) {
            visible = true;
            rx = x;
            ry = y;
            cursor.classList.remove('is-hidden');
        }
    }, { passive: true });

    const loop = () => {
        rx = lerp(rx, x, 0.28);
        ry = lerp(ry, y, 0.28);
        ring.style.transform = `translate3d(${rx}px, ${ry}px, 0)`;
        requestAnimationFrame(loop);
    };

    requestAnimationFrame(loop);

    document.addEventListener('mouseleave', () => {
        visible = false;
        cursor.classList.add('is-hidden');
    });

    /* Contextual states. */
    const LABELS = { view: 'VIEW', explore: 'EXPLORE', drag: 'DRAG' };

    document.addEventListener('pointerover', (e) => {
        const target = e.target.closest?.('a, button, [data-cursor], input, textarea, select, [role="button"]');

        cursor.classList.remove('is-hover', 'is-label', 'is-ext', 'is-text');
        text.textContent = '';

        if (!target) return;

        if (target.matches('input:not([type="radio"]):not([type="checkbox"]), textarea')) {
            cursor.classList.add('is-text');
            return;
        }

        const mode = target.dataset.cursor;

        if (mode && LABELS[mode]) {
            text.textContent = LABELS[mode];
            cursor.classList.add('is-label');
        } else if (mode === 'external' || (target.matches('a[target="_blank"]'))) {
            cursor.classList.add('is-ext');
        } else {
            cursor.classList.add('is-hover');
        }
    });

    // Never trap the cursor hidden inside an overlay or iframe.
    addEventListener('blur', () => cursor.classList.add('is-hidden'));
}
