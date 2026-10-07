/**
 * Lab: sorting, visible.
 *
 * Each algorithm is a generator that yields every comparison and every write,
 * so the visualiser can play, pause and single-step through the real work —
 * and count it. Watching quicksort and insertion sort do the same job is a
 * better explanation of O(n log n) than the notation.
 */

import { $, $$, reducedMotion } from '@/core/util.js';

const ALGORITHMS = {
    insertion: { name: 'Insertion sort', big: 'O(n²) · stable · in place', run: insertion },
    merge:     { name: 'Merge sort', big: 'O(n log n) · stable · O(n) extra', run: merge },
    quick:     { name: 'Quicksort', big: 'O(n log n) average · in place', run: quick },
    heap:      { name: 'Heapsort', big: 'O(n log n) worst case · in place', run: heap },
};

function* insertion(a) {
    for (let i = 1; i < a.length; i++) {
        let j = i;
        while (j > 0) {
            yield { cmp: [j - 1, j] };
            if (a[j - 1] <= a[j]) break;
            [a[j - 1], a[j]] = [a[j], a[j - 1]];
            yield { swp: [j - 1, j] };
            j--;
        }
    }
}

function* merge(a, lo = 0, hi = a.length - 1) {
    if (lo >= hi) return;
    const mid = (lo + hi) >> 1;
    yield* merge(a, lo, mid);
    yield* merge(a, mid + 1, hi);

    const left = a.slice(lo, mid + 1);
    const right = a.slice(mid + 1, hi + 1);
    let i = 0; let j = 0; let k = lo;

    while (i < left.length && j < right.length) {
        yield { cmp: [lo + i, mid + 1 + j] };
        a[k] = left[i] <= right[j] ? left[i++] : right[j++];
        yield { swp: [k, k] };
        k++;
    }
    while (i < left.length) { a[k] = left[i++]; yield { swp: [k, k] }; k++; }
    while (j < right.length) { a[k] = right[j++]; yield { swp: [k, k] }; k++; }
}

function* quick(a, lo = 0, hi = a.length - 1) {
    if (lo >= hi) return;

    // Median-of-three pivot avoids the sorted-input worst case.
    const mid = (lo + hi) >> 1;
    if (a[mid] < a[lo]) [a[mid], a[lo]] = [a[lo], a[mid]];
    if (a[hi] < a[lo]) [a[hi], a[lo]] = [a[lo], a[hi]];
    if (a[mid] < a[hi]) [a[mid], a[hi]] = [a[hi], a[mid]];
    const pivot = a[hi];

    let i = lo;
    for (let j = lo; j < hi; j++) {
        yield { cmp: [j, hi] };
        if (a[j] < pivot) {
            [a[i], a[j]] = [a[j], a[i]];
            yield { swp: [i, j] };
            i++;
        }
    }
    [a[i], a[hi]] = [a[hi], a[i]];
    yield { swp: [i, hi] };

    yield* quick(a, lo, i - 1);
    yield* quick(a, i + 1, hi);
}

function* heap(a) {
    const n = a.length;

    function* sift(start, end) {
        let root = start;
        while (2 * root + 1 <= end) {
            let child = 2 * root + 1;
            let swap = root;
            yield { cmp: [swap, child] };
            if (a[swap] < a[child]) swap = child;
            if (child + 1 <= end) {
                yield { cmp: [swap, child + 1] };
                if (a[swap] < a[child + 1]) swap = child + 1;
            }
            if (swap === root) return;
            [a[root], a[swap]] = [a[swap], a[root]];
            yield { swp: [root, swap] };
            root = swap;
        }
    }

    for (let start = (n - 2) >> 1; start >= 0; start--) yield* sift(start, n - 1);

    for (let end = n - 1; end > 0; end--) {
        [a[0], a[end]] = [a[end], a[0]];
        yield { swp: [0, end] };
        yield* sift(0, end - 1);
    }
}

