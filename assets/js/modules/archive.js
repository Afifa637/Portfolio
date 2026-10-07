/**
 * Archive: category filter, sort, instant fuzzy search, a live count, and the
 * tech inspector that reads the hovered or focused build's architecture.
 */

import { $, $$, data, esc, reducedMotion, finePointer } from '@/core/util.js';
import { fuzzy } from '@/core/fuzzy.js';

export function init(root) {
    const d = data();
    const grid = $('#arc-grid', root);
    const cards = $$('.arc-card', grid);
    const count = $('#arc-count', root);
    const empty = $('#arc-empty', root);
    const search = $('#arc-q', root);
    const sort = $('#arc-sort', root);
    const insp = $('#arc-insp', root);

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

    /* --- inspector --------------------------------------------------- */

    if (!insp) return;

    const inspect = (card) => {
        const p = d.bySlug[card.dataset.slug];
        if (!p) return;

        const layers = p.architecture.length
            ? `<dl class="kv">${p.architecture.map((l) => `<div><dt>${esc(l.layer)}</dt><dd>${esc(l.tech)}</dd></div>`).join('')}</dl>`
            : `<div class="tags">${p.stack.map((t) => `<span class="tag">${esc(t)}</span>`).join('')}</div>`;

        insp.innerHTML =
            '<div class="panel-head"><span class="label">tech.inspector</span>'
            + `<span class="label t-3">${esc(p.year)}</span></div>`
            + '<div class="insp-body">'
            + `<div><p class="label" style="color:var(--amber)">${esc(d.categories?.[p.category] ?? p.category)}</p>`
            + `<h3 style="margin-top:.4rem">${esc(p.title)}</h3>`
            + (p.subtitle ? `<p class="t-2" style="font-size:.875rem;margin-top:.3rem">${esc(p.subtitle)}</p>` : '')
            + '</div>'
            + `<div><p class="label" style="margin-bottom:.4rem">${p.architecture.length ? 'Architecture' : 'Stack'}</p>${layers}</div>`
            + (p.role ? `<dl class="kv"><div><dt>Role</dt><dd>${esc(p.role)}</dd></div></dl>` : '')
            + `<a class="arrow-link" href="${esc(p.url)}">Open case study <span aria-hidden="true">→</span></a>`
            + '</div>';
    };

    cards.forEach((card) => {
        if (finePointer.matches) card.addEventListener('pointerenter', () => inspect(card));
        card.addEventListener('focusin', () => inspect(card));
    });
}
