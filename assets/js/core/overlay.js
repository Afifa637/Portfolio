/**
 * Modal overlay behaviour shared by the palette, terminal and recruiter mode:
 * open/close, Escape, backdrop click, body scroll lock, focus trap, and focus
 * returned to wherever it was before opening.
 */

import { $$ } from '@/core/util.js';

const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), textarea, select, [tabindex]:not([tabindex="-1"])';
const stack = [];

function onKey(e) {
    const top = stack[stack.length - 1];
    if (!top) return;

    if (e.key === 'Escape') {
        e.preventDefault();
        close(top.el);
        return;
    }

    if (e.key !== 'Tab') return;

    const items = $$(FOCUSABLE, top.el).filter((el) => el.offsetParent !== null);
    if (items.length === 0) return;

    const first = items[0];
    const last = items[items.length - 1];

    if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
    }
}

export function isOpen(el) {
    return el?.classList.contains('is-open') ?? false;
}

export function open(el, { focus, onClose } = {}) {
    if (!el || isOpen(el)) return;

    stack.push({ el, returnTo: document.activeElement, onClose });

    el.classList.add('is-open');
    el.removeAttribute('inert');
    document.body.classList.add('is-locked');

    if (stack.length === 1) document.addEventListener('keydown', onKey);

    requestAnimationFrame(() => (focus ?? el.querySelector(FOCUSABLE))?.focus());
}

export function close(el) {
    const index = stack.findIndex((entry) => entry.el === el);
    if (index === -1) return;

    const [entry] = stack.splice(index, 1);

    el.classList.remove('is-open');
    el.setAttribute('inert', '');

    if (stack.length === 0) {
        document.body.classList.remove('is-locked');
        document.removeEventListener('keydown', onKey);
    }

    entry.onClose?.();
    entry.returnTo?.focus?.();
}

export function initOverlays() {
    $$('[data-overlay]').forEach((el) => {
        el.setAttribute('inert', '');

        // A click on the dimmed backdrop, not inside the panel, dismisses it.
        el.addEventListener('mousedown', (e) => {
            if (e.target === el) close(el);
        });
    });
}
