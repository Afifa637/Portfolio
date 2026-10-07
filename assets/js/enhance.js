/**
 * Progressive enhancements layered on top of app.js.
 *
 * Kept in a separate file so the core experience — navigation, filtering, the
 * case-study modal, the contact form — never waits on it. Everything here is
 * optional polish that degrades to nothing if it fails to load, and every
 * module exits early under `prefers-reduced-motion` or on a device where the
 * interaction makes no sense.
 */

(() => {
    'use strict';

    const $  = (s, c = document) => c.querySelector(s);
    const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));

    const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)');
    const finePointer  = matchMedia('(hover: hover) and (pointer: fine)');

    const boot = (name, fn) => {
        try { fn(); } catch (err) { console.error(`[enhance] ${name}:`, err); }
    };


    /* -------------------------------------------------- command palette --- */

    boot('command-palette', () => {
        const palette = $('#cmdk');
        if (!palette) return;

        const input = $('#cmdk-input', palette);
        const list  = $('#cmdk-list', palette);
        let items = [];
        let active = 0;
        let lastFocus = null;

        /* The index is built from the page itself, so it never drifts out of
           step with the content the CMS rendered. */
        const buildIndex = () => {
            const entries = [];

            $$('.nav-link[data-spy]').forEach((link) => {
                entries.push({
                    group: 'Go to',
                    label: link.textContent.trim(),
                    icon: 'arrow-right',
                    action: () => location.hash = '#' + link.dataset.spy,
                });
            });

            $$('.project').forEach((card) => {
                const title = card.querySelector('.project-title')?.textContent.trim();
                const sub   = card.querySelector('.project-sub')?.textContent.trim() || '';
                const slug  = card.querySelector('[data-case]')?.dataset.case;
                if (!title || !slug) return;

                entries.push({
                    group: 'Projects',
                    label: title,
                    meta: sub,
                    icon: 'layers',
                    search: (card.dataset.search || ''),
                    action: () => card.querySelector('[data-case]')?.click(),
                });
            });

            const cv = $('a[href*="CV"], a[href*="download_cv"]');
            if (cv) {
                entries.push({ group: 'Actions', label: 'Download CV', icon: 'download', action: () => cv.click() });
            }

            $$('.hero-socials .social-link').forEach((link) => {
                entries.push({
                    group: 'Actions',
                    label: link.getAttribute('aria-label') || 'Open link',
                    icon: 'external',
                    action: () => window.open(link.href, link.href.startsWith('mailto:') ? '_self' : '_blank', 'noopener'),
                });
            });

            entries.push({
                group: 'Actions',
                label: 'Toggle light / dark theme',
                icon: 'sun',
                action: () => $('#theme-toggle')?.click(),
            });

            return entries;
        };

        const iconFor = (name) => {
            const source = document.querySelector(`[data-icon-source="${name}"]`);
            return source ? source.innerHTML : '';
        };

        const render = (query = '') => {
            const q = query.trim().toLowerCase();

            const matched = items.filter((item) => {
                if (q === '') return true;
                return (item.label + ' ' + (item.meta || '') + ' ' + (item.search || ''))
                    .toLowerCase().includes(q);
            });

            if (matched.length === 0) {
                list.innerHTML = '<p class="cmdk-empty">Nothing matches that.</p>';
                active = 0;
                return;
            }

            active = Math.min(active, matched.length - 1);

            let html = '';
            let group = null;

            matched.forEach((item, i) => {
                if (item.group !== group) {
                    group = item.group;
                    html += `<p class="cmdk-group">${group}</p>`;
                }

                html += `<button type="button" class="cmdk-item" role="option" data-index="${i}"`
                    + ` aria-selected="${i === active}">`
                    + `<span class="label">${item.label}</span>`
                    + (item.meta ? `<span class="meta">${item.meta}</span>` : '')
                    + '</button>';
            });

            list.innerHTML = html;
            list.dataset.count = String(matched.length);
            palette._matched = matched;
        };

        const open = () => {
            if (items.length === 0) items = buildIndex();

            lastFocus = document.activeElement;
            palette.classList.add('is-open');
            palette.setAttribute('aria-hidden', 'false');
            document.body.classList.add('is-locked');
            input.value = '';
            active = 0;
            render();
            input.focus();
        };

        const close = () => {
            palette.classList.remove('is-open');
            palette.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('is-locked');
            lastFocus?.focus?.();
        };

        const run = (index) => {
            const item = (palette._matched || [])[index];
            if (!item) return;
            close();
            // Let the dialog finish closing before the action moves the page.
            setTimeout(() => item.action(), 60);
        };

        const move = (delta) => {
            const count = Number(list.dataset.count || 0);
            if (count === 0) return;

            active = (active + delta + count) % count;

            $$('.cmdk-item', list).forEach((el, i) => {
                el.setAttribute('aria-selected', String(i === active));
                if (i === active) el.scrollIntoView({ block: 'nearest' });
            });
        };

        addEventListener('keydown', (e) => {
            const isOpen = palette.classList.contains('is-open');

            if ((e.key === 'k' || e.key === 'K') && (e.metaKey || e.ctrlKey)) {
                e.preventDefault();
                isOpen ? close() : open();
                return;
            }

            // "/" is a familiar shortcut, but not while the visitor is typing.
            if (!isOpen && e.key === '/' && !/^(INPUT|TEXTAREA|SELECT)$/.test(document.activeElement?.tagName)) {
                e.preventDefault();
                open();
                return;
            }

            if (!isOpen) return;

            if (e.key === 'Escape')      { e.preventDefault(); close(); }
            else if (e.key === 'ArrowDown') { e.preventDefault(); move(1); }
            else if (e.key === 'ArrowUp')   { e.preventDefault(); move(-1); }
            else if (e.key === 'Enter')     { e.preventDefault(); run(active); }
        });

        input?.addEventListener('input', () => { active = 0; render(input.value); });

        list?.addEventListener('click', (e) => {
            const item = e.target.closest('.cmdk-item');
            if (item) run(Number(item.dataset.index));
        });

        palette.addEventListener('click', (e) => { if (e.target === palette) close(); });

        $$('[data-cmdk-open]').forEach((trigger) => {
            trigger.addEventListener('click', open);
        });
    });


    /* ------------------------------------------------------- hero canvas --- */

    boot('hero-canvas', () => {
        const canvas = $('#hero-canvas');
        if (!canvas || reduceMotion.matches) return;

        const ctx = canvas.getContext('2d', { alpha: true });
        if (!ctx) return;

        const hero = canvas.parentElement;
        let width = 0;
        let height = 0;
        let dpr = 1;
        let nodes = [];
        let raf = null;
        let visible = true;
        const pointer = { x: -9999, y: -9999 };

        // Particle count scales with area but is hard-capped: a large desktop
        // viewport would otherwise make this O(n²) link pass expensive.
        const countFor = (w, h) => Math.min(70, Math.max(22, Math.round((w * h) / 26000)));

        const accent = () =>
            getComputedStyle(document.documentElement).getPropertyValue('--accent').trim() || '#ffb454';

        const resize = () => {
            const rect = hero.getBoundingClientRect();
            dpr = Math.min(devicePixelRatio || 1, 2);
            width = rect.width;
            height = rect.height;

            canvas.width = Math.round(width * dpr);
            canvas.height = Math.round(height * dpr);
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

            const count = countFor(width, height);
            nodes = Array.from({ length: count }, () => ({
                x: Math.random() * width,
                y: Math.random() * height,
                vx: (Math.random() - 0.5) * 0.22,
                vy: (Math.random() - 0.5) * 0.22,
                r: Math.random() * 1.6 + 0.7,
            }));
        };

        const draw = () => {
            ctx.clearRect(0, 0, width, height);

            const colour = accent();
            const linkDistance = Math.min(150, width / 7);

            for (let i = 0; i < nodes.length; i++) {
                const node = nodes[i];

                node.x += node.vx;
                node.y += node.vy;

                // Wrap rather than bounce — bouncing makes the edges visible.
                if (node.x < -10) node.x = width + 10;
                if (node.x > width + 10) node.x = -10;
                if (node.y < -10) node.y = height + 10;
                if (node.y > height + 10) node.y = -10;

                // Gentle drift toward the cursor, capped so it never swarms.
                const dx = pointer.x - node.x;
                const dy = pointer.y - node.y;
                const distance = Math.hypot(dx, dy);

                if (distance < 180 && distance > 0.1) {
                    node.x += (dx / distance) * 0.35;
                    node.y += (dy / distance) * 0.35;
                }

                ctx.beginPath();
                ctx.arc(node.x, node.y, node.r, 0, Math.PI * 2);
                ctx.fillStyle = colour;
                ctx.globalAlpha = 0.45;
                ctx.fill();

                for (let j = i + 1; j < nodes.length; j++) {
                    const other = nodes[j];
                    const d = Math.hypot(node.x - other.x, node.y - other.y);

                    if (d > linkDistance) continue;

                    ctx.beginPath();
                    ctx.moveTo(node.x, node.y);
                    ctx.lineTo(other.x, other.y);
                    ctx.strokeStyle = colour;
                    ctx.globalAlpha = (1 - d / linkDistance) * 0.16;
                    ctx.lineWidth = 1;
                    ctx.stroke();
                }
            }

            ctx.globalAlpha = 1;
            raf = requestAnimationFrame(draw);
        };

        const start = () => { if (raf === null) raf = requestAnimationFrame(draw); };
        const stop  = () => { if (raf !== null) { cancelAnimationFrame(raf); raf = null; } };

        // Stop painting the moment the hero scrolls away, and when the tab is
        // hidden — an invisible canvas should cost nothing.
        new IntersectionObserver(([entry]) => {
            visible = entry.isIntersecting;
            visible && !document.hidden ? start() : stop();
        }, { threshold: 0.01 }).observe(hero);

        document.addEventListener('visibilitychange', () => {
            document.hidden || !visible ? stop() : start();
        });

        hero.addEventListener('pointermove', (e) => {
            const rect = hero.getBoundingClientRect();
            pointer.x = e.clientX - rect.left;
            pointer.y = e.clientY - rect.top;
        });

        hero.addEventListener('pointerleave', () => {
            pointer.x = pointer.y = -9999;
        });

        let resizeTimer;
        addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(resize, 160);
        });

        resize();
        start();
    });


    /* --------------------------------------------------------- card tilt --- */

    boot('tilt', () => {
        if (reduceMotion.matches || !finePointer.matches) return;

        $$('.tilt').forEach((card) => {
            const MAX = 4; // degrees — anything more reads as a gimmick

            card.addEventListener('pointermove', (e) => {
                const rect = card.getBoundingClientRect();
                const px = (e.clientX - rect.left) / rect.width - 0.5;
                const py = (e.clientY - rect.top) / rect.height - 0.5;

                card.classList.add('is-tilting');
                card.style.setProperty('--ry', `${px * MAX * 2}deg`);
                card.style.setProperty('--rx', `${-py * MAX * 2}deg`);
                card.style.setProperty('--ty', '-3px');
            });

            card.addEventListener('pointerleave', () => {
                card.classList.remove('is-tilting');
                card.style.setProperty('--rx', '0deg');
                card.style.setProperty('--ry', '0deg');
                card.style.setProperty('--ty', '0');
            });
        });
    });


    /* ---------------------------------------------------- magnetic buttons --- */

    boot('magnetic', () => {
        if (reduceMotion.matches || !finePointer.matches) return;

        $$('[data-magnetic]').forEach((el) => {
            const STRENGTH = 0.22;

            el.addEventListener('pointermove', (e) => {
                const rect = el.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;

                el.style.transform = `translate(${x * STRENGTH}px, ${y * STRENGTH}px)`;
            });

            el.addEventListener('pointerleave', () => {
                el.style.transform = '';
            });
        });
    });
})();
