/**
 * Stack explorer: selecting a technology rewrites the inspector with the
 * projects that use it and what it was built alongside — all from the
 * computed payload, never from typed-in claims.
 */

import { $, $$, data, esc } from '@/core/util.js';

export function init(root) {
    const d = data();
    const body = $('#stack-insp-body', root);
    const groupLabel = $('#stack-insp-group', root);
    const buttons = $$('.skill', root);

    const index = {};
    d.skills.forEach((g) => g.items.forEach((item) => { index[item.key] = { ...item, group: g.group }; }));

    const select = (key, { scroll = false } = {}) => {
        const item = index[key];
        if (!item || !body) return;

        buttons.forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.skill === key)));
        if (groupLabel) groupLabel.textContent = item.group;

        const projects = item.projects.map((s) => d.bySlug[s]).filter(Boolean);
        const n = projects.length;

        body.innerHTML =
            `<h3 class="insp-title">${esc(item.name)}</h3>`
            + `<p class="insp-used"><b>${n}</b><span>${n === 1 ? 'project uses it' : 'projects use it'}</span></p>`
            + (n
                ? `<ul class="insp-projects" role="list">${projects.map((p) =>
                    `<li><a href="${esc(p.url)}">${esc(p.title)}<span>${esc(p.year)}</span></a></li>`).join('')}</ul>`
                : '<p class="insp-empty">Part of my toolkit from coursework and smaller builds — not yet in a featured project here.</p>')
            + (item.related.length
                ? `<div><p class="label" style="margin-bottom:.6rem">Used alongside</p><div class="tags">${item.related.map((r) =>
                    `<button type="button" class="tag" data-jump="${esc(r.toLowerCase())}">${esc(r)}</button>`).join('')}</div></div>`
                : '');

        if (scroll) root.querySelector(`.skill[data-skill="${CSS.escape(key)}"]`)?.scrollIntoView({ block: 'nearest' });
    };

    root.addEventListener('click', (e) => {
        const btn = e.target.closest('.skill');
        if (btn) { select(btn.dataset.skill); return; }

        // "Used alongside" chips jump to that technology when it is in the stack.
        const jump = e.target.closest('[data-jump]');
        if (jump) {
            const key = Object.keys(index).find((k) => k === jump.dataset.jump || index[k].name.toLowerCase() === jump.dataset.jump);
            if (key) select(key, { scroll: true });
        }
    });

    const fromGraph = (key) => {
        const match = index[key] ? key : Object.keys(index).find((k) => k.startsWith(key) || index[k].name.toLowerCase() === key);
        if (match) select(match, { scroll: true });
    };

    document.addEventListener('afifa:select-skill', (e) => fromGraph(e.detail.key));

    if (window.__pendingSkill) {
        fromGraph(window.__pendingSkill);
        window.__pendingSkill = null;
    }
}
