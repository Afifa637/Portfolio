/**
 * Contact form: validated in the browser for fast feedback, validated again on
 * the server for real, submitted with fetch, and replaced by a success state
 * once the message is stored and on its way to the inbox.
 */

import { $, $$, esc, icon, track } from '@/core/util.js';

const RULES = {
    name: (v) => (v.trim() === '' ? 'Please tell me your name.' : ''),
    email: (v) => (v.trim() === '' ? 'I need an email address to reply to.'
        : !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim()) ? 'That email address does not look right.' : ''),
    message: (v) => (v.trim().length < 10 ? 'A sentence or two, please — at least 10 characters.' : ''),
};

export function init(form) {
    const status = $('#form-status', form);
    const submit = $('button[type="submit"]', form);

    const setError = (name, message) => {
        const input = form.elements[name];
        const field = input?.closest?.('.field');
        if (!field) return;

        field.dataset.invalid = message ? 'true' : 'false';
        input.setAttribute('aria-invalid', message ? 'true' : 'false');

        const slot = $('.field-error', field);
        if (slot) slot.textContent = message;
    };

    const validate = () => {
        let first = null;

        Object.entries(RULES).forEach(([name, rule]) => {
            const message = rule(form.elements[name]?.value ?? '');
            setError(name, message);
            if (message && !first) first = form.elements[name];
        });

        return first;
    };

    // Validate a field once the visitor leaves it, then live while they fix it.
    Object.keys(RULES).forEach((name) => {
        const input = form.elements[name];
        input?.addEventListener('blur', () => { if (input.value) setError(name, RULES[name](input.value)); });
        input?.addEventListener('input', () => {
            if (input.closest('.field')?.dataset.invalid === 'true') setError(name, RULES[name](input.value));
        });
    });

    const showStatus = (ok, message) => {
        status.hidden = false;
        status.className = `alert ${ok ? 'alert-ok' : 'alert-err'}`;
        status.setAttribute('role', ok ? 'status' : 'alert');
        status.innerHTML = `${icon(ok ? 'check' : 'x')}<span>${esc(message)}</span>`;
    };

    form.addEventListener('submit', async (e) => {
        if (!window.fetch) return; // plain POST still works

        e.preventDefault();

        const invalid = validate();
        if (invalid) {
            invalid.focus();
            return;
        }

        submit.classList.add('is-busy');
        submit.setAttribute('aria-disabled', 'true');
        status.hidden = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });

            const result = await response.json().catch(() => ({ ok: false, message: 'Unexpected response from the server.' }));

            Object.entries(result.errors ?? {}).forEach(([name, message]) => setError(name, message));

            if (result.csrf) {
                const token = form.elements.csrf_token;
                if (token) token.value = result.csrf;
            }

            if (!result.ok) {
                showStatus(false, result.message || 'Something went wrong.');
                return;
            }

            track('contact submitted', { reason: form.elements.purpose?.value ?? '' });

            const name = String(form.elements.name.value).trim().split(/\s+/)[0];
            const card = form.parentElement;

            card.innerHTML = `
                <div class="form-success" role="status" tabindex="-1">
                    <span class="check"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg></span>
                    <h3>Thank you${name ? ', ' + esc(name) : ''}.</h3>
                    <p>Your message is saved and on its way to my inbox. I reply within a day or two.</p>
                    <button class="btn btn-ghost btn-sm" type="button" data-reload>Send another</button>
                </div>`;

            card.querySelector('.form-success')?.focus();
            card.querySelector('[data-reload]')?.addEventListener('click', () => location.reload());
        } catch {
            showStatus(false, 'Could not reach the server. Check your connection, or email me directly.');
        } finally {
            submit.classList.remove('is-busy');
            submit.removeAttribute('aria-disabled');
        }
    });

    $$('.choice input', form).forEach((radio) => radio.addEventListener('change', () => setError('purpose', '')));
}
