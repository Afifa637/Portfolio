/**
 * Lab: SQL playground.
 *
 * A small SQL engine — tokenizer, recursive-descent parser, executor — over
 * this portfolio's own data. It supports a deliberate subset:
 *
 *   SELECT * | cols | COUNT(*) [AS x]  FROM table
 *   [WHERE expr]        =, !=, <>, <, <=, >, >=, LIKE, NOT LIKE, AND, OR, ( )
 *   [GROUP BY col]      with COUNT(*)
 *   [ORDER BY col [ASC|DESC]]
 *   [LIMIT n]
 *
 * Each run shows the plan it executed and how many rows survived each step,
 * which is the part of SQL worth understanding.
 */

import { $, data, esc } from '@/core/util.js';

/* ------------------------------------------------------------ tables --- */

function tables() {
    const d = data();

    return {
        projects: d.projects.map((p) => ({
            title: p.title,
            category: p.category,
            year: Number(p.year) || null,
            featured: p.featured,
            language: p.stack[0] ?? null,
            stack: p.stack.join(', '),
            layers: p.architecture.length,
            features: p.features.length,
        })),
        skills: d.skills.flatMap((g) => g.items.map((i) => ({
            name: i.name,
            grp: g.group,
            projects: i.projects.length,
        }))),
    };
}

/* --------------------------------------------------------- tokenizer --- */

function tokenize(sql) {
    const tokens = [];
    // Sticky regex: each match must start exactly where the previous ended.
    const re = /\s*(?:('(?:[^']|'')*')|(\d+(?:\.\d+)?)|(<=|>=|<>|!=|=|<|>)|([(),*])|([A-Za-z_][A-Za-z0-9_]*)|(\S))/y;

    while (re.lastIndex < sql.length) {
        const m = re.exec(sql);
        if (!m) break; // only trailing whitespace remains

        if (m[1] !== undefined) tokens.push({ t: 'str', v: m[1].slice(1, -1).replace(/''/g, "'") });
        else if (m[2] !== undefined) tokens.push({ t: 'num', v: Number(m[2]) });
        else if (m[3] !== undefined) tokens.push({ t: 'op', v: m[3] });
        else if (m[4] !== undefined) tokens.push({ t: 'punc', v: m[4] });
        else if (m[5] !== undefined) tokens.push({ t: 'word', v: m[5] });
        else if (m[6] === "'") throw new Error('Unterminated string — strings use single quotes');
        else if (m[6] !== ';') throw new Error(`Unexpected character “${m[6]}”`);
    }

    return tokens;
}

/* ------------------------------------------------------------ parser --- */

