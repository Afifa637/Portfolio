/**
 * Terminal mode (press ~).
 *
 * An optional, keyboard-first way through the portfolio, with history (↑/↓)
 * and tab completion. Every command reads the same content payload as the
 * rest of the site.
 */

import { $, data, esc, goTo } from '@/core/util.js';
import { open, close } from '@/core/overlay.js';
import { toggleTheme } from '@/core/theme.js';
import { answer } from '@/modules/kb.js';

let mounted = false;
const history = [];
let cursor = 0;

const PROMPT = 'afifa@portfolio:~$';

function commands() {
    const d = data();
    const id = d.identity;

    return {
        help: {
            desc: 'list commands',
            run: () => Object.entries(commands())
                .filter(([, c]) => !c.hidden)
                .map(([name, c]) => `  <span class="k">${name.padEnd(14)}</span><span class="mu">${c.desc}</span>`)
                .join('\n'),
        },
        whoami: {
            desc: 'who is this',
            run: () => `<span class="ok">${esc(id.name)}</span>\nComputer Science & Engineering student, KUET\n${esc(id.title)} — backend-focused\n${esc(id.availability)}.`,
        },
        about: { desc: 'the short version', run: () => esc(id.pitch) },
        skills: {
            desc: 'stack by group',
            run: () => d.skills.map((g) => `<span class="k">${esc(g.group.padEnd(11))}</span>${esc(g.items.map((i) => i.name).join(', '))}`).join('\n'),
        },
        projects: {
            desc: 'list builds  (open &lt;slug&gt;)',
            run: () => d.projects.map((p) => `  <span class="p">${esc(p.slug.padEnd(20))}</span>${esc(p.title)}${p.featured ? ' <span class="ok">★</span>' : ''}`).join('\n')
                + '\n\n<span class="mu">open &lt;slug&gt; to read a case study</span>',
        },
        ls: { desc: 'alias for projects', hidden: true, run: () => commands().projects.run() },
        open: {
            desc: 'open a case study',
            run: (args) => {
                const slug = (args[0] || '').toLowerCase();
                const p = d.projects.find((x) => x.slug === slug || x.title.toLowerCase() === slug);
                if (!p) return `<span class="er">no such project: ${esc(slug || '(none)')}</span> — try <span class="k">projects</span>`;
                setTimeout(() => { location.href = p.url; }, 350);
                return `<span class="ok">opening</span> ${esc(p.title)}…`;
            },
        },
        cat: {
            desc: 'cat &lt;slug&gt; — case study summary',
            run: (args) => {
                const p = d.bySlug[(args[0] || '').toLowerCase()];
                if (!p) return '<span class="er">usage: cat &lt;slug&gt;</span>';
                return `<span class="ok"># ${esc(p.title)}</span>  <span class="mu">${esc(p.year)} · ${esc(p.category)}</span>\n${esc(p.summary)}\n\n<span class="k">stack</span>  ${esc(p.stack.join(', '))}`
                    + (p.architecture.length ? `\n<span class="k">layers</span> ${esc(p.architecture.map((l) => l.layer).join(' → '))}` : '');
            },
        },
        education: {
            desc: 'degrees and grades',
            run: () => d.education.map((e) => `<span class="k">${esc(e.start)}–${esc(e.end)}</span>  ${esc(e.degree)}\n            ${esc(e.institution)}${e.grade ? ' · ' + esc(e.grade) : ''}`).join('\n'),
        },
        resume: {
            desc: 'open the résumé',
            run: () => { setTimeout(() => { location.href = id.resume; }, 350); return 'opening résumé…'; },
        },
        contact: {
            desc: 'how to reach me',
            run: () => `email    <a href="mailto:${esc(id.email)}">${esc(id.email)}</a>\ngithub   <a href="${esc(id.github)}" target="_blank" rel="noopener">${esc(id.github.replace('https://', ''))}</a>`
                + d.socials.filter((s) => s.icon === 'linkedin').map((s) => `\nlinkedin <a href="${esc(s.url)}" target="_blank" rel="noopener">profile</a>`).join(''),
        },
        github: {
            desc: 'open GitHub',
            run: () => { window.open(id.github, '_blank', 'noopener'); return 'opened GitHub in a new tab.'; },
        },
        ask: { desc: 'ask &lt;question&gt;', run: (args) => (args.length ? answer(args.join(' ')).html.replace(/<\/?(p|ul|li)>/g, (t) => (t === '<li>' ? '\n  • ' : t.startsWith('</p') ? '\n' : '')) : '<span class="er">usage: ask &lt;question&gt;</span>') },
        goto: {
            desc: 'goto &lt;section&gt;',
            run: (args) => {
                const target = args[0] || '';
                if (!document.getElementById(target)) return '<span class="er">sections:</span> about stack work hood archive lab activity journey resume contact';
                setTimeout(() => { closeTerminal(); goTo(target); }, 200);
                return `jumping to ${esc(target)}…`;
            },
        },
        theme: { desc: 'toggle light / dark', run: () => { toggleTheme(); return `theme: ${document.documentElement.dataset.theme}`; } },
        date: { desc: 'local time in Khulna', run: () => new Date().toLocaleString('en-GB', { timeZone: d.status?.timezone || 'Asia/Dhaka' }) },
        clear: { desc: 'clear the screen', run: () => null },
        exit: { desc: 'close the terminal', run: () => { setTimeout(closeTerminal, 50); return 'bye.'; } },
        sudo: {
            hidden: true,
            run: (args) => (args.join(' ').toLowerCase() === 'hire afifa'
                ? '<span class="ok">[sudo] password for recruiter: ********</span>\nAccess granted. Opening a direct line…\n<span class="mu">(this is the part where you scroll to the contact form)</span>'
                : `<span class="er">${esc(args.join(' ') || 'sudo')}: permission denied.</span> Nice try.`),
        },
        rm: { hidden: true, run: () => '<span class="er">rm: refusing to delete a portfolio that took this long to build.</span>' },
        hire: { hidden: true, run: () => 'did you mean <span class="k">sudo hire afifa</span>?' },
    };
}

