/**
 * Developer diagnostics (click the logo five times, or "Developer mode" in the
 * palette): live frame rate, DOM size, modules loaded, timing, viewport, theme
 * and connection. Real numbers from this page, for the visitor who opens
 * devtools on a portfolio.
 */

let panel = null;
let raf = 0;

export function toggleDiagnostics() {
    if (panel) {
        cancelAnimationFrame(raf);
        panel.remove();
        panel = null;
        return;
    }

    panel = document.createElement('aside');
    panel.className = 'diag';
    panel.setAttribute('aria-label', 'Developer diagnostics');
    document.body.append(panel);

    let frames = 0;
    let last = performance.now();
    let fps = '…';

    const render = () => {
        const modules = performance.getEntriesByType('resource').filter((r) => r.name.includes('/assets/js/')).length;
        const nav = performance.getEntriesByType('navigation')[0];

        panel.innerHTML =
            '<div class="diag-title"><span>DEV DIAGNOSTICS</span><button type="button" aria-label="Close diagnostics">×</button></div>'
            + `<div><span>fps</span><b>${fps}</b></div>`
            + `<div><span>dom nodes</span><b>${document.getElementsByTagName('*').length}</b></div>`
            + `<div><span>js modules loaded</span><b>${modules}</b></div>`
            + `<div><span>dom ready</span><b>${nav ? Math.round(nav.domContentLoadedEventEnd) + ' ms' : 'n/a'}</b></div>`
            + `<div><span>viewport</span><b>${innerWidth}×${innerHeight} @${devicePixelRatio}x</b></div>`
            + `<div><span>theme</span><b>${document.documentElement.dataset.theme}</b></div>`
            + `<div><span>reduced motion</span><b>${matchMedia('(prefers-reduced-motion: reduce)').matches}</b></div>`
            + `<div><span>connection</span><b>${navigator.connection?.effectiveType ?? 'n/a'}</b></div>`;

        panel.querySelector('button').onclick = toggleDiagnostics;
    };

    const loop = (now) => {
        frames++;

        if (now - last >= 1000) {
            fps = Math.round((frames * 1000) / (now - last));
            frames = 0;
            last = now;
            render();
        }

        raf = requestAnimationFrame(loop);
    };

    render();
    raf = requestAnimationFrame(loop);
}
