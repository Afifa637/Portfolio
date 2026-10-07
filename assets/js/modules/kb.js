/**
 * Knowledge base behind "Ask Afifa".
 *
 * Retrieval, not generation: every answer is assembled from the portfolio's
 * own content and cites where it came from. There is no external AI service,
 * so nothing can be invented.
 *
 *   1. Intent routing   — common questions (contact, education, "best
 *                          project", a named technology…) get a composed answer
 *                          built from structured data.
 *   2. BM25 retrieval   — everything else is ranked over an index of every
 *                          project, skill, principle, education entry and
 *                          journey stage, with field boosts for titles and
 *                          stacks, and answered with the best passages.
 *   3. Honest fallback  — if nothing scores, it says so and suggests questions
 *                          it can answer.
 */

import { data, esc } from '@/core/util.js';

const STOP = new Set(('a an the and or of to in on for with is are was were be been it its this that these those '
    + 'what which who whom whose how why when where does do did has have had can could should would will '
    + 'she her he his they them their afifa afifas sultana me my i you your show tell about any some '
    + 'project projects use uses used using built build experience know there').split(' '));

/* ------------------------------------------------------------ text --- */

export function tokens(text) {
    return String(text).toLowerCase()
        .replace(/c\+\+/g, 'cplusplus')
        .replace(/node\.js/g, 'nodejs')
        .split(/[^a-z0-9#]+/)
        .filter((t) => t.length > 1 && !STOP.has(t))
        .map(stem);
}

function stem(t) {
    if (t.length > 5 && t.endsWith('ing')) return t.slice(0, -3);
    if (t.length > 4 && t.endsWith('ies')) return t.slice(0, -3) + 'y';
    if (t.length > 4 && t.endsWith('ed')) return t.slice(0, -2);
    if (t.length > 3 && t.endsWith('s') && !t.endsWith('ss')) return t.slice(0, -1);
    return t;
}

const list = (items) => items.length > 1
    ? items.slice(0, -1).join(', ') + ' and ' + items[items.length - 1]
    : items[0] ?? '';

const link = (p) => `<a href="${esc(p.url)}">${esc(p.title)}</a>`;

/* ----------------------------------------------------------- index --- */

let index = null;

function buildIndex() {
    const d = data();
    const docs = [];

    d.projects.forEach((p) => docs.push({
        kind: 'project', ref: p, href: p.url, label: p.title,
        fields: {
            title: `${p.title} ${p.subtitle}`,
            stack: [...p.stack, ...p.architecture.map((l) => l.tech)].join(' '),
            body: [p.summary, p.problem, p.features.join(' '), p.challenges, p.outcome, p.learned, p.decisions, p.security, p.category].join(' '),
        },
        snippet: p.summary,
    }));

    d.skills.forEach((g) => docs.push({
        kind: 'skills', href: '#stack', label: `${g.group} skills`,
        fields: { title: g.group, stack: g.items.map((i) => i.name).join(' '), body: g.note },
        snippet: `${g.group}: ${g.items.map((i) => i.name).join(', ')}.`,
    }));

    (d.principles ?? []).forEach((p) => docs.push({
        kind: 'principle', href: '#about', label: 'How I think',
        fields: { title: p.title, stack: '', body: p.body }, snippet: `${p.title} — ${p.body}`,
    }));

    (d.education ?? []).forEach((e) => docs.push({
        kind: 'education', href: '#resume', label: 'Education',
        fields: { title: `${e.degree} ${e.institution}`, stack: '', body: `${e.grade} ${e.grade_note} ${e.detail} ${e.location} ${e.start} ${e.end}` },
        snippet: `${e.degree}, ${e.institution} (${e.start}–${e.end})${e.grade ? ' — ' + e.grade : ''}.`,
    }));

    (d.journey ?? []).forEach((j) => docs.push({
        kind: 'journey', href: '#journey', label: `Journey · ${j.title}`,
        fields: { title: j.title, stack: j.tech.join(' '), body: j.body }, snippet: `${j.period}: ${j.title} — ${j.body}`,
    }));

    (d.activities ?? []).forEach((a) => docs.push({
        kind: 'activity', href: '#resume', label: 'Activities',
        fields: { title: a.org, stack: '', body: `${a.role} ${a.detail}` }, snippet: `${a.role}, ${a.org}. ${a.detail}`,
    }));

    (d.services ?? []).forEach((s) => docs.push({
        kind: 'service', href: '#contact', label: 'What I can build',
        fields: { title: s.title, stack: '', body: s.body }, snippet: `${s.title}: ${s.body}`,
    }));

    // Pre-tokenise and collect statistics for BM25.
    const BOOST = { title: 3, stack: 2, body: 1 };
    const df = new Map();

    docs.forEach((doc) => {
        doc.tf = new Map();
        doc.len = 0;

        Object.entries(doc.fields).forEach(([field, text]) => {
            tokens(text).forEach((t) => {
                doc.tf.set(t, (doc.tf.get(t) ?? 0) + BOOST[field]);
                doc.len += BOOST[field];
            });
        });

        doc.tf.forEach((_, t) => df.set(t, (df.get(t) ?? 0) + 1));
    });

    const avg = docs.reduce((s, d) => s + d.len, 0) / Math.max(docs.length, 1);

    return { docs, df, avg, n: docs.length };
}

/** Okapi BM25 — the ranking function behind most search engines. */
export function search(query, limit = 3) {
    index ??= buildIndex();

    const k1 = 1.4;
    const b = 0.72;
    const q = [...new Set(tokens(query))];

    return index.docs
        .map((doc) => {
            let score = 0;

            q.forEach((t) => {
                const f = doc.tf.get(t);
                if (!f) return;
                const idf = Math.log(1 + (index.n - index.df.get(t) + 0.5) / (index.df.get(t) + 0.5));
                score += idf * (f * (k1 + 1)) / (f + k1 * (1 - b + b * doc.len / index.avg));
            });

            return { doc, score };
        })
        .filter((r) => r.score > 0.8)
        .sort((a, b) => b.score - a.score)
        .slice(0, limit);
}

/* ------------------------------------------------- technology lookup --- */

function findTechnology(query) {
    const d = data();
    const q = ` ${query.toLowerCase().replace(/[?!.,]/g, ' ')} `;

    // Longest name first, so "spring security" wins over "spring".
    const names = new Map();
    d.skills.forEach((g) => g.items.forEach((i) => names.set(i.name.toLowerCase(), i)));
    d.projects.forEach((p) => p.stack.forEach((t) => {
        if (!names.has(t.toLowerCase())) names.set(t.toLowerCase(), { name: t, key: t.toLowerCase(), projects: null, related: [] });
    }));

    const aliases = { spring: 'spring boot', postgres: 'postgresql', js: 'javascript', ts: 'typescript', 'c plus plus': 'c++', firestore: 'cloud firestore' };

    const candidates = [...names.keys(), ...Object.keys(aliases)].sort((a, b) => b.length - a.length);

    for (const name of candidates) {
        const plain = name.replace(/\s*\(.*?\)/, '').replace(/ \d+$/, '');
        if (plain.length < 2) continue;

        const escaped = plain.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        if (!new RegExp(`[^a-z0-9+#]${escaped}[^a-z0-9+#]`).test(q)) continue;

        const resolved = aliases[name] ?? name;
        const item = names.get(resolved) ?? names.get(name);
        if (!item) continue;

        const projects = (item.projects
            ? item.projects.map((s) => d.bySlug[s])
            : d.projects.filter((p) => p.stack.some((t) => t.toLowerCase() === resolved))
        ).filter(Boolean);

        // Stack names like "Spring Boot 3" should also count for "spring boot" —
        // but only as a whole word: a substring test let "java" match inside
        // "JavaScript" and overstated Java to seven projects instead of three.
        const whole = new RegExp(`(^|[^a-z0-9+#])${escaped}([^a-z0-9+#]|$)`);
        const extra = d.projects.filter((p) => !projects.includes(p)
            && [...p.stack, ...p.architecture.flatMap((l) => l.tech.split(','))]
                .some((t) => whole.test(t.trim().toLowerCase())));

        return { name: item.name, projects: [...projects, ...extra], related: item.related ?? [] };
    }

    return null;
}

/* ---------------------------------------------------------- intents --- */

const CATEGORY_WORDS = [
    [/\b(mobile|android|ios|flutter apps?|apps?)\b/, 'mobile', 'mobile applications'],
    [/\b(game|games|ai\b|artificial|agent)/, 'ai', 'AI and game projects'],
    [/\b(embedded|hardware|iot|compiler|systems?|low.level)\b/, 'systems', 'systems and embedded work'],
    [/\b(full.?stack|web app|website)/, 'fullstack', 'full-stack builds'],
    [/\b(frontend|front.end|ui)\b/, 'frontend', 'frontend work'],
];

export function answer(raw) {
    const d = data();
    const id = d.identity ?? {};
    const q = raw.toLowerCase().trim();
    const featured = d.projects.filter((p) => p.featured);

    const done = (html, sources = []) => ({ html, sources });
    const projectSources = (ps) => ps.slice(0, 4).map((p) => ({ label: p.title, href: p.url }));

    if (/^(hi|hello|hey|salam|assalamu)/.test(q) && q.length < 24) {
        return done(`<p>Hi! I can answer questions about Afifa’s projects, stack, education and availability — all from this portfolio. Try one of the suggestions below.</p>`);
    }

    if (/sudo/.test(q) && /hire/.test(q)) {
        return done('<p><strong>Permission granted.</strong> The fastest route is the contact form — she replies within a day or two.</p>', [{ label: 'Contact', href: '#contact' }]);
    }

    /* Contact */
    if (/\b(contact|e-?mail|reach|get in touch|linkedin|message her|talk to)\b/.test(q)) {
        const li = d.socials.find((s) => s.icon === 'linkedin');
        return done(
            `<p>The quickest way is email: <a href="mailto:${esc(id.email)}">${esc(id.email)}</a>. `
            + `The contact form on this page also stores the message and forwards it to her inbox.</p>`
            + `<ul><li><a href="${esc(id.github)}" target="_blank" rel="noopener">GitHub</a></li>`
            + (li ? `<li><a href="${esc(li.url)}" target="_blank" rel="noopener">LinkedIn</a></li>` : '')
            + `<li><a href="${esc(id.cv)}">Download CV</a></li></ul>`,
            [{ label: 'Contact', href: '#contact' }],
        );
    }

    /* Availability */
    if (/\b(open to|available|availability|internship|looking for|hiring|job|position|vacanc)/.test(q) && !/why/.test(q)) {
        return done(
            `<p><strong>${esc(id.availability)}.</strong> She is a CSE undergraduate at KUET, based in ${esc(id.location)}, `
            + 'and is looking for backend or full-stack work on real systems — internships and junior engineering roles in particular.</p>',
            [{ label: 'Contact', href: '#contact' }, { label: 'Résumé', href: '#resume' }],
        );
    }

    /* Education */
    if (/\b(cgpa|gpa|grade|result|university|kuet|education|degree|stud(y|ies|ying)|school|college|hsc|ssc|academic)/.test(q)) {
        const items = (d.education ?? []).map((e) =>
            `<li><strong>${esc(e.degree)}</strong> — ${esc(e.institution)}, ${esc(e.start)}–${esc(e.end)}${e.grade ? ` · ${esc(e.grade)}` : ''}</li>`);
        return done(`<ul>${items.join('')}</ul>`, [{ label: 'Résumé', href: '#resume' }]);
    }

    /* Why hire */
    if (/why.*(hire|choose|pick|consider)|what makes her|strength|stand out|good fit/.test(q)) {
        const backend = featured.filter((p) => p.category === 'backend' || p.architecture.length >= 5).slice(0, 2);
        const edu = (d.education ?? [])[0];
        return done(
            '<p>Judged only by what is on this site:</p><ul>'
            + (backend.length ? `<li>Backend work with real engineering underneath — ${backend.map(link).join(' and ')} cover authentication, role-based access, migrations and containers.</li>` : '')
            + `<li>Range below the web layer: a compiler written in C and an ESP32 control system.</li>`
            + `<li>${d.projects.length} documented projects across ${d.metrics?.languages ?? 'many'} languages, each with the problem, the hard part and what she learned.</li>`
            + (edu?.grade ? `<li>${esc(edu.grade)} at ${esc(edu.institution)}.</li>` : '')
            + '</ul><p>The case studies are the evidence — they explain decisions, not just features.</p>',
            [...projectSources(backend), { label: 'How I think', href: '#about' }],
        );
    }

    /* Authentication & security */
    if (/\b(auth|authentication|authorization|login|security|secure|jwt|rbac|role|access control|permission)/.test(q)) {
        const ps = d.projects.filter((p) => p.security
            || p.stack.some((t) => /jwt|spring security|firebase auth|rbac/i.test(t)));
        const lead = ps.find((p) => p.slug === 'timeless') ?? ps[0];
        return done(
            (lead ? `<p>The clearest example is ${link(lead)}: ${esc(lead.security || lead.summary)}</p>` : '')
            + `<p>Other projects that handle access control: ${ps.filter((p) => p !== lead).map(link).join(', ')}.</p>`,
            projectSources(ps),
        );
    }

    /* Strongest / best project */
    if (/\b(best|strongest|favou?rite|most impressive|flagship|proudest|top|main|biggest)\b/.test(q)) {
        const wantBackend = /backend|api|server|java|spring/.test(q);
        const pool = (wantBackend ? featured.filter((p) => p.category === 'backend') : featured);
        const best = [...(pool.length ? pool : featured)].sort((a, b) => b.architecture.length - a.architecture.length)[0];

        if (best) {
            const decision = best.decisions.split(/\n{2,}/)[0];
            return done(
                `<p><strong>${link(best)}</strong> — ${esc(best.summary)}</p>`
                + (decision ? `<p>The key decision: ${esc(decision)}</p>` : '')
                + `<p>Stack: ${esc(best.stack.slice(0, 6).join(', '))}.</p>`,
                [{ label: best.title, href: best.url }],
            );
        }
    }

    /* Databases */
    if (/\b(database|databases|sql|schema|migration|data model|postgres|mysql)\b/.test(q) && !findTechnology(q)) {
        const group = d.skills.find((g) => /data/i.test(g.group));
        const ps = d.projects.filter((p) => p.stack.some((t) => /postgres|mysql|firestore|firebase|liquibase/i.test(t)));
        return done(
            (group ? `<p>${esc(group.items.map((i) => i.name).join(', '))}.</p>` : '')
            + `<p>Used in ${ps.length} projects, including ${ps.slice(0, 4).map(link).join(', ')}. `
            + 'Schema changes in the Spring Boot services go through Liquibase migrations.</p>',
            [{ label: 'Stack', href: '#stack' }, ...projectSources(ps)],
        );
    }

    /* APIs */
    if (/\b(api|apis|rest|endpoint|http)\b/.test(q) && !findTechnology(q)) {
        const ps = d.projects.filter((p) => p.stack.some((t) => /rest|jwt|swagger/i.test(t)) || p.demo_request);
        return done(
            `<p>REST APIs with authentication, role checks and documented endpoints — in ${ps.map(link).join(', ')}. `
            + 'The “Under the hood” section lets you run a request through those systems layer by layer.</p>',
            [{ label: 'Under the hood', href: '#hood' }, ...projectSources(ps)],
        );
    }

    /* A named technology */
    const tech = findTechnology(q);

    if (tech) {
        if (!tech.projects.length) {
            return done(`<p><strong>${esc(tech.name)}</strong> is in her stack, but none of the projects on this site use it yet.</p>`, [{ label: 'Stack', href: '#stack' }]);
        }

        return done(
            `<p><strong>${esc(tech.name)}</strong> — used in ${tech.projects.length} project${tech.projects.length === 1 ? '' : 's'}:</p>`
            + `<ul>${tech.projects.slice(0, 6).map((p) => `<li>${link(p)} — ${esc(p.subtitle || p.summary.slice(0, 80))}</li>`).join('')}</ul>`
            + (tech.related.length ? `<p>Usually alongside ${esc(list(tech.related.slice(0, 4)))}.</p>` : ''),
            projectSources(tech.projects),
        );
    }

    /* A category of work */
    for (const [re, category, label] of CATEGORY_WORDS) {
        if (!re.test(q)) continue;
        const ps = d.projects.filter((p) => p.category === category);
        if (!ps.length) continue;

        return done(
            `<p>Yes — ${ps.length} ${esc(label)}:</p><ul>${ps.map((p) => `<li>${link(p)} — ${esc(p.subtitle || '')}</li>`).join('')}</ul>`,
            projectSources(ps),
        );
    }

    /* Backend overview */
    if (/\bbackend|back.end|server/.test(q)) {
        const group = d.skills.find((g) => /backend/i.test(g.group));
        const ps = d.projects.filter((p) => p.category === 'backend' || p.demo_request);
        return done(
            (group ? `<p>${esc(group.items.map((i) => i.name).join(', '))}.</p>` : '')
            + `<p>Most of that work is in ${ps.map(link).join(', ')}.</p>`,
            [{ label: 'Stack', href: '#stack' }, ...projectSources(ps)],
        );
    }

    /* Skills overview */
    if (/\b(skill|stack|technolog|languages?|tools?|know|proficien)/.test(q)) {
        return done(
            `<ul>${d.skills.map((g) => `<li><strong>${esc(g.group)}:</strong> ${esc(g.items.map((i) => i.name).join(', '))}</li>`).join('')}</ul>`
            + '<p>The stack explorer shows which projects use each one.</p>',
            [{ label: 'Stack explorer', href: '#stack' }],
        );
    }

    /* Activities */
    if (/\b(club|activit|hackathon|competition|contest|sgipc|volunteer|extracurricular)/.test(q)) {
        return done(`<ul>${(d.activities ?? []).map((a) => `<li><strong>${esc(a.role)}</strong>, ${esc(a.org)}</li>`).join('')}</ul>`, [{ label: 'Activities', href: '#resume' }]);
    }

    /* About / background */
    if (/\b(who|about|background|introduce|summary|tell me)\b/.test(q)) {
        return done(`<p>${esc(id.pitch)}</p>`, [{ label: 'How I think', href: '#about' }]);
    }

    if (/\b(where|located|based|location|live|country|city)\b/.test(q)) {
        return done(`<p>${esc(id.location)} — open to remote work.</p>`);
    }

    if (/\b(resume|cv)\b/.test(q)) {
        return done(`<p>You can <a href="${esc(id.cv)}">download her CV</a> or <a href="${esc(id.resume)}">open the live résumé</a>, which is generated from this site’s content.</p>`, [{ label: 'Résumé', href: '#resume' }]);
    }

    /* Retrieval fallback */
    const hits = search(raw);

    if (hits.length) {
        return done(
            '<p>Here is what this portfolio says about that:</p><ul>'
            + hits.map(({ doc }) => `<li>${doc.kind === 'project' ? link(doc.ref) + ' — ' : `<strong>${esc(doc.label)}</strong> — `}${esc(doc.snippet.slice(0, 170))}${doc.snippet.length > 170 ? '…' : ''}</li>`).join('')
            + '</ul>',
            hits.map(({ doc }) => ({ label: doc.label, href: doc.href })),
        );
    }

    return done(
        '<p>I can only answer from what is on this site, and I found nothing about that. '
        + 'Try asking about a technology, a project, her education, or how to reach her.</p>',
    );
}

export const SUGGESTIONS = [
    'Best backend project',
    'Spring Boot experience',
    'Mobile apps',
    'Education',
    'Why hire Afifa?',
    'What project best shows authentication?',
    'How can I contact her?',
];
