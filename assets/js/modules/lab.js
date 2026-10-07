/**
 * Lab bench: an accessible tablist whose panels each load their experiment
 * module only when first opened.
 */

import { $, $$ } from '@/core/util.js';

const loaders = {
    jwt:  () => import('@/modules/lab-jwt.js'),
    sql:  () => import('@/modules/lab-sql.js'),
    sort: () => import('@/modules/lab-sort.js'),
};

export function init(root) {
    const tabs = $$('.lab-tab', root);
    const loaded = new Set();

    const select = (tab, { focus = false } = {}) => {
        tabs.forEach((t) => {
            const on = t === tab;
            t.setAttribute('aria-selected', String(on));
            t.tabIndex = on ? 0 : -1;
            $('#' + t.getAttribute('aria-controls'), root).hidden = !on;
        });

        if (focus) tab.focus();

        const key = tab.dataset.lab;
        if (loaded.has(key) || !loaders[key]) return;
        loaded.add(key);

        const panel = $('#lab-' + key, root);
        loaders[key]()
            .then((m) => m.mount(panel))
            .catch((err) => {
                panel.innerHTML = '<p class="lab-loading">This experiment failed to load.</p>';
                console.error(err);
            });
    };

    tabs.forEach((tab) => tab.addEventListener('click', () => select(tab)));

    $('.lab-tabs', root)?.addEventListener('keydown', (e) => {
        const i = tabs.indexOf(document.activeElement);
        if (i === -1) return;

        const next = { ArrowRight: i + 1, ArrowLeft: i - 1, Home: 0, End: tabs.length - 1 }[e.key];
        if (next === undefined) return;

        e.preventDefault();
        select(tabs[(next + tabs.length) % tabs.length], { focus: true });
    });

    select(tabs[0]);
}