function parse(sql) {
    const tk = tokenize(sql);
    let i = 0;

    const peek = () => tk[i];
    const kw = (word) => peek()?.t === 'word' && peek().v.toUpperCase() === word;
    const expectKw = (word) => {
        if (!kw(word)) throw new Error(`Expected ${word}${peek() ? ` but found “${peek().v}”` : ''}`);
        i++;
    };
    const ident = () => {
        const t = tk[i++];
        if (t?.t !== 'word') throw new Error(`Expected a column name${t ? ` but found “${t.v}”` : ''}`);
        return t.v.toLowerCase();
    };

    expectKw('SELECT');

    const columns = [];
    let count = null;

    do {
        if (peek()?.v === '*') { i++; columns.push('*'); continue; }

        if (kw('COUNT')) {
            i++;
            if (tk[i++]?.v !== '(' || tk[i++]?.v !== '*' || tk[i++]?.v !== ')') throw new Error('Only COUNT(*) is supported');
            count = 'count';
            if (kw('AS')) { i++; count = ident(); }
            columns.push({ count });
            continue;
        }

        columns.push(ident());
    } while (peek()?.v === ',' && ++i);

    expectKw('FROM');
    const table = ident();

    const value = () => {
        const t = tk[i++];
        if (!t) throw new Error('Expected a value');
        if (t.t === 'str' || t.t === 'num') return t.v;
        if (t.t === 'word' && /^(true|false)$/i.test(t.v)) return t.v.toLowerCase() === 'true';
        if (t.t === 'word' && /^null$/i.test(t.v)) return null;
        throw new Error(`Expected a value but found “${t.v}”`);
    };

    const condition = () => {
        if (peek()?.v === '(') {
            i++;
            const e = orExpr();
            if (tk[i++]?.v !== ')') throw new Error('Missing )');
            return e;
        }

        const col = ident();
        let not = false;
        if (kw('NOT')) { i++; not = true; }

        if (kw('LIKE')) {
            i++;
            const pattern = value();
            return { type: 'like', col, pattern: String(pattern), not };
        }

        if (not) throw new Error('NOT is only supported as NOT LIKE');

        const op = tk[i++];
        if (op?.t !== 'op') throw new Error(`Expected an operator after ${col}`);

        return { type: 'cmp', col, op: op.v, value: value() };
    };

    const andExpr = () => {
        let left = condition();
        while (kw('AND')) { i++; left = { type: 'and', left, right: condition() }; }
        return left;
    };

    function orExpr() {
        let left = andExpr();
        while (kw('OR')) { i++; left = { type: 'or', left, right: andExpr() }; }
        return left;
    }

    let where = null;
    let groupBy = null;
    let orderBy = null;
    let limit = null;

    if (kw('WHERE')) { i++; where = orExpr(); }
    if (kw('GROUP')) { i++; expectKw('BY'); groupBy = ident(); }

    if (kw('ORDER')) {
        i++;
        expectKw('BY');
        const col = ident();
        let dir = 'asc';
        if (kw('ASC') || kw('DESC')) dir = tk[i++].v.toLowerCase();
        orderBy = { col, dir };
    }

    if (kw('LIMIT')) {
        i++;
        const n = tk[i++];
        if (n?.t !== 'num') throw new Error('LIMIT needs a number');
        limit = n.v;
    }

    if (i < tk.length) throw new Error(`Unexpected “${tk[i].v}”`);

    return { columns, table, where, groupBy, orderBy, limit };
}

/* ---------------------------------------------------------- executor --- */

function test(row, e) {
    if (e.type === 'and') return test(row, e.left) && test(row, e.right);
    if (e.type === 'or') return test(row, e.left) || test(row, e.right);

    if (!(e.col in row)) throw new Error(`No column “${e.col}”`);
    const v = row[e.col];

    if (e.type === 'like') {
        const re = new RegExp('^' + e.pattern.replace(/[.+?^${}()|[\]\\]/g, '\\$&').replace(/%/g, '.*').replace(/_/g, '.') + '$', 'i');
        const hit = re.test(String(v ?? ''));
        return e.not ? !hit : hit;
    }

    const a = typeof v === 'string' ? v.toLowerCase() : v;
    const b = typeof e.value === 'string' ? e.value.toLowerCase() : e.value;

    switch (e.op) {
        case '=': return a === b;
        case '!=': case '<>': return a !== b;
        case '<': return a < b;
        case '<=': return a <= b;
        case '>': return a > b;
        case '>=': return a >= b;
        default: return false;
    }
}

function execute(q) {
    const all = tables();
    const source = all[q.table];
    if (!source) throw new Error(`No table “${q.table}”. Try projects or skills.`);

    const plan = [{ step: `Scan ${q.table}`, rows: source.length }];
    let rows = source.slice();

    if (q.where) {
        rows = rows.filter((r) => test(r, q.where));
        plan.push({ step: 'Filter', rows: rows.length });
    }

    const countCol = q.columns.find((c) => typeof c === 'object')?.count;

    if (q.groupBy) {
        if (!(q.groupBy in (source[0] ?? {}))) throw new Error(`No column “${q.groupBy}”`);

        const groups = new Map();
        rows.forEach((r) => groups.set(r[q.groupBy], (groups.get(r[q.groupBy]) ?? 0) + 1));
        rows = [...groups].map(([k, n]) => ({ [q.groupBy]: k, [countCol ?? 'count']: n }));
        plan.push({ step: `Group by ${q.groupBy}`, rows: rows.length });
    } else if (countCol) {
        rows = [{ [countCol]: rows.length }];
        plan.push({ step: 'Aggregate', rows: 1 });
    }

    if (q.orderBy) {
        const { col, dir } = q.orderBy;
        if (rows.length && !(col in rows[0])) throw new Error(`No column “${col}” to order by`);
        rows.sort((a, b) => {
            const x = a[col]; const y = b[col];
            const c = typeof x === 'number' && typeof y === 'number' ? x - y : String(x ?? '').localeCompare(String(y ?? ''));
            return dir === 'desc' ? -c : c;
        });
        plan.push({ step: `Sort ${col} ${dir}`, rows: rows.length });
    }

    if (q.limit !== null) {
        rows = rows.slice(0, q.limit);
        plan.push({ step: `Limit ${q.limit}`, rows: rows.length });
    }

    // Projection.
    const wanted = q.columns.flatMap((c) => (c === '*' ? Object.keys(rows[0] ?? source[0] ?? {}) : typeof c === 'object' ? [c.count] : [c]));
    const unique = [...new Set(wanted)];

    if (!q.groupBy && !countCol) {
        unique.forEach((c) => { if (source.length && !(c in source[0])) throw new Error(`No column “${c}”`); });
    }

    return { columns: unique, rows: rows.map((r) => unique.map((c) => r[c])), plan };
}