export function mount(panel) {
    panel.innerHTML = `
        <div class="lab-intro">
            <p>Four algorithms on the same shuffled data. Every bar movement is a real comparison
               (amber) or write (violet) from the algorithm — counted, not animated for show.</p>
        </div>
        <div class="sort-controls">
            <div class="choices" role="radiogroup" aria-label="Algorithm">
                ${Object.entries(ALGORITHMS).map(([k, a], i) => `<label class="choice"><input type="radio" name="algo" value="${k}" ${i === 2 ? 'checked' : ''}><span>${a.name}</span></label>`).join('')}
            </div>
        </div>
        <div class="sort-controls">
            <button class="btn btn-primary btn-sm" type="button" data-act="play">Play</button>
            <button class="btn btn-ghost btn-sm" type="button" data-act="step">Step</button>
            <button class="btn btn-ghost btn-sm" type="button" data-act="shuffle">Shuffle</button>
            <label>Size <input type="range" min="12" max="120" value="48" data-act="size"></label>
            <label>Speed <input type="range" min="1" max="60" value="22" data-act="speed"></label>
        </div>
        <div class="sort-stage" id="sort-stage" role="img" aria-label="Bar chart of values being sorted"></div>
        <div class="sort-stats" aria-live="polite">
            <span>Algorithm <b data-s="name">—</b></span>
            <span>Complexity <b data-s="big">—</b></span>
            <span>Comparisons <b data-s="cmp">0</b></span>
            <span>Writes <b data-s="swp">0</b></span>
        </div>`;

    const stage = $('#sort-stage', panel);
    const stat = (k, v) => { const el = panel.querySelector(`[data-s="${k}"]`); if (el) el.textContent = v; };
    const playBtn = panel.querySelector('[data-act="play"]');

    let values = [];
    let bars = [];
    let gen = null;
    let timer = null;
    let comparisons = 0;
    let writes = 0;
    let lastMarked = [];

    const algo = () => panel.querySelector('input[name="algo"]:checked').value;
    const speed = () => Number(panel.querySelector('[data-act="speed"]').value);

    const draw = () => {
        const max = Math.max(...values);
        bars.forEach((bar, i) => { bar.style.height = `${(values[i] / max) * 100}%`; });
    };

    const reset = () => {
        stop();
        const n = Number(panel.querySelector('[data-act="size"]').value);
        values = Array.from({ length: n }, (_, i) => i + 1);

        for (let i = n - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [values[i], values[j]] = [values[j], values[i]];
        }

        stage.innerHTML = values.map(() => '<span class="sort-bar"></span>').join('');
        bars = $$('.sort-bar', stage);
        gen = null;
        comparisons = 0;
        writes = 0;
        stat('cmp', 0);
        stat('swp', 0);
        stat('name', ALGORITHMS[algo()].name);
        stat('big', ALGORITHMS[algo()].big);
        draw();
    };

    const tick = () => {
        if (!gen) gen = ALGORITHMS[algo()].run(values);

        lastMarked.forEach((i) => bars[i]?.classList.remove('cmp', 'swp'));
        lastMarked = [];

        const { value, done } = gen.next();

        if (done) {
            stop();
            bars.forEach((b) => b.classList.add('done'));
            gen = null;
            return false;
        }

        if (value.cmp) {
            comparisons++;
            value.cmp.forEach((i) => bars[i]?.classList.add('cmp'));
            lastMarked = value.cmp;
        } else if (value.swp) {
            writes++;
            value.swp.forEach((i) => bars[i]?.classList.add('swp'));
            lastMarked = value.swp;
            draw();
        }

        stat('cmp', comparisons);
        stat('swp', writes);
        return true;
    };

    function stop() {
        clearInterval(timer);
        timer = null;
        if (playBtn) playBtn.textContent = 'Play';
    }

    const play = () => {
        if (timer) { stop(); return; }
        if (bars.some((b) => b.classList.contains('done'))) reset();

        playBtn.textContent = 'Pause';

        // Reduced motion: run to completion in large batches rather than animate.
        const batch = reducedMotion.matches ? 5000 : Math.max(1, Math.round(speed() / 12));
        timer = setInterval(() => {
            for (let i = 0; i < batch; i++) if (!tick()) return;
        }, reducedMotion.matches ? 0 : Math.max(4, 70 - speed()));
    };

    panel.addEventListener('click', (e) => {
        const act = e.target.closest('[data-act]')?.dataset.act;
        if (act === 'play') play();
        if (act === 'step') { stop(); tick(); }
        if (act === 'shuffle') reset();
    });

    panel.addEventListener('change', (e) => {
        if (e.target.name === 'algo' || e.target.dataset.act === 'size') reset();
    });

    // Stop when the panel is hidden or the tab is backgrounded.
    document.addEventListener('visibilitychange', () => { if (document.hidden) stop(); });

    reset();
}
