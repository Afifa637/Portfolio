/**
 * Under the hood (system tabs + request runs) and Build Mode.
 */

import { $, $$, data, esc } from '@/core/util.js';
import { createFlow, renderLayers } from '@/modules/flow.js';

export function init(root) {
    const d = data();
    initHood(root, d);
    initBuild(root, d);
}

function initHood(root, d) {
    const list = $('#hood-arch', root);
    if (!list) return;

    const systems = d.projects.filter((p) => p.demo_request && p.architecture.length >= 3);
    let current = systems[0];

    const flow = createFlow({
        body: $('.hood-body', root),
        list,
        packet: $('#hood-packet', root),
        log: $('#hood-log', root),
        insp: $('#hood-insp', root),
        getLayers: () => current.architecture,
        getRequest: () => current.demo_request,
    });

    const load = (slug) => {
        if (flow.running) return;

        current = systems.find((s) => s.slug === slug) ?? current;

        $$('.hood-tab', root).forEach((t) => t.setAttribute('aria-selected', String(t.dataset.system === slug)));

        const [method, path] = current.demo_request.split(' ');
        $('#hood-req', root).innerHTML =
            `<span class="method">${esc(method)}</span><span class="path">${esc(path)}</span><span class="hint">${esc(current.title)}</span>`;

        renderLayers(list, current.architecture);
        flow.select(0);
        $('#hood-log', root).innerHTML = '<p class="empty">$ awaiting request — press “Run request”.</p>';
    };

    $$('.hood-tab', root).forEach((tab) => tab.addEventListener('click', () => load(tab.dataset.system)));

    // Arrow keys move between system tabs, as a tablist should.
    $('.hood-tabs', root)?.addEventListener('keydown', (e) => {
        if (!['ArrowLeft', 'ArrowRight'].includes(e.key)) return;
        const tabs = $$('.hood-tab', root);
        const i = tabs.indexOf(document.activeElement);
        const next = tabs[(i + (e.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length];
        next?.focus();
        next?.click();
    });

    const runBtn = $('#hood-run', root);
    const denyBtn = $('#hood-deny', root);

    const guard = async (denied) => {
        runBtn.disabled = denyBtn.disabled = true;
        await flow.run({ denied });
        runBtn.disabled = denyBtn.disabled = false;
    };

    runBtn?.addEventListener('click', () => guard(false));
    denyBtn?.addEventListener('click', () => guard(true));
}

/* ============================================================ build mode */

function tokensOf(project) {
    const set = new Set();

    const add = (t) => {
        const k = t.trim().toLowerCase();
        if (!k) return;
        set.add(k);
        // "Spring Boot 3" is Spring Boot — drop a trailing version number.
        set.add(k.replace(/\s+\d+(\.\d+)*$/, ''));
        // "React Native & Expo" names two technologies.
        k.split(/\s*&\s*/).forEach((part) => set.add(part));
        k.split(/\s+/).forEach((w) => set.add(w));
    };

    project.stack.forEach(add);
    project.architecture.forEach((l) => l.tech.split(',').forEach(add));

    return set;
}

/** Does a project use this choice? Matched on whole names, with a few aliases. */
function uses(tokens, choice) {
    const c = choice.toLowerCase();

    const aliases = {
        'cloud firestore': ['cloud firestore', 'firestore', 'firebase firestore', 'realtime database'],
        'firebase': ['firebase', 'firebase auth', 'cloud firestore', 'firestore'],
        'laravel middleware': ['laravel middleware', 'laravel'],
        'spring security': ['spring security'],
        'jwt': ['jwt'],
        'node.js': ['node.js'],
        'android': ['android'],
    };

    return (aliases[c] ?? [c]).some((a) => tokens.has(a));
}

function initBuild(root, d) {
    const form = $('#build-form', root);
    const list = $('#build-arch', root);
    const text = $('#build-match-text', root);
    if (!form || !list || !text) return;

    const projects = d.projects.map((p) => ({ p, tokens: tokensOf(p) }));

    const update = () => {
        const f = new FormData(form);
        const pick = {
            client: f.get('client'),
            backend: f.get('backend'),
            auth: f.get('auth'),
            database: f.get('database'),
        };

        const managed = pick.backend === 'Firebase';
        const serverRendered = ['Thymeleaf', 'Blade'].includes(pick.client);

        const layers = [
            { layer: serverRendered ? 'Server-rendered pages' : 'Client', tech: pick.client, role: '' },
            { layer: managed ? 'SDK calls' : (serverRendered ? 'HTTP routes' : 'REST API'), tech: managed ? 'Firebase SDK' : 'HTTP · JSON', role: '' },
            { layer: managed ? 'Managed backend' : 'Backend', tech: pick.backend, role: '' },
            { layer: 'Authentication', tech: pick.auth, role: '' },
            { layer: 'Database', tech: pick.database, role: '' },
        ];

        renderLayers(list, layers, { interactive: false });

        // Closest real build: the project using the most chosen technologies.
        const choices = Object.values(pick);
        const ranked = projects
            .map(({ p, tokens }) => ({ p, hits: choices.filter((c) => uses(tokens, c)) }))
            .filter((r) => r.hits.length > 0)
            .sort((a, b) => b.hits.length - a.hits.length);

        const best = ranked[0];

        // A few pairings that do not belong together are worth saying out loud.
        const notes = [];
        if (managed && ['PostgreSQL', 'MySQL'].includes(pick.database)) notes.push('Firebase is usually paired with Firestore rather than a SQL database.');
        if (pick.auth === 'Spring Security' && pick.backend !== 'Spring Boot') notes.push('Spring Security belongs with a Spring Boot backend.');
        if (pick.auth === 'Laravel middleware' && pick.backend !== 'Laravel') notes.push('Laravel middleware belongs with a Laravel backend.');

        text.innerHTML = best
            ? `<a class="ulink" href="${esc(best.p.url)}">${esc(best.p.title)}</a> — uses ${best.hits.map(esc).join(', ')} (${best.hits.length} of ${choices.length}).`
              + (ranked[1] && ranked[1].hits.length === best.hits.length ? ` Also close: <a class="ulink" href="${esc(ranked[1].p.url)}">${esc(ranked[1].p.title)}</a>.` : '')
            : 'Nothing built with this combination yet — which is a good reason to build it.';

        if (notes.length) text.innerHTML += `<br><span class="t-3">${notes.map(esc).join(' ')}</span>`;
    };

    form.addEventListener('change', update);
    update();
}
