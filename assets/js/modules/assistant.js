/**
 * Ask Afifa — the floating assistant panel.
 *
 * The UI over kb.js. Answers appear after a short pause that reflects real
 * work (retrieval runs synchronously and is fast), and every answer carries
 * links to its sources. Section links close the panel and scroll there.
 */

import { $, esc, goTo, track, reducedMotion } from '@/core/util.js';
import { answer, SUGGESTIONS } from '@/modules/kb.js';

let ready = false;

function init() {
    if (ready) return;
    ready = true;

    const panel = $('#ask');
    const log = $('#ask-log');
    const form = $('#ask-form');
    const input = $('#ask-input');
    const chips = $('#ask-chips');

    chips.innerHTML = SUGGESTIONS.map((s) => `<button type="button">${esc(s)}</button>`).join('');

    const add = (html, cls) => {
        const el = document.createElement('div');
        el.className = cls;
        el.innerHTML = html;
        log.append(el);
        log.scrollTop = log.scrollHeight;
        return el;
    };

    const ask = async (question) => {
        const q = question.trim();
        if (!q) return;

        add(esc(q), 'msg-q');
        input.value = '';
        track('assistant question');

        const typing = add('<span class="msg-typing" aria-label="Searching"><i></i><i></i><i></i></span>', 'msg-a');
        await new Promise((r) => setTimeout(r, reducedMotion.matches ? 0 : 380));

        const result = answer(q);

        typing.innerHTML = result.html
            + (result.sources.length
                ? `<div class="msg-sources"><span class="label" style="width:100%">Sources</span>${result.sources.map((s) =>
                    `<a href="${esc(s.href)}">↗ ${esc(s.label)}</a>`).join('')}</div>`
                : '');

        log.scrollTop = log.scrollHeight;
    };

    add('<p><strong>Hi — ask me anything about Afifa’s work.</strong></p><p>I answer from this portfolio’s own content and link every source, so nothing here is invented.</p>', 'msg-a');

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        ask(input.value);
    });

    chips.addEventListener('click', (e) => {
        const chip = e.target.closest('button');
        if (chip) ask(chip.textContent);
    });

    // In-page links in answers close the panel and scroll to the section.
    log.addEventListener('click', (e) => {
        const a = e.target.closest('a[href^="#"]');
        if (!a) return;
        e.preventDefault();
        closeAssistant();
        goTo(a.getAttribute('href'));
    });

    $('#ask-close').addEventListener('click', closeAssistant);

    panel.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeAssistant();
    });
}

export function openAssistant(question) {
    init();

    const panel = $('#ask');
    panel.classList.add('is-open');
    $('#ask-open')?.setAttribute('aria-expanded', 'true');

    setTimeout(() => $('#ask-input')?.focus(), 60);

    if (question) {
        $('#ask-input').value = question;
        $('#ask-form').requestSubmit();
    }
}

export function closeAssistant() {
    $('#ask')?.classList.remove('is-open');
    const fab = $('#ask-open');
    fab?.setAttribute('aria-expanded', 'false');
    fab?.focus();
}