/* ---------------------------------------------------------------- UI --- */

const EXAMPLES = [
    "SELECT title, year FROM projects WHERE stack LIKE '%Spring%'",
    'SELECT category, COUNT(*) AS builds FROM projects GROUP BY category ORDER BY builds DESC',
    'SELECT title, layers FROM projects WHERE layers >= 4 ORDER BY layers DESC',
    'SELECT name, projects FROM skills WHERE grp = \'Backend\' ORDER BY projects DESC LIMIT 5',
];

export function mount(panel) {
    const t = tables();

    panel.innerHTML = `
        <div class="lab-intro">
            <p>Query this portfolio’s own data. A small SQL engine — tokenizer, parser and executor —
               runs in your browser and shows the plan it followed.</p>
        </div>
        <div class="sql-schema">
            <span class="tag"><b>projects</b>&nbsp;(${Object.keys(t.projects[0] ?? {}).join(', ')})</span>
            <span class="tag"><b>skills</b>&nbsp;(${Object.keys(t.skills[0] ?? {}).join(', ')})</span>
        </div>
        <label class="field-label" for="sql-in">Query</label>
        <textarea class="code-input" id="sql-in" spellcheck="false" style="min-height:6rem;margin-top:.5rem"></textarea>
        <div class="hero-actions" style="margin-top:.7rem;align-items:center">
            <button class="btn btn-primary btn-sm" type="button" id="sql-run">Run <kbd style="margin-left:.3rem">Ctrl ↵</kbd></button>
            <span class="field-hint">or try:</span>
        </div>
        <div class="sql-examples">${EXAMPLES.map((q, i) => `<button class="tag" type="button" data-ex="${i}">${esc(q.length > 54 ? q.slice(0, 52) + '…' : q)}</button>`).join('')}</div>
        <div id="sql-out" aria-live="polite" style="margin-top:1rem"></div>`;

    const input = $('#sql-in', panel);
    const out = $('#sql-out', panel);

    const run = () => {
        const started = performance.now();

        try {
            const result = execute(parse(input.value));
            const ms = (performance.now() - started).toFixed(2);

            out.innerHTML =
                `<div class="plan">${result.plan.map((p) => `<span>${esc(p.step)} → <b>${p.rows}</b></span>`).join('')}<span>${ms} ms in-browser</span></div>`
                + (result.rows.length
                    ? `<div class="result-wrap"><table class="result"><thead><tr>${result.columns.map((c) => `<th>${esc(c)}</th>`).join('')}</tr></thead>`
                      + `<tbody>${result.rows.map((r) => `<tr>${r.map((v) => `<td>${esc(v === null ? 'NULL' : v)}</td>`).join('')}</tr>`).join('')}</tbody></table></div>`
                    : '<p class="t-3">Query returned no rows.</p>');
        } catch (err) {
            out.innerHTML = `<p class="alert alert-err"><span>${esc(err.message)}</span></p>`;
        }
    };

    $('#sql-run', panel).addEventListener('click', run);
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); run(); }
    });

    panel.addEventListener('click', (e) => {
        const ex = e.target.closest('[data-ex]');
        if (!ex) return;
        input.value = EXAMPLES[Number(ex.dataset.ex)];
        run();
    });

    input.value = EXAMPLES[0];
    run();
}
