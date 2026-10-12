/**
 * Archive: category filter, sort, instant fuzzy search and a live count.
 */

import { $, $$, reducedMotion } from '@/core/util.js';
import { fuzzy } from '@/core/fuzzy.js';

export function init(root) {
    const grid = $('#arc-grid', root);
    const cards = $$('.arc-card', grid);
    const count = $('#arc-count', root);
    const empty = $('#arc-empty', root);
    const search = $('#arc-q', root);
    const sort = $('#arc-sort', root);

    let category = 'all';
    let query = '';

    const apply = () => {
        const q = query.trim();
        const visible = [];

        cards.forEach((card) => {
            const inCategory = category === 'all' || card.dataset.category === category;
            let score = 0;

            if (q) {
                const m = fuzzy(q, card.dataset.search);
                score = m ? m.score : -1;
            }

            const show = inCategory && score >= 0;
            card.hidden = !show;
            if (show) visible.push({ card, score });
        });

        // Sort: search relevance wins while searching; otherwise the chosen order.
        const mode = sort?.value ?? 'order';
        const key = {
            order: (c) => Number(c.dataset.order),
            newest: (c) => -Number(c.dataset.year || 0) * 1000 + Number(c.dataset.order),
            scope: (c) => -Number(c.dataset.scope),
            tech: (c) => c.dataset.tech,
            name: (c) => c.dataset.name,
        }[mode];

        visible.sort((a, b) => {
            if (q && b.score !== a.score) return b.score - a.score;
            const ka = key(a.card);
            const kb = key(b.card);
            return typeof ka === 'number' ? ka - kb : String(ka).localeCompare(String(kb));
        });

        visible.forEach(({ card }, i) => {
            grid.insertBefore(card, empty);
            if (!reducedMotion.matches) {
                card.classList.remove('is-entering');
                void card.offsetWidth; // restart the entry animation
                card.style.setProperty('--delay', `${Math.min(i, 10) * 30}ms`);
                card.classList.add('is-entering');
            }
        });

        empty.hidden = visible.length > 0;
        if (count) count.innerHTML = `Showing <b>${visible.length}</b> of ${cards.length} builds`;
    };

    $$('.arc-filter', root).forEach((btn) => {
        btn.addEventListener('click', () => {
            category = btn.dataset.filter;
            $$('.arc-filter', root).forEach((b) => b.setAttribute('aria-pressed', String(b === btn)));
            apply();
        });
    });

    let timer;
    search?.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => { query = search.value; apply(); }, 90);
    });

    search?.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && search.value) {
            e.stopPropagation();
            search.value = '';
            query = '';
            apply();
        }
    });

    sort?.addEventListener('change', apply);
}
