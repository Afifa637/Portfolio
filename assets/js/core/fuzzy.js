/**
 * Fuzzy matching for the command palette and archive search.
 *
 * A subsequence matcher in the style of editor "go to file" pickers: every
 * query character must appear in order, and the score rewards what people
 * mean when they type a fragment — consecutive runs, the start of a word, the
 * start of the string — and penalises gaps.
 *
 * Returns { score, indices } or null when there is no match.
 */

const WORD_BREAK = /[\s\-_./·:()]/;

export function fuzzy(query, text) {
    const q = query.toLowerCase().trim();
    const t = text.toLowerCase();

    if (q === '') return { score: 0, indices: [] };

    // A literal substring is the strongest possible signal.
    const literal = t.indexOf(q);

    if (literal !== -1) {
        const atWord = literal === 0 || WORD_BREAK.test(t[literal - 1]);
        return {
            score: 1000 - literal + (atWord ? 200 : 0) + (literal === 0 ? 150 : 0),
            indices: Array.from({ length: q.length }, (_, i) => literal + i),
        };
    }

    let score = 0;
    let ti = 0;
    let run = 0;
    let lastMatch = -1;
    const indices = [];

    for (let qi = 0; qi < q.length; qi++) {
        const ch = q[qi];
        if (ch === ' ') continue;

        let found = -1;

        for (; ti < t.length; ti++) {
            if (t[ti] === ch) {
                found = ti;
                break;
            }
        }

        if (found === -1) return null;

        const atWord = found === 0 || WORD_BREAK.test(t[found - 1]);
        const consecutive = lastMatch === found - 1;

        run = consecutive ? run + 1 : 1;
        score += 10 + (atWord ? 25 : 0) + run * 6 - (lastMatch >= 0 ? Math.min(found - lastMatch - 1, 10) : found * 0.5);

        indices.push(found);
        lastMatch = found;
        ti = found + 1;
    }

    return { score, indices };
}

/** Wrap matched characters in <mark>, escaping everything else. */
export function highlight(text, indices) {
    if (!indices?.length) return escapeHtml(text);

    const set = new Set(indices);
    let out = '';
    let open = false;

    for (let i = 0; i < text.length; i++) {
        const hit = set.has(i);

        if (hit && !open) { out += '<mark>'; open = true; }
        if (!hit && open) { out += '</mark>'; open = false; }

        out += escapeHtml(text[i]);
    }

    return open ? out + '</mark>' : out;
}

function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}
