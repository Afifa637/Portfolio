/**
 * Case-study page: the table of contents follows the section being read, and
 * builds with an architecture record get the interactive request run.
 */

import { $, $$, data } from '@/core/util.js';
import { createFlow } from '@/modules/flow.js';

export function init(page) {
    const links = $$('.cs-toc a', page);
    const sections = links.map((a) => document.getElementById(a.hash.slice(1))).filter(Boolean);

    if ('IntersectionObserver' in window && sections.length) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                links.forEach((a) => {
                    if (a.hash === '#' + entry.target.id) a.setAttribute('aria-current', 'true');
                    else a.removeAttribute('aria-current');
                });
            });
        }, { rootMargin: '-30% 0px -60% 0px' });

        sections.forEach((s) => io.observe(s));
    }

    const list = $('#cs-arch', page);
    const project = data().bySlug[page.dataset.slug];
    if (!list || !project) return;

    const flow = createFlow({
        body: $('.hood-body', page),
        list,
        packet: $('#cs-packet', page),
        log: $('#cs-log', page),
        insp: $('#cs-insp', page),
        getLayers: () => project.architecture,
        getRequest: () => project.demo_request || 'GET /',
    });

    flow.select(0);

    const run = $('#cs-run', page);
    const deny = $('#cs-deny', page);

    const guard = async (denied) => {
        [run, deny].forEach((b) => { if (b) b.disabled = true; });
        await flow.run({ denied });
        [run, deny].forEach((b) => { if (b) b.disabled = false; });
    };

    run?.addEventListener('click', () => guard(false));
    deny?.addEventListener('click', () => guard(true));
}
