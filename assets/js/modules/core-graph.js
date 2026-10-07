/**
 * Hero: the rotating role slot and the System Core graph.
 *
 * Focusing a node (hover, keyboard, or tap) lights the technologies it was
 * actually used alongside and opens a capability card listing the projects.
 * Selecting it jumps to the stack explorer with that technology chosen.
 * The graph shifts very slightly with the cursor for depth — no idle motion.
 */

import { $, $$, data, esc, reducedMotion, finePointer, goTo, lerp } from '@/core/util.js';

const SKILL_KEYS = { 'C / C++': 'c++', 'AI': 'game ai', 'REST APIs': 'rest apis', 'Spring Boot': 'spring boot' };

export function init(root) {
    rotateRoles();
    initGraph(root);
}

function rotateRoles() {
    const track = $('#role-slot');
    if (!track || reducedMotion.matches) return;

    const count = track.children.length;
    if (count < 2) return;

    let i = 0;

    setInterval(() => {
        if (document.hidden) return;
        i = (i + 1) % count;
        track.style.setProperty('--slot', i);
    }, 2600);
}

function initGraph(root) {
    const svg = $('#core-svg', root);
    const card = $('#core-card', root);
    if (!svg || !card) return;

    const d = data();
    const nodes = Object.fromEntries((d.graph?.nodes ?? []).map((n) => [n.id, n]));
    const edges = $$('.edge', svg);
    const els = $$('.node', svg);

    // Adjacency from the computed edges (not spokes).
    const adjacent = {};
    (d.graph?.edges ?? []).forEach(([a, b]) => {
        (adjacent[a] ??= new Set()).add(b);
        (adjacent[b] ??= new Set()).add(a);
    });

    let focused = null;

    const show = (id) => {
        const node = nodes[id];
        if (!node || focused === id) return;
        focused = id;

        const linked = adjacent[id] ?? new Set();

        svg.classList.add('is-focus');
        els.forEach((el) => {
            el.classList.toggle('is-on', el.dataset.id === id);
            el.classList.toggle('is-linked', linked.has(el.dataset.id));
        });
        edges.forEach((edge) => {
            const on = edge.dataset.a === id || edge.dataset.b === id;
            edge.classList.toggle('is-on', on);
        });

        const projects = node.projects.map((slug) => d.bySlug[slug]).filter(Boolean);
        const evidence = node.repos
            ? `In every one of my ${node.repos} public repositories.`
            : projects.length
                ? `Used in ${projects.length} project${projects.length === 1 ? '' : 's'}.`
                : 'Listed in my stack; no featured project uses it yet.';

        card.innerHTML =
            `<p class="label">${esc(node.group)}</p>`
            + `<h3>${esc(node.label)}</h3>`
            + `<p>${esc(evidence)}${linked.size ? ` Used alongside ${[...linked].map((x) => esc(nodes[x]?.label)).join(', ')}.` : ''}</p>`
            + (projects.length
                ? `<div class="tags">${projects.slice(0, 4).map((p) => `<a class="tag" href="${esc(p.url)}">${esc(p.title)}</a>`).join('')}</div>`
                : '');
        card.classList.add('is-shown');
    };

    const hide = () => {
        focused = null;
        svg.classList.remove('is-focus');
        els.forEach((el) => el.classList.remove('is-on', 'is-linked'));
        edges.forEach((edge) => edge.classList.remove('is-on'));
        card.classList.remove('is-shown');
    };

    els.forEach((el) => {
        el.addEventListener('pointerenter', () => show(el.dataset.id));
        el.addEventListener('focus', () => show(el.dataset.id));

        const select = () => {
            const node = nodes[el.dataset.id];
            const key = SKILL_KEYS[node?.label] ?? node?.label.toLowerCase();
            window.__pendingSkill = key;
            document.dispatchEvent(new CustomEvent('afifa:select-skill', { detail: { key } }));
            goTo('stack');
        };

        el.addEventListener('click', select);
        el.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                select();
            }
        });
    });

    root.addEventListener('pointerleave', hide);
    svg.addEventListener('focusout', (e) => {
        if (!svg.contains(e.relatedTarget) && !card.contains(e.relatedTarget)) hide();
    });

    // Keep the card open while the pointer is on it, so its links are reachable.
    card.addEventListener('pointerenter', () => card.classList.add('is-shown'));

    /* --- subtle cursor depth ------------------------------------------ */

    if (reducedMotion.matches || !finePointer.matches) return;

    const layers = [
        [$('.nodes', svg), 14],
        [$('.edges', svg), 10],
        [$('.center', svg), 5],
    ].filter(([el]) => el);

    let tx = 0;
    let ty = 0;
    let cx = 0;
    let cy = 0;
    let running = false;

    const loop = () => {
        cx = lerp(cx, tx, 0.08);
        cy = lerp(cy, ty, 0.08);

        layers.forEach(([el, depth]) => el.setAttribute('transform', `translate(${(cx * depth).toFixed(2)} ${(cy * depth).toFixed(2)})`));

        if (Math.abs(cx - tx) > 0.001 || Math.abs(cy - ty) > 0.001) {
            requestAnimationFrame(loop);
        } else {
            running = false;
        }
    };

    root.addEventListener('pointermove', (e) => {
        const r = root.getBoundingClientRect();
        tx = ((e.clientX - r.left) / r.width - 0.5) * 2;
        ty = ((e.clientY - r.top) / r.height - 0.5) * 2;

        if (!running) {
            running = true;
            requestAnimationFrame(loop);
        }
    });

    root.addEventListener('pointerleave', () => {
        tx = 0;
        ty = 0;
        if (!running) {
            running = true;
            requestAnimationFrame(loop);
        }
    });
}
