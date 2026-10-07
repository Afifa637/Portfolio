/**
 * Request blueprint: one stage open at a time, opened by hover, focus or
 * click, so it reads as a guided walk through the lifecycle.
 */

import { $$, finePointer } from '@/core/util.js';

export function init(root) {
    const stages = $$('.bp-stage', root);

    const openStage = (stage) => {
        stages.forEach((s) => {
            const on = s === stage;
            s.classList.toggle('is-open', on);
            s.querySelector('.bp-btn')?.setAttribute('aria-expanded', String(on));
        });
    };

    stages.forEach((stage) => {
        const btn = stage.querySelector('.bp-btn');
        btn?.addEventListener('click', () => openStage(stage));
        btn?.addEventListener('focus', () => openStage(stage));
        if (finePointer.matches) stage.addEventListener('pointerenter', () => openStage(stage));
    });
}
