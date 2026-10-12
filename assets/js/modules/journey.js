/**
 * Journey: the stage track works as tabs. Click, or arrow keys along the
 * track, to show a stage's panel. Without this module every panel is visible.
 */

import { $$ } from '@/core/util.js';

export function init(root) {
    const tabs = $$('.jr-btn', root);
    const panels = $$('.jr-panel', root);
    if (!tabs.length) return;

    root.classList.add('is-tabs');

    const select = (index, focus = false) => {
        tabs.forEach((tab, i) => {
            const on = i === index;
            tab.setAttribute('aria-selected', String(on));
            tab.tabIndex = on ? 0 : -1;
            panels[i]?.classList.toggle('is-on', on);
        });
        if (focus) {
            tabs[index].focus();
            tabs[index].scrollIntoView({ block: 'nearest', inline: 'nearest' });
        }
    };

    tabs.forEach((tab, i) => {
        tab.addEventListener('click', () => select(i));
        tab.addEventListener('keydown', (e) => {
            const step = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[e.key];
            if (step) {
                e.preventDefault();
                select((i + step + tabs.length) % tabs.length, true);
            } else if (e.key === 'Home' || e.key === 'End') {
                e.preventDefault();
                select(e.key === 'Home' ? 0 : tabs.length - 1, true);
            }
        });
    });
}
