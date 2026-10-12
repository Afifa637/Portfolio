/**
 * Roadmap: draws a winding road through every stop and drives a marker along
 * it as you scroll. The route bar jumps to any stop; the arrows go to the
 * previous or next one.
 *
 * The road is threaded through the real position of each stop's pin, so it
 * follows the layout at any width and is rebuilt when the layout changes.
 * Without this module the section is a plain vertical list.
 */

import { $, $$, clamp, reducedMotion } from '@/core/util.js';

export function init(root) {
    const stage = $('.road-stage', root);
    const svg = $('#road-svg', root);
    const stops = $$('.stop', root);
    if (!stage || !svg || stops.length < 2) return;

    const bed = $('.road-bed', svg);
    const mid = $('.road-mid', svg);
    const trip = $('.road-trip', svg);
    const car = $('.road-car', svg);
    const pins = $$('.route-pin', root);

    root.classList.add('is-road');

    let length = 0;
    let ys = [];
    let current = 0;

    /* --- geometry ------------------------------------------------------ */

    const build = () => {
        const box = stage.getBoundingClientRect();
        svg.setAttribute('width', box.width);
        svg.setAttribute('height', box.height);
        svg.setAttribute('viewBox', `0 0 ${box.width} ${box.height}`);

        const pts = stops.map((stop) => {
            const r = $('.stop-pin', stop).getBoundingClientRect();
            return { x: r.left - box.left + r.width / 2, y: r.top - box.top + r.height / 2 };
        });

        // The road runs in from above the first stop and out past the last.
        const first = pts[0];
        const last = pts[pts.length - 1];
        const all = [{ x: first.x, y: 0 }, ...pts, { x: last.x, y: box.height }];

        let d = `M${all[0].x} ${all[0].y}`;
        for (let i = 1; i < all.length; i++) {
            const a = all[i - 1];
            const b = all[i];
            const k = (b.y - a.y) / 2;
            d += ` C${a.x} ${a.y + k} ${b.x} ${b.y - k} ${b.x} ${b.y}`;
        }

        [bed, mid, trip].forEach((p) => p.setAttribute('d', d));

        length = trip.getTotalLength();
        trip.style.strokeDasharray = `${length}`;

        // Where along the road (0–1) each pin sits, found by walking the path.
        ys = pts.map((p) => {
            let lo = 0;
            let hi = length;
            for (let i = 0; i < 18; i++) {
                const m = (lo + hi) / 2;
                trip.getPointAtLength(m).y < p.y ? (lo = m) : (hi = m);
            }
            return lo / length;
        });

        update();
    };

    /* --- scroll-driven marker ------------------------------------------ */

    const progress = () => {
        if (reducedMotion.matches) return 1;
        const box = stage.getBoundingClientRect();
        const line = innerHeight * 0.55;
        return clamp((line - box.top) / box.height, 0, 1);
    };

    const setCurrent = (index) => {
        if (index === current) return;
        current = index;
        pins.forEach((pin, i) => {
            if (i === index) pin.setAttribute('aria-current', 'step');
            else pin.removeAttribute('aria-current');
        });
        $('.route-list', root)?.querySelectorAll('.route-pin')[index]?.scrollIntoView({ block: 'nearest', inline: 'center' });
    };

    function update() {
        if (!length) return;

        const p = progress();
        const at = trip.getPointAtLength(p * length);
        const ahead = trip.getPointAtLength(Math.min(length, p * length + 2));
        const angle = Math.atan2(ahead.y - at.y, ahead.x - at.x) * 180 / Math.PI + 90;

        car.setAttribute('transform', `translate(${at.x} ${at.y}) rotate(${angle})`);
        trip.style.strokeDashoffset = `${length * (1 - p)}`;

        let nearest = 0;
        stops.forEach((stop, i) => {
            const reached = ys[i] <= p + 0.0005;
            stop.classList.toggle('is-reached', reached);
            if (reached) nearest = i;
        });
        setCurrent(nearest);
    }

    let ticking = false;
    addEventListener('scroll', () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => { ticking = false; update(); });
    }, { passive: true });

    new ResizeObserver(() => build()).observe(stage);
    // Fonts and images can move the stops after the first measurement.
    document.fonts?.ready.then(build);
    addEventListener('load', build, { once: true });

    /* --- navigation ----------------------------------------------------- */

    let jumpTimer;

    const goto = (index) => {
        const stop = stops[clamp(index, 0, stops.length - 1)];
        // Land with the stop at the marker line so the road reaches it.
        const target = () => scrollY + stop.getBoundingClientRect().top - innerHeight * 0.45;

        scrollTo({ top: target(), behavior: reducedMotion.matches ? 'auto' : 'smooth' });
        $('.stop-card', stop)?.focus({ preventScroll: true });

        // Images and lazy sections above can shift the page mid-scroll and cut
        // a smooth scroll short, so check where it ended up and finish the job.
        clearTimeout(jumpTimer);
        let tries = 0;
        const settle = () => {
            if (Math.abs(target() - scrollY) > 24 && tries++ < 4) {
                scrollTo({ top: target(), behavior: reducedMotion.matches ? 'auto' : 'smooth' });
                jumpTimer = setTimeout(settle, 650);
            }
        };
        jumpTimer = setTimeout(settle, 650);
    };

    root.addEventListener('click', (e) => {
        const jump = e.target.closest('[data-goto]');
        if (jump) {
            const id = jump.dataset.goto;
            goto(stops.findIndex((s) => s.id === id));
            return;
        }

        const step = e.target.closest('[data-step]');
        if (step) goto(current + Number(step.dataset.step));
    });

    build();
}
