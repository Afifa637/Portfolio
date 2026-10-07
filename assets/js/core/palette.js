/**
 * Command palette (Ctrl/Cmd + K).
 *
 * Every section, every project and every action in one fuzzy-searchable list.
 * Projects are matched on name *and* stack, so typing "spring" or "flutter"
 * finds builds by technology. Heavier modes (assistant, terminal, recruiter
 * view) are imported only when chosen.
 */

import { $, data, icon, esc, goTo, copyText, toast, track } from '@/core/util.js';
import { fuzzy, highlight } from '@/core/fuzzy.js';
import { open, close, isOpen } from '@/core/overlay.js';
import { toggleTheme } from '@/core/theme.js';

let items = [];
let shown = [];
let active = 0;

const SECTIONS = [
    ['about', 'How I think', 'route'],
    ['stack', 'Stack explorer', 'layers'],
    ['work', 'Featured work', 'layers'],
    ['hood', 'Under the hood', 'server'],
    ['archive', 'Project archive', 'search'],
    ['lab', 'Lab', 'flask'],
    ['activity', 'Development activity', 'zap'],
    ['journey', 'Journey', 'route'],
    ['resume', 'Résumé', 'file'],
    ['contact', 'Contact', 'mail'],
];

export const openAssistant = () => import('@/modules/assistant.js').then((m) => m.openAssistant());
export const openTerminal  = () => import('@/modules/terminal.js').then((m) => m.openTerminal());
export const openRecruiter = () => import('@/modules/recruiter.js').then((m) => m.openRecruiter());
export const openDiagnostics = () => import('@/modules/diagnostics.js').then((m) => m.toggleDiagnostics());

function build() {
    const d = data();
    const id = d.identity ?? {};

    const list = [];

    SECTIONS.forEach(([sid, label, ic]) => {
        list.push({ group: 'Go to', title: label, keys: `go ${sid} ${label}`, icon: ic, run: () => goTo(sid) });
    });

    list.push(
        { group: 'Actions', title: 'Ask Afifa a question', keys: 'assistant ai chat ask', icon: 'sparkle', run: openAssistant },
        { group: 'Actions', title: 'Open résumé', keys: 'resume cv open', icon: 'file', run: () => { location.href = id.resume; } },
        { group: 'Actions', title: 'Download CV (PDF)', keys: 'resume cv download pdf', icon: 'download', run: () => { track('resume downloaded'); location.href = id.cv; } },
        { group: 'Actions', title: 'View GitHub', keys: 'github code repositories', icon: 'github', meta: id.github?.replace('https://', ''), run: () => { track('github clicked'); window.open(id.github, '_blank', 'noopener'); } },
        { group: 'Actions', title: 'Copy email address', keys: 'email copy mail', icon: 'copy', meta: id.email, run: async () => toast((await copyText(id.email)) ? 'Email copied' : 'Could not copy') },
        { group: 'Actions', title: 'Contact me', keys: 'contact hire message email', icon: 'mail', run: () => goTo('contact') },
        { group: 'Actions', title: 'Toggle theme', keys: 'theme dark light mode', icon: 'sun', run: toggleTheme },
        { group: 'Actions', title: 'Show featured projects', keys: 'featured best projects work', icon: 'layers', run: () => goTo('work') },
        {
            group: 'Actions', title: 'Random project', keys: 'random surprise project', icon: 'shuffle',
            run: () => {
                const p = d.projects[Math.floor(Math.random() * d.projects.length)];
                if (p) location.href = p.url;
            },
        },
        { group: 'Modes', title: 'Recruiter mode', meta: '60-second profile', keys: 'recruiter summary quick overview hiring', icon: 'eye', run: openRecruiter },
        { group: 'Modes', title: 'Terminal mode', meta: '~', keys: 'terminal shell console cli', icon: 'terminal', run: openTerminal },
        { group: 'Modes', title: 'Developer mode', meta: 'diagnostics', keys: 'developer diagnostics debug fps', icon: 'command', run: openDiagnostics },
        { group: 'Shortcuts', title: 'Open this palette', meta: 'Ctrl K  ·  /', keys: 'shortcut keyboard', icon: 'command', run: () => {} },
        { group: 'Shortcuts', title: 'Open the terminal', meta: '~', keys: 'shortcut keyboard', icon: 'terminal', run: openTerminal },
        { group: 'Shortcuts', title: 'Close any panel', meta: 'Esc', keys: 'shortcut keyboard', icon: 'x', run: () => {} },
    );

    d.projects.forEach((p) => {
        list.push({
            group: 'Projects',
            title: p.title,
            meta: [p.subtitle, p.stack.slice(0, 3).join(' · ')].filter(Boolean).join(' — '),
            keys: [p.subtitle, p.category, ...p.stack].join(' '),
            icon: 'layers',
            run: () => { track('project opened', { project: p.slug }); location.href = p.url; },
        });
    });

    return list;
}

