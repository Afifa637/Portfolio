/**
 * Request-flow animation over an architecture diagram.
 *
 * A packet travels down the layers and back up as the response. The "denied"
 * run stops at the first layer responsible for security — found by reading the
 * layer's own name and technology — and returns 403, which is how role-based
 * access control actually behaves.
 *
 * Timings are generated deterministically from the layer name and labelled as
 * illustrative wherever they are shown. They are not measurements.
 */

import { $, $$, esc, sleep, reducedMotion } from '@/core/util.js';

const SECURITY = /secur|auth|rbac|middleware|rules?\b|jwt|permission/i;

/** Stable pseudo-latency for a layer, in ms — illustrative only. */
function illustrativeMs(layer) {
    let h = 0;
    for (const ch of layer.layer + layer.tech) h = (h * 31 + ch.charCodeAt(0)) >>> 0;

    const base = /database|postgres|mysql|firestore|repositor/i.test(layer.layer + layer.tech) ? 9
        : SECURITY.test(layer.layer + layer.tech) ? 3
            : /client|browser/i.test(layer.layer) ? 1 : 2;

    return base + (h % 7);
}

export function renderLayers(list, layers, { interactive = true } = {}) {
    list.innerHTML = layers.map((l, i) =>
        `<li class="arch-layer${i === 0 && interactive ? ' is-on' : ''}">`
        + `<span class="arch-pin" aria-hidden="true">${i + 1}</span>`
        + (interactive
            ? `<button class="arch-node" type="button" data-layer="${i}" data-cursor="explore">`
            : '<div class="arch-node">')
        + `<strong>${esc(l.layer)}</strong><span>${esc(l.tech)}</span>`
        + (interactive ? '</button>' : '</div>')
        + '</li>').join('');
}

export function createFlow({ body, list, packet, log, insp, getLayers, getRequest }) {
    let running = false;

    const pins = () => $$('.arch-pin', list);
    const rows = () => $$('.arch-layer', list);

    const select = (i) => {
        const layers = getLayers();
        const layer = layers[i];
        if (!layer || !insp) return;

        rows().forEach((row, j) => row.classList.toggle('is-on', j === i));

        insp.innerHTML =
            `<p class="label">Layer ${String(i + 1).padStart(2, '0')}${SECURITY.test(layer.layer + layer.tech) ? ' · security boundary' : ''}</p>`
            + `<h3>${esc(layer.layer)}</h3>`
            + `<p>${esc(layer.role || 'No notes recorded for this layer yet.')}</p>`
            + `<div class="tags">${layer.tech.split(',').map((t) => t.trim()).filter(Boolean).map((t) => `<span class="tag">${esc(t)}</span>`).join('')}</div>`;
    };

    const movePacket = (i) => {
        const pin = pins()[i];
        if (!pin || !packet) return;

        const top = pin.getBoundingClientRect().top - body.getBoundingClientRect().top + pin.offsetHeight / 2 - 5;
        packet.style.setProperty('--py', `${top}px`);
    };

    const line = (cls, text, ms) => {
        if (!log) return;
        const row = document.createElement('div');
        row.className = `row ${cls}`;
        row.innerHTML = `<span class="st">${text.st}</span><span>${text.msg}</span><span class="ms">${ms ?? ''}</span>`;
        log.append(row);
        log.scrollTop = log.scrollHeight;
    };

    const step = reducedMotion.matches ? 0 : 420;

    async function run({ denied = false } = {}) {
        if (running) return;
        running = true;

        const layers = getLayers();
        const [method, path] = getRequest().split(' ');
        const stopAt = denied ? layers.findIndex((l) => SECURITY.test(l.layer + l.tech)) : -1;
        const blockAt = denied && stopAt === -1 ? 0 : stopAt;

        rows().forEach((r) => r.classList.remove('is-hit', 'is-denied', 'is-skipped', 'is-on'));
        if (log) log.innerHTML = '';
        packet?.classList.remove('is-back', 'is-denied');

        line('', { st: '$', msg: `${esc(method)} ${esc(path)}${denied ? '  <span class="t-3">(caller lacks the required role)</span>' : ''}` });

        movePacket(0);
        packet?.classList.add('is-live');
        await sleep(step / 2);

        let total = 0;
        const last = denied ? blockAt : layers.length - 1;

        for (let i = 0; i <= last; i++) {
            const ms = illustrativeMs(layers[i]);
            total += ms;

            movePacket(i);
            await sleep(step);

            const row = rows()[i];
            select(i);

            if (denied && i === blockAt) {
                row?.classList.add('is-denied');
                rows().slice(i + 1).forEach((r) => r.classList.add('is-skipped'));
                line('er', { st: '✕', msg: `${esc(layers[i].layer)} refused the request` }, `${ms}ms`);
                break;
            }

            row?.classList.add('is-hit');
            line('', { st: '→', msg: esc(layers[i].layer) }, `${ms}ms`);
        }

        // The response travels back up.
        packet?.classList.add(denied ? 'is-denied' : 'is-back');

        for (let i = last - 1; i >= 0; i--) {
            movePacket(i);
            await sleep(step / 2.4);
        }

        line(denied ? 'er' : 'ok', {
            st: '←',
            msg: denied ? '403 Forbidden — authenticated, but not authorised' : (method === 'POST' ? '201 Created / 200 OK' : '200 OK — JSON body'),
        }, `${total}ms`);

        if (log) {
            const note = document.createElement('p');
            note.className = 'demo-note';
            note.style.marginTop = '0.8rem';
            note.textContent = 'Illustrative timings — not measurements';
            log.append(note);
        }

        await sleep(step);
        packet?.classList.remove('is-live');
        running = false;
    }

    list.addEventListener('click', (e) => {
        const node = e.target.closest('[data-layer]');
        if (node) select(Number(node.dataset.layer));
    });

    list.addEventListener('focusin', (e) => {
        const node = e.target.closest('[data-layer]');
        if (node) select(Number(node.dataset.layer));
    });

    return { run, select, get running() { return running; } };
}
