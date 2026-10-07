/**
 * Recruiter mode: the whole profile in under a minute.
 *
 * One screen — most-used technologies, the three strongest builds, education,
 * availability and every way to make contact. Shareable as /?view=recruiter.
 */

import { $, data, esc, track } from '@/core/util.js';
import { open, close } from '@/core/overlay.js';

let mounted = false;

function mount() {
    const d = data();
    const id = d.identity;
    const el = $('#recruiter');

    // Most-used technologies by evidence: how many projects actually use them.
    const top = d.skills.flatMap((g) => g.items)
        .filter((i) => i.projects.length > 0)
        .sort((a, b) => b.projects.length - a.projects.length)
        .slice(0, 10);

    const best = d.projects.filter((p) => p.featured)
        .sort((a, b) => b.architecture.length - a.architecture.length)
        .slice(0, 3);

    const edu = d.education[0];
    const linkedin = d.socials.find((s) => s.icon === 'linkedin');

    el.innerHTML = `
        <div class="overlay-panel rec-panel">
            <div class="rec-head">
                <span class="label"><span class="live"></span>&nbsp; Recruiter mode · 60-second profile</span>
                <div class="hero-actions">
                    <button class="btn btn-ghost btn-sm" type="button" data-rec-print>Print</button>
                    <button class="btn btn-primary btn-sm" type="button" data-rec-close>Exit recruiter mode</button>
                </div>
            </div>
            <div class="rec-body">
                <div class="rec-col">
                    <div>
                        <h2>${esc(id.name)}</h2>
                        <p class="lead" style="margin-top:.4rem">${esc(id.title)} · ${esc(id.subtitle)}</p>
                        <p class="t-2" style="margin-top:.8rem">${esc(id.pitch)}</p>
                    </div>
                    <div>
                        <h3>Strongest builds</h3>
                        ${best.map((p) => `<div class="rec-proj"><a class="ulink" href="${esc(p.url)}">${esc(p.title)}</a><span>${esc(p.subtitle)} — ${esc(p.stack.slice(0, 4).join(', '))}</span></div>`).join('')}
                    </div>
                    <div>
                        <h3>Most-used technologies <span class="t-3" style="text-transform:none;letter-spacing:0">(by project count)</span></h3>
                        <div class="tags">${top.map((t) => `<span class="tag">${esc(t.name)} · ${t.projects.length}</span>`).join('')}</div>
                    </div>
                </div>
                <div class="rec-col">
                    <div>
                        <h3>Availability</h3>
                        <p><span class="live"></span>&nbsp; ${esc(id.availability)}</p>
                        <p class="t-2" style="font-size:.9rem">${esc(id.location)} · open to remote</p>
                    </div>
                    ${edu ? `<div><h3>Education</h3><p><strong>${esc(edu.degree)}</strong></p><p class="t-2">${esc(edu.institution)} · ${esc(edu.start)}–${esc(edu.end)}</p>${edu.grade ? `<p style="font-family:var(--f-display);font-size:1.6rem;font-weight:650;margin-top:.3rem">${esc(edu.grade)}</p>` : ''}</div>` : ''}
                    <div>
                        <h3>Evidence</h3>
                        <p class="t-2">${d.projects.length} documented projects${d.metrics?.repos ? ` · ${d.metrics.repos} public repositories · ${d.metrics.languages} languages` : ''}</p>
                    </div>
                    <div>
                        <h3>Contact</h3>
                        <div class="hero-actions">
                            <a class="btn btn-primary btn-sm" href="mailto:${esc(id.email)}">Email</a>
                            <a class="btn btn-ghost btn-sm" href="${esc(id.github)}" target="_blank" rel="noopener">GitHub</a>
                            ${linkedin ? `<a class="btn btn-ghost btn-sm" href="${esc(linkedin.url)}" target="_blank" rel="noopener">LinkedIn</a>` : ''}
                            <a class="btn btn-ghost btn-sm" href="${esc(id.cv)}" data-track="resume">CV (PDF)</a>
                        </div>
                        <p class="t-3" style="font-size:.8rem;margin-top:.7rem">${esc(id.email)}</p>
                    </div>
                </div>
            </div>
        </div>`;

    el.querySelector('[data-rec-close]').addEventListener('click', closeRecruiter);
    el.querySelector('[data-rec-print]').addEventListener('click', () => { location.href = id.resume + '?print=1'; });
    mounted = true;
}

export function openRecruiter() {
    if (!mounted) mount();
    track('recruiter mode');

    open($('#recruiter'), {
        onClose: () => {
            const url = new URL(location.href);
            if (url.searchParams.has('view')) {
                url.searchParams.delete('view');
                history.replaceState(null, '', url);
            }
        },
    });
}

export function closeRecruiter() {
    close($('#recruiter'));
}