function mount() {
    const el = $('#terminal');
    el.innerHTML = `
        <div class="overlay-panel term-panel">
            <div class="term-bar"><i></i><i></i><i></i><span>afifa@portfolio — zsh</span><button type="button" data-term-close aria-label="Close terminal">esc</button></div>
            <div class="term-out" id="term-out" aria-live="polite"></div>
            <form class="term-in" id="term-form" autocomplete="off">
                <label for="term-input">${PROMPT}</label>
                <input type="text" id="term-input" spellcheck="false" autocapitalize="off" aria-label="Terminal command">
            </form>
        </div>`;

    const out = $('#term-out', el);
    const input = $('#term-input', el);

    const print = (html) => {
        out.insertAdjacentHTML('beforeend', html + '\n');
        out.scrollTop = out.scrollHeight;
    };

    print('<span class="mu">Portfolio shell. Type</span> <span class="k">help</span> <span class="mu">for commands, Tab to complete, ↑ for history.</span>\n');

    $('#term-form', el).addEventListener('submit', (e) => {
        e.preventDefault();

        const line = input.value.trim();
        input.value = '';
        print(`<span class="p">${PROMPT}</span> ${esc(line)}`);

        if (!line) return;

        history.push(line);
        cursor = history.length;

        const [name, ...args] = line.split(/\s+/);
        const cmd = commands()[name.toLowerCase()];

        if (!cmd) {
            print(`<span class="er">command not found: ${esc(name)}</span> — type <span class="k">help</span>`);
            return;
        }

        const result = cmd.run(args);

        if (result === null) {
            out.innerHTML = '';
            return;
        }

        print(result);
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowUp' && history.length) {
            e.preventDefault();
            cursor = Math.max(0, cursor - 1);
            input.value = history[cursor] ?? '';
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            cursor = Math.min(history.length, cursor + 1);
            input.value = history[cursor] ?? '';
        } else if (e.key === 'Tab') {
            e.preventDefault();
            const [name, arg] = input.value.split(/\s+/);
            const pool = arg !== undefined ? data().projects.map((p) => p.slug) : Object.keys(commands()).filter((k) => !commands()[k].hidden);
            const partial = arg !== undefined ? arg : name;
            const match = pool.filter((x) => x.startsWith(partial.toLowerCase()));

            if (match.length === 1) {
                input.value = arg !== undefined ? `${name} ${match[0]}` : match[0] + ' ';
            } else if (match.length > 1) {
                print(`<span class="mu">${match.join('  ')}</span>`);
            }
        }
    });

    el.querySelector('[data-term-close]').addEventListener('click', closeTerminal);
    el.addEventListener('click', (e) => { if (e.target.closest('.term-out')) input.focus(); });

    mounted = true;
}

export function openTerminal() {
    if (!mounted) mount();
    open($('#terminal'), { focus: $('#term-input') });
}

export function closeTerminal() {
    close($('#terminal'));
}
