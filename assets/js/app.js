/**
 * Portfolio front-end.
 *
 * Deliberately dependency-free. The previous build pulled ScrollReveal,
 * MixItUp and Typed.js from three CDNs (~60 KB gzipped plus three extra
 * connections) to do work the platform already does: IntersectionObserver for
 * reveals, a filter over the DOM for the project grid, and a short interval for
 * the typing effect.
 *
 * Every module is defensive — a missing element disables that feature rather
 * than throwing and killing the modules registered after it.
 */

(() => {
    'use strict';

    const $  = (sel, ctx = document) => ctx.querySelector(sel);
    const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    /** Run a module, isolating failures so one broken feature cannot break the rest. */
    const boot = (name, fn) => {
        try {
            fn();
        } catch (err) {
            console.error(`[portfolio] ${name} failed:`, err);
        }
    };


    /* ------------------------------------------------------------- theme --- */

    boot('theme', () => {
        const KEY    = 'portfolio-theme';
        const toggle = $('#theme-toggle');
        const root   = document.documentElement;

        const apply = (theme) => {
            root.dataset.theme = theme;
            root.style.colorScheme = theme;

            if (toggle) {
                const isDark = theme === 'dark';
                toggle.setAttribute('aria-label', isDark ? 'Switch to light theme' : 'Switch to dark theme');
                toggle.setAttribute('aria-pressed', String(!isDark));
                $$('[data-theme-icon]', toggle).forEach((el) => {
                    el.hidden = el.dataset.themeIcon !== (isDark ? 'sun' : 'moon');
                });
            }

            // Keep the mobile browser chrome in step with the page.
            const meta = $('meta[name="theme-color"]');
            if (meta) meta.content = theme === 'dark' ? '#0b0d14' : '#fbfaf7';
        };

        // The inline script in <head> has already set an initial theme to avoid
        // a flash; read it back rather than recomputing.
        apply(root.dataset.theme || 'dark');

        toggle?.addEventListener('click', () => {
            const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
            apply(next);
            try { localStorage.setItem(KEY, next); } catch { /* private mode */ }
        });

        // Follow the OS only while the visitor has expressed no preference.
        window.matchMedia('(prefers-color-scheme: light)').addEventListener('change', (e) => {
            let stored = null;
            try { stored = localStorage.getItem(KEY); } catch { /* ignore */ }
            if (!stored) apply(e.matches ? 'light' : 'dark');
        });
    });


    /* ------------------------------------------------- header & progress --- */

    boot('header', () => {
        const header = $('#header');
        const bar    = $('#progress');
        const toTop  = $('#to-top');

        if (!header && !bar && !toTop) return;

        let ticking = false;

        const update = () => {
            const y      = window.scrollY;
            const height = document.documentElement.scrollHeight - window.innerHeight;

            header?.classList.toggle('is-stuck', y > 12);
            toTop?.classList.toggle('is-shown', y > window.innerHeight * 0.6);

            if (bar) bar.style.setProperty('--progress', height > 0 ? (y / height).toFixed(4) : '0');

            ticking = false;
        };

        addEventListener('scroll', () => {
            if (!ticking) {
                ticking = true;
                requestAnimationFrame(update);
            }
        }, { passive: true });

        update();

        toTop?.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: reduceMotion.matches ? 'auto' : 'smooth' });
        });
    });


    /* --------------------------------------------------- mobile drawer --- */

    boot('drawer', () => {
        const drawer = $('#nav-drawer');
        const open   = $('#nav-toggle');
        const close  = $('#nav-close');

        if (!drawer || !open) return;

        let lastFocus = null;

        const setOpen = (isOpen) => {
            drawer.classList.toggle('is-open', isOpen);
            drawer.setAttribute('aria-hidden', String(!isOpen));
            open.setAttribute('aria-expanded', String(isOpen));
            document.body.classList.toggle('is-locked', isOpen);

            if (isOpen) {
                lastFocus = document.activeElement;
                ($('a, button', drawer))?.focus();
            } else {
                lastFocus?.focus?.();
            }
        };

        open.addEventListener('click', () => setOpen(true));
        close?.addEventListener('click', () => setOpen(false));

        // Any navigation choice closes the drawer.
        $$('a', drawer).forEach((a) => a.addEventListener('click', () => setOpen(false)));

        addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && drawer.classList.contains('is-open')) setOpen(false);
        });

        // Returning to desktop width must not leave the body scroll-locked.
        matchMedia('(min-width: 861px)').addEventListener('change', (e) => {
            if (e.matches) setOpen(false);
        });
    });


    /* ------------------------------------------------------- scrollspy --- */

    boot('scrollspy', () => {
        const links = $$('[data-spy]');
        if (!links.length) return;

        const sections = links
            .map((link) => {
                const el = document.getElementById(link.dataset.spy);
                return el ? { link, el } : null;
            })
            .filter(Boolean);

        if (!sections.length) return;

        const setActive = (id) => {
            sections.forEach(({ link }) => {
                const on = link.dataset.spy === id;
                link.classList.toggle('is-active', on);
                if (on) link.setAttribute('aria-current', 'true');
                else link.removeAttribute('aria-current');
            });
        };

        const observer = new IntersectionObserver((entries) => {
            // Choose the entry nearest the top of the viewport that is visible.
            const visible = entries
                .filter((e) => e.isIntersecting)
                .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);

            if (visible.length) setActive(visible[0].target.id);
        }, {
            rootMargin: '-45% 0px -50% 0px',
            threshold: 0,
        });

        sections.forEach(({ el }) => observer.observe(el));
    });


    /* ---------------------------------------------------------- reveal --- */

    boot('reveal', () => {
        const items = $$('[data-reveal]');
        if (!items.length) return;

        if (reduceMotion.matches || !('IntersectionObserver' in window)) {
            items.forEach((el) => el.classList.add('is-visible'));
            return;
        }

        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                obs.unobserve(entry.target);
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

        items.forEach((el, i) => {
            // Stagger siblings only; a global index would delay late sections by seconds.
            const index = Array.from(el.parentElement?.children || []).indexOf(el);
            el.style.setProperty('--reveal-delay', `${Math.min(index, 6) * 65}ms`);
            observer.observe(el);
        });
    });


    /* ------------------------------------------------------ type effect --- */

    boot('typing', () => {
        const target = $('#typed');
        if (!target) return;

        let roles = [];
        try {
            roles = JSON.parse(target.dataset.roles || '[]');
        } catch { /* fall through to the static label */ }

        if (!Array.isArray(roles) || roles.length === 0) return;

        if (reduceMotion.matches) {
            target.textContent = roles[0];
            return;
        }

        const TYPE = 62, ERASE = 30, HOLD = 1900, GAP = 420;
        let roleIndex = 0;
        let charIndex = 0;
        let erasing   = false;

        const tick = () => {
            const role = roles[roleIndex];

            if (!erasing) {
                target.textContent = role.slice(0, ++charIndex);

                if (charIndex === role.length) {
                    erasing = true;
                    return setTimeout(tick, HOLD);
                }

                return setTimeout(tick, TYPE);
            }

            target.textContent = role.slice(0, --charIndex);

            if (charIndex === 0) {
                erasing = false;
                roleIndex = (roleIndex + 1) % roles.length;
                return setTimeout(tick, GAP);
            }

            setTimeout(tick, ERASE);
        };

        setTimeout(tick, 700);
    });


    /* --------------------------------------------------------- counters --- */

    boot('counters', () => {
        const nums = $$('[data-count]');
        if (!nums.length) return;

        const run = (el) => {
            const target = parseInt(el.dataset.count, 10);
            if (Number.isNaN(target)) return;

            const suffix = el.dataset.countSuffix || '';

            if (reduceMotion.matches) {
                el.textContent = target + suffix;
                return;
            }

            const DURATION = 1100;
            const start = performance.now();

            const step = (now) => {
                const p = Math.min((now - start) / DURATION, 1);
                // easeOutExpo — fast start, gentle settle.
                const eased = p === 1 ? 1 : 1 - Math.pow(2, -10 * p);

                el.textContent = Math.round(target * eased) + suffix;

                if (p < 1) requestAnimationFrame(step);
            };

            requestAnimationFrame(step);
        };

        if (!('IntersectionObserver' in window)) {
            nums.forEach(run);
            return;
        }

        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                run(entry.target);
                obs.unobserve(entry.target);
            });
        }, { threshold: 0.5 });

        nums.forEach((el) => observer.observe(el));
    });


    /* ------------------------------------------------------- card glow --- */

    boot('card-glow', () => {
        if (reduceMotion.matches || !matchMedia('(hover: hover) and (pointer: fine)').matches) return;

        const cards = $$('.card-glow');
        if (!cards.length) return;

        cards.forEach((card) => {
            card.addEventListener('pointermove', (e) => {
                const rect = card.getBoundingClientRect();
                card.style.setProperty('--mx', `${e.clientX - rect.left}px`);
                card.style.setProperty('--my', `${e.clientY - rect.top}px`);
            });
        });
    });


    /* ------------------------------------------------- project filtering --- */

    boot('projects', () => {
        const grid = $('#projects-grid');
        if (!grid) return;

        const cards   = $$('.project', grid);
        const filters = $$('.filter');
        const search  = $('#project-search');
        const empty   = $('#projects-empty');
        const count   = $('#projects-count');

        let category = 'all';
        let query    = '';

        const apply = () => {
            let shown = 0;

            cards.forEach((card) => {
                const matchesCategory = category === 'all' || card.dataset.category === category;
                const matchesQuery    = query === '' || (card.dataset.search || '').includes(query);
                const visible         = matchesCategory && matchesQuery;

                card.hidden = !visible;

                if (visible) {
                    // Restart the entry animation so filtered-in cards animate.
                    card.style.animationDelay = `${Math.min(shown, 8) * 45}ms`;
                    shown++;
                }
            });

            if (empty) empty.hidden = shown > 0;
            if (count) count.textContent = String(shown);
        };

        filters.forEach((btn) => {
            btn.addEventListener('click', () => {
                category = btn.dataset.filter || 'all';
                filters.forEach((b) => b.setAttribute('aria-pressed', String(b === btn)));
                apply();
            });
        });

        if (search) {
            let timer;
            search.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    query = search.value.trim().toLowerCase();
                    apply();
                }, 140);
            });

            // Escape clears an active search rather than closing anything else.
            search.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && search.value) {
                    e.stopPropagation();
                    search.value = '';
                    query = '';
                    apply();
                }
            });
        }

        apply();
    });


    /* ---------------------------------------------------- case-study modal --- */

    boot('modal', () => {
        const modal = $('#case-modal');
        const panel = $('#case-panel');
        if (!modal || !panel) return;

        const closeBtn = $('.modal-close', modal);
        let lastFocus  = null;

        const FOCUSABLE = 'a[href], button:not([disabled]), input, textarea, select, [tabindex]:not([tabindex="-1"])';

        const close = () => {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('is-locked');
            lastFocus?.focus?.();

            if (location.hash.startsWith('#project-')) {
                history.replaceState(null, '', location.pathname + location.search);
            }
        };

        const open = (slug) => {
            const source = document.getElementById(`case-${slug}`);
            if (!source) return;

            lastFocus = document.activeElement;

            panel.innerHTML = source.innerHTML;
            panel.prepend(closeBtn);

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('is-locked');
            panel.scrollTop = 0;

            closeBtn?.focus();
            history.replaceState(null, '', `#project-${slug}`);
        };

        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('[data-case]');

            if (trigger) {
                e.preventDefault();
                open(trigger.dataset.case);
                return;
            }

            // A click on the backdrop, but not inside the panel, dismisses it.
            if (e.target === modal) close();
        });

        closeBtn?.addEventListener('click', close);

        addEventListener('keydown', (e) => {
            if (!modal.classList.contains('is-open')) return;

            if (e.key === 'Escape') {
                close();
                return;
            }

            if (e.key !== 'Tab') return;

            // Trap focus inside the dialog.
            const items = $$(FOCUSABLE, panel).filter((el) => el.offsetParent !== null);
            if (!items.length) return;

            const first = items[0];
            const last  = items[items.length - 1];

            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        });

        // Deep link: /#project-timeless opens that case study directly.
        if (location.hash.startsWith('#project-')) {
            open(location.hash.replace('#project-', ''));
        }
    });


    /* ---------------------------------------------------------- contact --- */

    boot('contact-form', () => {
        const form = $('#contact-form');
        if (!form) return;

        const submit = $('[type="submit"]', form);
        const status = $('#form-status');

        const showFieldErrors = (errors) => {
            $$('.field', form).forEach((field) => {
                const input = $('input, textarea, select', field);
                const slot  = $('.field-error', field);
                const name  = input?.name;
                const msg   = name ? errors[name] : null;

                field.dataset.invalid = msg ? 'true' : 'false';
                if (slot) slot.textContent = msg || '';
                if (input) input.setAttribute('aria-invalid', msg ? 'true' : 'false');
            });

            const firstInvalid = $('[data-invalid="true"] input, [data-invalid="true"] textarea', form);
            firstInvalid?.focus();
        };

        const setStatus = (ok, message) => {
            if (!status) return;

            status.hidden = false;
            status.className = `alert ${ok ? 'alert-ok' : 'alert-err'}`;
            status.setAttribute('role', ok ? 'status' : 'alert');
            status.innerHTML = `<span>${message}</span>`;
            status.scrollIntoView({ block: 'nearest', behavior: reduceMotion.matches ? 'auto' : 'smooth' });
        };

        form.addEventListener('submit', async (e) => {
            // Without fetch, fall through to the ordinary POST — the server
            // handles both and the form still works.
            if (!window.fetch) return;

            e.preventDefault();

            submit?.classList.add('is-busy');
            submit?.setAttribute('aria-disabled', 'true');
            if (status) status.hidden = true;

            try {
                const response = await fetch(form.action || location.href, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });

                const data = await response.json().catch(() => ({
                    ok: false,
                    message: 'Unexpected response from the server.',
                    errors: {},
                }));

                showFieldErrors(data.errors || {});
                setStatus(Boolean(data.ok), data.message || 'Something went wrong.');

                if (data.ok) {
                    form.reset();

                    // The server rotates the CSRF token after a successful send,
                    // so refresh the hidden field before another submission.
                    const token = $('[name="csrf_token"]', form);
                    if (token && data.csrf) token.value = data.csrf;
                }
            } catch {
                setStatus(false, 'Could not reach the server. Please check your connection and try again.');
            } finally {
                submit?.classList.remove('is-busy');
                submit?.removeAttribute('aria-disabled');
            }
        });

        // Clear a field's error as soon as the visitor starts correcting it.
        $$('input, textarea', form).forEach((input) => {
            input.addEventListener('input', () => {
                const field = input.closest('.field');
                if (field?.dataset.invalid === 'true') {
                    field.dataset.invalid = 'false';
                    input.removeAttribute('aria-invalid');
                    const slot = $('.field-error', field);
                    if (slot) slot.textContent = '';
                }
            });
        });
    });


    /* ------------------------------------------------------- copy email --- */

    boot('copy', () => {
        $$('[data-copy]').forEach((btn) => {
            const label = btn.querySelector('[data-copy-label]') || btn;
            const original = label.textContent;

            btn.addEventListener('click', async () => {
                const value = btn.dataset.copy;
                if (!value) return;

                try {
                    await navigator.clipboard.writeText(value);
                } catch {
                    // Clipboard API needs a secure context; fall back to a
                    // temporary selection, which works over plain HTTP too.
                    const ta = document.createElement('textarea');
                    ta.value = value;
                    ta.setAttribute('readonly', '');
                    ta.style.cssText = 'position:fixed;opacity:0;pointer-events:none';
                    document.body.appendChild(ta);
                    ta.select();
                    try { document.execCommand('copy'); } catch { /* give up quietly */ }
                    ta.remove();
                }

                label.textContent = 'Copied';
                setTimeout(() => { label.textContent = original; }, 1800);
            });
        });
    });


    /* ------------------------------------------------------------ year --- */

    boot('year', () => {
        $$('[data-year]').forEach((el) => {
            el.textContent = String(new Date().getFullYear());
        });
    });
})();