function render(query) {
    const list = $('#palette-list');
    const q = query.trim();

    // Easter egg: someone typed what we hoped they would.
    if (/^hire\s+afifa$/i.test(q)) {
        shown = [{
            group: '🎉', title: 'Excellent decision. Let’s talk.', meta: 'Opens the contact form',
            icon: 'send', run: () => goTo('contact'),
        }];
    } else if (q === '') {
        const featured = items.filter((i) => i.group === 'Projects' && data().bySlug[slugOf(i)]?.featured);
        shown = [
            ...items.filter((i) => i.group === 'Actions').slice(0, 6),
            ...items.filter((i) => i.group === 'Modes'),
            ...featured.slice(0, 4),
            ...items.filter((i) => i.group === 'Go to'),
        ];
        shown.forEach((i) => { i.hl = null; });
    } else {
        shown = items
            .map((item) => {
                const t = fuzzy(q, item.title);
                const k = fuzzy(q, item.keys || '');
                const score = Math.max(t ? t.score + 40 : -Infinity, k ? k.score * 0.75 : -Infinity);
                return score === -Infinity ? null : { ...item, score, hl: t?.indices ?? null };
            })
            .filter(Boolean)
            .sort((a, b) => b.score - a.score)
            .slice(0, 24);
    }

    active = 0;

    if (shown.length === 0) {
        list.innerHTML = `<p class="cmdk-empty">Nothing matches “${esc(q)}”. Try a technology like “spring” or “flutter”.</p>`;
        return;
    }

    let html = '';
    let group = null;

    // Keep results grouped, in the order groups first appear.
    const order = [...new Set(shown.map((i) => i.group))];
    shown = order.flatMap((g) => shown.filter((i) => i.group === g));

    shown.forEach((item, i) => {
        if (item.group !== group) {
            group = item.group;
            html += `<p class="cmdk-group" role="presentation">${esc(group)}</p>`;
        }

        html += `<button type="button" class="cmdk-item" role="option" id="cmdk-${i}" data-i="${i}" aria-selected="${i === 0}">`
            + icon(item.icon)
            + `<span class="t">${highlight(item.title, item.hl)}</span>`
            + (item.meta ? `<span class="m">${esc(item.meta)}</span>` : '')
            + '</button>';
    });

    list.innerHTML = html;
    $('#palette-input')?.setAttribute('aria-activedescendant', 'cmdk-0');
}

function slugOf(item) {
    return data().projects.find((p) => p.title === item.title)?.slug;
}

function move(delta) {
    if (!shown.length) return;

    active = (active + delta + shown.length) % shown.length;

    document.querySelectorAll('.cmdk-item').forEach((el) => {
        const on = Number(el.dataset.i) === active;
        el.setAttribute('aria-selected', String(on));
        if (on) el.scrollIntoView({ block: 'nearest' });
    });

    $('#palette-input')?.setAttribute('aria-activedescendant', `cmdk-${active}`);
}

function run(index) {
    const item = shown[index];
    if (!item) return;

    closePalette();
    // Let the overlay finish closing before the action moves the page.
    setTimeout(() => item.run(), 40);
}

export function openPalette(prefill = '') {
    const el = $('#palette');
    const input = $('#palette-input');
    if (!el || !input) return;

    if (items.length === 0) items = build();

    input.value = prefill;
    render(prefill);
    open(el, { focus: input });
}

export function closePalette() {
    close($('#palette'));
}

export function togglePalette() {
    isOpen($('#palette')) ? closePalette() : openPalette();
}

export function initPalette() {
    const input = $('#palette-input');
    const list = $('#palette-list');
    if (!input || !list) return;

    input.addEventListener('input', () => render(input.value));

    input.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown') { e.preventDefault(); move(1); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); move(-1); }
        else if (e.key === 'Enter') { e.preventDefault(); run(active); }
    });

    list.addEventListener('click', (e) => {
        const btn = e.target.closest('.cmdk-item');
        if (btn) run(Number(btn.dataset.i));
    });

    list.addEventListener('mousemove', (e) => {
        const btn = e.target.closest('.cmdk-item');
        if (btn && Number(btn.dataset.i) !== active) move(Number(btn.dataset.i) - active);
    });

    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-open]');
        if (!trigger) return;

        const target = trigger.dataset.open;
        if (target === 'palette') openPalette();
        if (target === 'ask') openAssistant();
        if (target === 'terminal') openTerminal();
        if (target === 'recruiter') openRecruiter();
    });
}
