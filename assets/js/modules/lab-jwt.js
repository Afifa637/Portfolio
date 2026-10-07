/**
 * Lab: JWT inspector.
 *
 * Decodes a token in the browser and explains it: the three segments, each
 * registered claim, whether it is currently valid, and the mistakes worth
 * catching (alg "none", no expiry, a lifetime measured in weeks).
 *
 * It deliberately does not "verify" anything: verification needs the
 * server's secret, and saying so is the most useful thing a decoder can teach.
 */

import { $, esc } from '@/core/util.js';

const CLAIMS = {
    iss: 'Issuer — who minted the token',
    sub: 'Subject — the user it represents',
    aud: 'Audience — who it is meant for',
    exp: 'Expires — rejected after this time',
    nbf: 'Not before — rejected before this time',
    iat: 'Issued at',
    jti: 'Token ID — lets a server revoke this one token',
    roles: 'Roles — what this user may do (RBAC)',
    scope: 'Scopes — what this token may do',
};

const b64url = (obj) => btoa(unescape(encodeURIComponent(JSON.stringify(obj))))
    .replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');

function decodeSegment(seg) {
    const padded = seg.replace(/-/g, '+').replace(/_/g, '/').padEnd(Math.ceil(seg.length / 4) * 4, '=');
    return JSON.parse(decodeURIComponent(escape(atob(padded))));
}

function sample(kind) {
    const now = Math.floor(Date.now() / 1000);
    const header = { alg: kind === 'none' ? 'none' : 'HS256', typ: 'JWT' };
    const payload = {
        iss: 'timeless-api (sample)',
        sub: 'demo-seller-42',
        roles: ['SELLER'],
        iat: now - 600,
        exp: kind === 'expired' ? now - 120 : now + 3000,
        jti: 'f3a9c2',
    };

    const sig = kind === 'none' ? '' : 'c2FtcGxlLXNpZ25hdHVyZS1ub3QtcmVhbA';
    return `${b64url(header)}.${b64url(payload)}.${sig}`;
}

/** Pretty JSON with light syntax colouring, escaped first. */
function pretty(obj) {
    return esc(JSON.stringify(obj, null, 2))
        .replace(/(&quot;[^&]*?&quot;)(\s*:)/g, '<span class="tok-k">$1</span>$2')
        .replace(/:\s(&quot;.*?&quot;)/g, ': <span class="tok-s">$1</span>')
        .replace(/:\s(-?\d+(\.\d+)?)/g, ': <span class="tok-n">$1</span>');
}

function when(seconds) {
    const date = new Date(seconds * 1000);
    const diff = Math.round((date - Date.now()) / 60000);
    const rel = Math.abs(diff) < 60
        ? `${Math.abs(diff)} min`
        : Math.abs(diff) < 2880 ? `${Math.round(Math.abs(diff) / 60)} h` : `${Math.round(Math.abs(diff) / 1440)} days`;

    return `${date.toLocaleString()} (${diff >= 0 ? 'in ' + rel : rel + ' ago'})`;
}

export function mount(panel) {
    panel.innerHTML = `
        <div class="lab-intro">
            <p>Paste a JSON Web Token to see what it carries. Timeless and Student Management both
               authenticate API calls this way. Decoding happens locally — nothing is sent anywhere.</p>
            <div class="hero-actions">
                <button class="btn btn-sm btn-ghost" type="button" data-sample="valid">Valid sample</button>
                <button class="btn btn-sm btn-ghost" type="button" data-sample="expired">Expired sample</button>
                <button class="btn btn-sm btn-ghost" type="button" data-sample="none">alg: none</button>
            </div>
        </div>
        <div class="lab-split">
            <div>
                <label class="field-label" for="jwt-in">Token</label>
                <textarea class="code-input" id="jwt-in" spellcheck="false" style="margin-top:.5rem"></textarea>
                <p class="field-hint" style="margin-top:.6rem">Samples are generated in your browser with a fake signature.</p>
                <div class="code-out jwt-seg" id="jwt-seg" style="margin-top:.8rem" aria-label="Token segments"></div>
            </div>
            <div id="jwt-out" aria-live="polite"></div>
        </div>`;

    const input = $('#jwt-in', panel);
    const seg = $('#jwt-seg', panel);
    const out = $('#jwt-out', panel);

    const render = () => {
        const token = input.value.trim();

        if (!token) {
            out.innerHTML = '<p class="t-3">Waiting for a token.</p>';
            seg.innerHTML = '';
            return;
        }

        const parts = token.split('.');

        if (parts.length !== 3) {
            out.innerHTML = `<p class="alert alert-err"><span>A JWT has three dot-separated parts; this has ${parts.length}.</span></p>`;
            seg.textContent = token;
            return;
        }

        seg.innerHTML = `<span class="h">${esc(parts[0])}</span><span class="d">.</span><span class="p">${esc(parts[1])}</span><span class="d">.</span><span class="s">${esc(parts[2]) || '<em class="t-3">(empty)</em>'}</span>`;

        let header;
        let payload;

        try {
            header = decodeSegment(parts[0]);
            payload = decodeSegment(parts[1]);
        } catch {
            out.innerHTML = '<p class="alert alert-err"><span>The header or payload is not valid base64url-encoded JSON.</span></p>';
            return;
        }

        const now = Date.now() / 1000;
        const warnings = [];

        if (String(header.alg).toLowerCase() === 'none') {
            warnings.push('<strong>alg is "none"</strong> — the token is unsigned. A server that accepts this lets anyone forge any identity.');
        }
        if (payload.exp === undefined) warnings.push('<strong>No exp claim</strong> — this token never expires.');
        if (payload.exp && payload.iat && payload.exp - payload.iat > 86400 * 7) warnings.push('Lifetime longer than a week — long-lived tokens widen the window if one leaks.');

        let status = '<span class="t-3">no expiry set</span>';
        if (payload.exp !== undefined) {
            status = payload.exp < now
                ? '<span style="color:var(--red)">● expired</span>'
                : '<span style="color:var(--green)">● within its validity window</span>';
        }
        if (payload.nbf !== undefined && payload.nbf > now) status = '<span style="color:var(--amber)">● not yet valid</span>';

        const claims = Object.entries(payload).map(([k, v]) => {
            const shown = ['exp', 'iat', 'nbf'].includes(k) && typeof v === 'number' ? when(v) : (typeof v === 'object' ? JSON.stringify(v) : String(v));
            return `<div class="claim"><code>${esc(k)}</code><span>${esc(shown)}${CLAIMS[k] ? `<br><small class="t-3">${esc(CLAIMS[k])}</small>` : ''}</span></div>`;
        }).join('');

        out.innerHTML =
            `<p class="label" style="margin-bottom:.5rem">Header · ${esc(header.alg)}</p>`
            + `<pre class="code-out">${pretty(header)}</pre>`
            + `<p class="label" style="margin:1rem 0 .3rem">Claims · ${status}</p>`
            + `<div class="claims">${claims}</div>`
            + (warnings.length ? `<div class="alert alert-err" style="margin-top:1rem;display:block">${warnings.map((w) => `<p>${w}</p>`).join('')}</div>` : '')
            + '<p class="field-hint" style="margin-top:1rem">Decoding is not verifying. Only the server, holding the signing secret, can check the signature — never trust these claims client-side.</p>';
    };

    panel.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-sample]');
        if (!btn) return;
        input.value = sample(btn.dataset.sample);
        render();
    });

    input.addEventListener('input', render);
    input.value = sample('valid');
    render();
}
